<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenStreetMapOrganizationController extends Controller
{
    /**
     * Несколько публичных инстансов:
     * если один временно перегружен, пробуем следующий.
     */
    private const OVERPASS_ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.private.coffee/api/interpreter',
    ];

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kind' => [
                'nullable',
                'in:organization,doctor',
            ],

            'q' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'speciality' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * longitude,latitude
             */
            'll' => [
                'nullable',
                'regex:/^-?\d+(?:\.\d+)?,-?\d+(?:\.\d+)?$/',
            ],

            /*
             * longitude_span,latitude_span
             */
            'spn' => [
                'nullable',
                'regex:/^\d+(?:\.\d+)?,\d+(?:\.\d+)?$/',
            ],

            'results' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $kind =
            $validated['kind']
            ?? 'organization';

        $query =
            trim(
                (string) $validated['q']
            );

        $speciality =
            trim(
                (string) (
                    $validated['speciality']
                    ?? ''
                )
            );

        $limit =
            (int) (
                $validated['results']
                ?? 30
            );

        [$longitude, $latitude] =
            $this->parsePair(
                $validated['ll']
                    ?? null,
                [
                    84.9476,
                    56.4846,
                ]
            );

        [$longitudeSpan, $latitudeSpan] =
            $this->parsePair(
                $validated['spn']
                    ?? null,
                [
                    0.30,
                    0.22,
                ]
            );

        /*
         * Ограничиваем размер области,
         * чтобы пользователь случайно не отправил тяжёлый запрос на весь мир.
         */
        $longitudeSpan =
            min(
                max(
                    abs($longitudeSpan),
                    0.01
                ),
                $kind === 'doctor'
                    ? 0.10
                    : 1.20
            );

        $latitudeSpan =
            min(
                max(
                    abs($latitudeSpan),
                    0.01
                ),
                $kind === 'doctor'
                    ? 0.08
                    : 0.90
            );

        $bbox = [
            $latitude
                - (
                    $latitudeSpan
                    / 2
                ),

            $longitude
                - (
                    $longitudeSpan
                    / 2
                ),

            $latitude
                + (
                    $latitudeSpan
                    / 2
                ),

            $longitude
                + (
                    $longitudeSpan
                    / 2
                ),
        ];

        $overpassQuery =
            $kind === 'doctor'
                ? $this->buildDoctorQuery(
                    $query,
                    $speciality,
                    $bbox
                )
                : $this->buildOrganizationQuery(
                    $query,
                    $bbox
                );

        $cacheKey =
            'osm-medical:'
            . sha1(
                json_encode(
                    [
                        $kind,
                        mb_strtolower($query),
                        mb_strtolower($speciality),
                        $bbox,
                        $limit,
                    ],
                    JSON_UNESCAPED_UNICODE
                )
            );

        try {
            $items =
                Cache::remember(
                    $cacheKey,
                    now()->addMinutes(10),
                    function () use (
                        $overpassQuery,
                        $kind,
                        $query,
                        $speciality,
                        $limit
                    ): array {

                        $payload =
                            $this->requestOverpass(
                                $overpassQuery
                            );

                        $elements =
                            is_array(
                                $payload['elements']
                                ?? null
                            )
                                ? $payload['elements']
                                : [];

                        return $this->normalize(
                            $elements,
                            $kind,
                            $query,
                            $speciality,
                            $limit
                        );
                    }
                );

            return response()->json([
                'items' => $items,
                'count' => count($items),
                'source' => 'OpenStreetMap',
            ]);

        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' =>
                    'OpenStreetMap сейчас не ответил. '
                    . 'Попробуйте повторить поиск через несколько секунд.',
                'items' => [],
            ], 502);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function requestOverpass(
        string $query
    ): array {
        $lastError = null;

        foreach (
            self::OVERPASS_ENDPOINTS
            as $endpoint
        ) {
            try {
                $response =
                    Http::asForm()
                        ->acceptJson()
                        ->withHeaders([
                            /*
                             * Публичные Overpass-инстансы просят
                             * идентифицировать приложение.
                             */
                            'User-Agent' =>
                                config(
                                    'app.name',
                                    'MedicalApplication'
                                )
                                . '/1.0 '
                                . config(
                                    'app.url',
                                    ''
                                ),
                        ])
                        ->connectTimeout(5)
                        ->timeout(22)
                        ->post(
                            $endpoint,
                            [
                                'data' => $query,
                            ]
                        );

                if (
                    $response->successful()
                ) {
                    return (array)
                        $response->json();
                }

                $lastError =
                    new \RuntimeException(
                        'Overpass '
                        . $response->status()
                        . ' from '
                        . $endpoint
                    );

                /*
                 * 429/5xx — пробуем другой публичный инстанс.
                 */
                continue;

            } catch (Throwable $exception) {
                $lastError =
                    $exception;
            }
        }

        throw $lastError
            ?: new \RuntimeException(
                'Overpass API unavailable'
            );
    }

    /**
     * @param array<int, float> $fallback
     * @return array{0: float, 1: float}
     */
    private function parsePair(
        ?string $value,
        array $fallback
    ): array {
        if (!$value) {
            return $fallback;
        }

        $parts =
            array_map(
                'trim',
                explode(
                    ',',
                    $value
                )
            );

        if (
            count($parts) !== 2
        ) {
            return $fallback;
        }

        return [
            (float) $parts[0],
            (float) $parts[1],
        ];
    }

    /**
     * @param array{0: float,1: float,2: float,3: float} $bbox
     */
    private function buildOrganizationQuery(
        string $query,
        array $bbox
    ): string {
        $bboxText =
            $this->bbox(
                $bbox
            );

        $normalized =
            mb_strtolower(
                trim($query)
            );

        /*
         * Быстрые кнопки интерфейса.
         */
        if (
            in_array(
                $normalized,
                [
                    'больница',
                    'больницы',
                    'госпиталь',
                ],
                true
            )
        ) {
            return <<<OVERPASS
[out:json][timeout:15];
(
  nwr["amenity"="hospital"]({$bboxText});
  nwr["healthcare"="hospital"]({$bboxText});
);
out center tags;
OVERPASS;
        }

        if (
            in_array(
                $normalized,
                [
                    'клиника',
                    'клиники',
                    'поликлиника',
                    'поликлиники',
                ],
                true
            )
        ) {
            return <<<OVERPASS
[out:json][timeout:15];
(
  nwr["amenity"="clinic"]({$bboxText});
  nwr["healthcare"~"^(clinic|centre|center)$",i]({$bboxText});
  nwr["name"~"поликлиник|clinic",i]["healthcare"]({$bboxText});
);
out center tags;
OVERPASS;
        }

        if (
            in_array(
                $normalized,
                [
                    'медицинский центр',
                    'медцентр',
                    'медцентры',
                ],
                true
            )
        ) {
            return <<<OVERPASS
[out:json][timeout:15];
(
  nwr["healthcare"~"^(centre|center|clinic)$",i]({$bboxText});
  nwr["amenity"="clinic"]({$bboxText});
  nwr["name"~"медицинск|медцентр|medical",i]["healthcare"]({$bboxText});
);
out center tags;
OVERPASS;
        }

        if (
            str_contains(
                $normalized,
                'диагност'
            )
        ) {
            return <<<OVERPASS
[out:json][timeout:15];
(
  nwr["healthcare"="laboratory"]({$bboxText});
  nwr["name"~"диагност|diagnostic",i]["healthcare"]({$bboxText});
  nwr["name"~"диагност|diagnostic",i]["amenity"~"clinic|hospital"]({$bboxText});
);
out center tags;
OVERPASS;
        }

        /*
         * Произвольное название организации.
         */
        $regex =
            $this->overpassRegex(
                $query
            );

        return <<<OVERPASS
[out:json][timeout:15];
(
  nwr["name"~"{$regex}",i]["amenity"~"^(hospital|clinic|doctors)$"]({$bboxText});
  nwr["name"~"{$regex}",i]["healthcare"]({$bboxText});
  nwr["official_name"~"{$regex}",i]["healthcare"]({$bboxText});
);
out center tags;
OVERPASS;
    }

    /**
     * @param array{0: float,1: float,2: float,3: float} $bbox
     */
    private function buildDoctorQuery(
        string $query,
        string $speciality,
        array $bbox
    ): string {
        $bboxText =
            $this->bbox(
                $bbox
            );

        $doctorName =
            trim($query);

        /*
         * Если q фактически является только специальностью,
         * не требуем совпадения name.
         */
        $nameFilter = '';

        if (
            $doctorName !== ''
            && mb_strtolower(
                $doctorName
            ) !== mb_strtolower(
                $speciality
            )
        ) {
            $nameRegex =
                $this->overpassRegex(
                    $doctorName
                );

            $nameFilter =
                '["name"~"'
                . $nameRegex
                . '",i]';
        }

        $specialityFilter = '';

        $osmSpeciality =
            $this->mapSpeciality(
                $speciality
                    ?: $query
            );

        if (
            $osmSpeciality !== null
        ) {
            $specialityFilter =
                '["healthcare:speciality"~"'
                . $this->overpassRegex(
                    $osmSpeciality
                )
                . '",i]';
        }

        return <<<OVERPASS
[out:json][timeout:12];
(
  nwr["amenity"="doctors"]{$nameFilter}{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]{$nameFilter}{$specialityFilter}({$bboxText});
);
out center tags;
OVERPASS;
    }

    /**
     * Map common Russian specialty names to OSM healthcare:speciality values.
     */
    private function mapSpeciality(
        string $value
    ): ?string {
        $value =
            mb_strtolower(
                trim($value)
            );

        if ($value === '') {
            return null;
        }

        $map = [
            'невролог' => 'neurology',
            'неврология' => 'neurology',

            'психиатр' => 'psychiatry',
            'психиатрия' => 'psychiatry',

            'психотерапевт' => 'psychotherapy',

            'кардиолог' => 'cardiology',
            'кардиология' => 'cardiology',

            'эндокринолог' => 'endocrinology',
            'эндокринология' => 'endocrinology',

            'гастроэнтеролог' => 'gastroenterology',
            'гастроэнтерология' => 'gastroenterology',

            'дерматолог' => 'dermatology',
            'дерматовенеролог' => 'dermatology',

            'офтальмолог' => 'ophthalmology',
            'окулист' => 'ophthalmology',

            'лор' => 'otolaryngology',
            'оториноларинголог' => 'otolaryngology',

            'уролог' => 'urology',
            'урология' => 'urology',

            'гинеколог' => 'gynaecology',
            'гинекология' => 'gynaecology',

            'педиатр' => 'paediatrics',
            'педиатрия' => 'paediatrics',

            'терапевт' => 'general',
            'врач общей практики' => 'general',

            'хирург' => 'surgery',
            'хирургия' => 'surgery',

            'ортопед' => 'orthopaedics',
            'травматолог' => 'orthopaedics',

            'онколог' => 'oncology',
            'онкология' => 'oncology',

            'нефролог' => 'nephrology',
            'пульмонолог' => 'pulmonology',
            'ревматолог' => 'rheumatology',
            'аллерголог' => 'allergology',
            'иммунолог' => 'immunology',
        ];

        foreach (
            $map as $needle => $mapped
        ) {
            if (
                str_contains(
                    $value,
                    $needle
                )
            ) {
                return $mapped;
            }
        }

        /*
         * Если врач ввёл уже OSM/английское значение.
         */
        if (
            preg_match(
                '/^[a-z][a-z_; -]+$/',
                $value
            )
        ) {
            return $value;
        }

        return null;
    }

    /**
     * @param array<int, mixed> $elements
     * @return array<int, array<string, mixed>>
     */
    private function normalize(
        array $elements,
        string $kind,
        string $query,
        string $speciality,
        int $limit
    ): array {
        $items =
            collect(
                $elements
            )
                ->map(
                    function (
                        mixed $element
                    ) use (
                        $kind
                    ): ?array {

                        if (
                            !is_array(
                                $element
                            )
                        ) {
                            return null;
                        }

                        $tags =
                            is_array(
                                $element['tags']
                                    ?? null
                            )
                                ? $element['tags']
                                : [];

                        $name =
                            trim(
                                (string) (
                                    $tags['name']
                                    ?? $tags['official_name']
                                    ?? $tags['short_name']
                                    ?? ''
                                )
                            );

                        if (
                            $name === ''
                        ) {
                            return null;
                        }

                        $latitude =
                            $element['lat']
                            ?? data_get(
                                $element,
                                'center.lat'
                            );

                        $longitude =
                            $element['lon']
                            ?? data_get(
                                $element,
                                'center.lon'
                            );

                        if (
                            !is_numeric(
                                $latitude
                            )
                            ||
                            !is_numeric(
                                $longitude
                            )
                        ) {
                            return null;
                        }

                        $phone =
                            trim(
                                (string) (
                                    $tags['contact:phone']
                                    ?? $tags['phone']
                                    ?? ''
                                )
                            );

                        $url =
                            trim(
                                (string) (
                                    $tags['contact:website']
                                    ?? $tags['website']
                                    ?? ''
                                )
                            );

                        $hours =
                            trim(
                                (string) (
                                    $tags['opening_hours']
                                    ?? ''
                                )
                            );

                        $speciality =
                            trim(
                                (string) (
                                    $tags['healthcare:speciality']
                                    ?? ''
                                )
                            );

                        $categories =
                            collect([
                                $this->humanize(
                                    $tags['healthcare']
                                    ?? null
                                ),
                                $this->humanize(
                                    $tags['amenity']
                                    ?? null
                                ),
                            ])
                                ->filter()
                                ->unique()
                                ->implode(
                                    ', '
                                );

                        return [
                            'id' =>
                                'osm:'
                                . (
                                    $element['type']
                                    ?? 'object'
                                )
                                . ':'
                                . (
                                    $element['id']
                                    ?? sha1($name)
                                ),

                            'name' =>
                                $name,

                            'address' =>
                                $this->address(
                                    $tags
                                ),

                            'phone' =>
                                $phone,

                            'categories' =>
                                $categories,

                            'speciality' =>
                                $speciality,

                            'hours' =>
                                $hours !== ''
                                    ? $hours
                                    : null,

                            'url' =>
                                $url !== ''
                                    ? $url
                                    : null,

                            /*
                             * Yandex JS API 3.0:
                             * [longitude, latitude]
                             */
                            'coords' => [
                                (float) $longitude,
                                (float) $latitude,
                            ],

                            'kind' =>
                                $kind,
                        ];
                    }
                )
                ->filter();

        /*
         * Для произвольного текста немного улучшаем порядок выдачи.
         */
        $needle =
            mb_strtolower(
                trim(
                    $query
                )
            );

        $specialityNeedle =
            mb_strtolower(
                trim(
                    $speciality
                )
            );

        return $items
            ->sortBy(
                function (
                    array $item
                ) use (
                    $needle,
                    $specialityNeedle,
                    $kind
                ): int {

                    $haystack =
                        mb_strtolower(
                            implode(
                                ' ',
                                [
                                    $item['name'],
                                    $item['address'],
                                    $item['categories'],
                                    $item['speciality'],
                                ]
                            )
                        );

                    $score = 100;

                    if (
                        $needle !== ''
                        && str_contains(
                            $haystack,
                            $needle
                        )
                    ) {
                        $score -= 40;
                    }

                    if (
                        $kind === 'doctor'
                        && $specialityNeedle !== ''
                        && str_contains(
                            $haystack,
                            $specialityNeedle
                        )
                    ) {
                        $score -= 20;
                    }

                    return $score;
                }
            )
            ->unique(
                fn (array $item): string =>
                    mb_strtolower(
                        $item['name']
                        . '|'
                        . $item['address']
                    )
            )
            ->take(
                $limit
            )
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $tags
     */
    private function address(
        array $tags
    ): string {
        $full =
            trim(
                (string) (
                    $tags['addr:full']
                    ?? ''
                )
            );

        if (
            $full !== ''
        ) {
            return $full;
        }

        $city =
            trim(
                (string) (
                    $tags['addr:city']
                    ?? $tags['addr:place']
                    ?? ''
                )
            );

        $street =
            trim(
                (string) (
                    $tags['addr:street']
                    ?? ''
                )
            );

        $house =
            trim(
                (string) (
                    $tags['addr:housenumber']
                    ?? ''
                )
            );

        $streetHouse =
            trim(
                $street
                . (
                    $street !== ''
                    && $house !== ''
                        ? ', '
                        : ''
                )
                . $house
            );

        return collect([
            $city,
            $streetHouse,
        ])
            ->filter()
            ->implode(
                ', '
            );
    }

    private function humanize(
        mixed $value
    ): ?string {
        $value =
            mb_strtolower(
                trim(
                    (string) $value
                )
            );

        return match ($value) {
            'hospital' =>
                'Больница',

            'clinic' =>
                'Клиника',

            'doctors',
            'doctor' =>
                'Врач',

            'centre',
            'center' =>
                'Медицинский центр',

            'laboratory' =>
                'Лаборатория',

            'dentist' =>
                'Стоматология',

            '' =>
                null,

            default =>
                $value,
        };
    }

    /**
     * Overpass bbox order:
     * south,west,north,east
     *
     * @param array{0: float,1: float,2: float,3: float} $bbox
     */
    private function bbox(
        array $bbox
    ): string {
        return implode(
            ',',
            array_map(
                static fn (
                    float $value
                ): string =>
                    number_format(
                        $value,
                        7,
                        '.',
                        ''
                    ),
                $bbox
            )
        );
    }

    /**
     * Escape user input for an Overpass regex string.
     */
    private function overpassRegex(
        string $value
    ): string {
        $value =
            preg_quote(
                trim($value),
                '/'
            );

        return str_replace(
            [
                '\\',
                '"',
            ],
            [
                '\\\\',
                '\\"',
            ],
            $value
        );
    }
}
