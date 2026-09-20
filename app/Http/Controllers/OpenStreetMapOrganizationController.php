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
        'https://overpass.kumi.systems/api/interpreter',
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

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'speciality' => [
                'nullable',
                'string',
                'max:255',
            ],

            'osm_speciality' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9_;, -]+$/i',
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

        $city =
            trim(
                (string) (
                    $validated['city']
                    ?? ''
                )
            );

        $speciality =
            trim(
                (string) (
                    $validated['speciality']
                    ?? ''
                )
            );

        $osmSpeciality =
            trim(
                (string) (
                    $validated['osm_speciality']
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
                    ? 0.08
                    : 1.20
            );

        $latitudeSpan =
            min(
                max(
                    abs($latitudeSpan),
                    0.01
                ),
                $kind === 'doctor'
                    ? 0.06
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

        $center = [
            $longitude,
            $latitude,
        ];

        $overpassQuery =
            $kind === 'doctor'
                ? $this->buildDoctorQuery(
                    $query,
                    $speciality,
                    $osmSpeciality,
                    $bbox
                )
                : $this->buildOrganizationQuery(
                    $query,
                    $bbox
                );

        $psychiatricSearch =
            $kind === 'organization'
            && $this->isPsychiatricDestinationQuery($query);

        $cacheKey =
            'osm-medical:v4:'
            . sha1(
                json_encode(
                    [
                        $kind,
                        mb_strtolower($query),
                        mb_strtolower($city),
                        mb_strtolower($speciality),
                        mb_strtolower($osmSpeciality),
                        $bbox,
                        $limit,
                    ],
                    JSON_UNESCAPED_UNICODE
                )
            );

        $items = [];
        $warnings = [];
        $sources = [];

        /*
         * Overpass — основной источник, но он публичный и временами отвечает
         * 429/5xx или просто не укладывается в таймаут. Это не должно ломать
         * пользовательский поиск: fallback через Nominatim выполняется ниже.
         */
        try {
            $items = Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                function () use (
                    $overpassQuery,
                    $kind,
                    $query,
                    $speciality,
                    $osmSpeciality,
                    $center,
                    $limit,
                    $psychiatricSearch
                ): array {
                    $payload = $this->requestOverpass($overpassQuery);

                    $elements = is_array($payload['elements'] ?? null)
                        ? $payload['elements']
                        : [];

                    $normalized = $this->normalize(
                        $elements,
                        $kind,
                        $query,
                        $speciality,
                        $osmSpeciality,
                        $center,
                        $psychiatricSearch
                            ? min(160, max($limit * 4, 80))
                            : $limit
                    );

                    if ($psychiatricSearch) {
                        $normalized = $this->filterPsychiatricDestinationItems(
                            $normalized,
                            $limit
                        );
                    }

                    return array_slice($normalized, 0, $limit);
                }
            );

            $sources[] = 'Overpass';
        } catch (Throwable $exception) {
            report($exception);
            $warnings[] = 'OVERPASS_UNAVAILABLE';

            \Log::warning('Medical organization Overpass search failed; using fallback', [
                'exception' => get_class($exception),
                'kind' => $kind,
                'psychiatric' => $psychiatricSearch,
            ]);
        }

        /*
         * Если Overpass недоступен ИЛИ выдал мало результатов, продолжаем
         * поиск через Nominatim. Для психиатрии используем расширенный набор
         * запросов (психбольница, ПНД/ПНБ, НИИ ПЗ). Для обычной организации —
         * один общий запрос по названию/типу и городу.
         */
        if ($kind === 'organization' && $city !== '') {
            if ($psychiatricSearch && count($items) < 2) {
                $fallback = $this->searchPsychiatricDestinationsNominatim(
                    $city,
                    $center,
                    $limit
                );

                if ($fallback !== []) {
                    $sources[] = 'Nominatim';
                    $items = $this->mergeDestinationItems(
                        $items,
                        $fallback,
                        $limit
                    );
                }
            } elseif (!$psychiatricSearch && count($items) < 1) {
                $fallback = $this->searchMedicalOrganizationsNominatim(
                    $query,
                    $city,
                    $center,
                    $limit
                );

                if ($fallback !== []) {
                    $sources[] = 'Nominatim';
                    $items = $this->mergeDestinationItems(
                        $items,
                        $fallback,
                        $limit
                    );
                }
            }
        }

        $sources = array_values(array_unique($sources));
        $warnings = array_values(array_unique($warnings));

        /*
         * Важно: недоступность Overpass сама по себе больше НЕ является 502.
         * UI получает 200 и может показать результаты Nominatim либо обычное
         * сообщение «ничего не найдено» без красной сетевой ошибки.
         */
        return response()->json([
            'items' => array_values($items),
            'count' => count($items),
            'source' => $sources !== []
                ? implode(' + ', $sources)
                : null,
            'warnings' => $warnings,
            'message' => (
                $items === []
                && in_array('OVERPASS_UNAVAILABLE', $warnings, true)
            )
                ? 'Основной каталог OpenStreetMap временно недоступен; резервный поиск также не дал результатов.'
                : null,
        ]);
    }

    /**
     * Определение адреса по координатам через Nominatim / OpenStreetMap.
     *
     * Вызывается только для ВЫБРАННОЙ клиники, когда в исходном OSM-объекте
     * отсутствуют addr:* теги.
     */
    public function reverseGeocode(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'lat' => [
                    'required',
                    'numeric',
                    'between:-90,90',
                ],

                'lon' => [
                    'required',
                    'numeric',
                    'between:-180,180',
                ],
            ]);

        $latitude =
            (float) $validated['lat'];

        $longitude =
            (float) $validated['lon'];

        /*
         * Шесть знаков после запятой — точность порядка десятков сантиметров.
         * Для почтового адреса этого более чем достаточно.
         */
        $cacheKey =
            sprintf(
                'osm-reverse:%0.6f:%0.6f',
                $latitude,
                $longitude
            );

        try {
            $result =
                Cache::remember(
                    $cacheKey,
                    now()->addDays(90),
                    function () use (
                        $latitude,
                        $longitude
                    ): array {

                        /*
                         * Публичный Nominatim просит не превышать примерно
                         * 1 запрос/сек. Lock + timestamp делают ограничение
                         * общим для всех пользователей приложения.
                         */
                        return Cache::lock(
                            'nominatim-reverse-global-lock',
                            10
                        )
                            ->block(
                                5,
                                function () use (
                                    $latitude,
                                    $longitude
                                ): array {

                                    $lastRequestAt =
                                        (float) Cache::get(
                                            'nominatim-reverse-last-request-at',
                                            0
                                        );

                                    $now =
                                        microtime(true);

                                    $waitSeconds =
                                        1.05
                                        - (
                                            $now
                                            - $lastRequestAt
                                        );

                                    if (
                                        $waitSeconds >
                                            0
                                    ) {

                                        usleep(
                                            (int) (
                                                $waitSeconds
                                                * 1_000_000
                                            )
                                        );

                                    }


                                    $response =
                                        Http::acceptJson()
                                            ->withHeaders([
                                                'User-Agent' =>
                                                    config(
                                                        'app.name',
                                                        'MedicalApplication'
                                                    )
                                                    . '/1.0 ('
                                                    . config(
                                                        'app.url',
                                                        ''
                                                    )
                                                    . ')',

                                                'Accept-Language' =>
                                                    'ru',
                                            ])
                                            ->connectTimeout(4)
                                            ->timeout(10)
                                            ->get(
                                                'https://nominatim.openstreetmap.org/reverse',
                                                [
                                                    'format' =>
                                                        'jsonv2',

                                                    'lat' =>
                                                        $latitude,

                                                    'lon' =>
                                                        $longitude,

                                                    'zoom' =>
                                                        18,

                                                    'addressdetails' =>
                                                        1,

                                                    'accept-language' =>
                                                        'ru',
                                                ]
                                            );


                                    Cache::put(
                                        'nominatim-reverse-last-request-at',
                                        microtime(true),
                                        now()->addMinutes(5)
                                    );


                                    if (
                                        !$response
                                            ->successful()
                                    ) {

                                        throw new \RuntimeException(
                                            'Nominatim HTTP '
                                            . $response->status()
                                        );

                                    }


                                    $payload =
                                        (array) $response
                                            ->json();


                                    $details =
                                        is_array(
                                            $payload['address']
                                                ?? null
                                        )
                                            ? $payload['address']
                                            : [];


                                    $shortAddress =
                                        $this->formatReverseAddress(
                                            $details
                                        );


                                    $displayName =
                                        trim(
                                            (string) (
                                                $payload['display_name']
                                                ?? ''
                                            )
                                        );


                                    return [
                                        'address' =>
                                            $shortAddress
                                            ?: $displayName,

                                        'display_name' =>
                                            $displayName,

                                        'details' =>
                                            $details,
                                    ];

                                }
                            );
                    }
                );


            return response()->json(
                $result
            );


        } catch (Throwable $exception) {
            report($exception);

            /*
             * Reverse geocoding — только fallback.
             * Ошибка не должна мешать оформлению направления.
             */
            return response()->json([
                'address' => null,
                'display_name' => null,
                'details' => [],
                'message' =>
                    'Не удалось определить адрес по координатам.',
            ], 200);
        }
    }


    /**
     * Собираем короткий человекочитаемый адрес.
     *
     * @param array<string, mixed> $details
     */
    private function formatReverseAddress(
        array $details
    ): string {
        $city =
            trim(
                (string) (
                    $details['city']
                    ?? $details['town']
                    ?? $details['village']
                    ?? $details['municipality']
                    ?? $details['county']
                    ?? ''
                )
            );

        $road =
            trim(
                (string) (
                    $details['road']
                    ?? $details['pedestrian']
                    ?? $details['residential']
                    ?? $details['footway']
                    ?? ''
                )
            );

        $house =
            trim(
                (string) (
                    $details['house_number']
                    ?? ''
                )
            );

        $streetHouse =
            trim(
                $road
                . (
                    $road !== ''
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

    private function isPsychiatricDestinationQuery(string $query): bool
    {
        $value = mb_strtolower(trim($query));

        return in_array(
            $value,
            [
                'психиатрические стационары',
                'психиатрический стационар',
                'психиатрия',
            ],
            true
        );
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function filterPsychiatricDestinationItems(array $items, int $limit): array
    {
        return collect($items)
            ->map(function (array $item): array {
                $haystack = mb_strtolower(
                    implode(' ', [
                        (string) ($item['name'] ?? ''),
                        implode(' ', (array) ($item['aliases'] ?? [])),
                        (string) ($item['categories'] ?? ''),
                        (string) ($item['speciality'] ?? ''),
                        (string) ($item['speciality_label'] ?? ''),
                        (string) ($item['address'] ?? ''),
                    ])
                );

                $score = 0;

                if (preg_match('/psychiatr|mental[ _-]?health|психиатр|психоневрол|психического\s+здоров|ментальн/iu', $haystack)) {
                    $score += 100;
                }

                if (preg_match('/(?:^|\W)(?:ткпб|ниипз|нии\s*пз|пнд|пнб)(?:\W|$)/iu', $haystack)) {
                    $score += 95;
                }

                if (preg_match('/научно.?исследователь|research\s+institute|нии/iu', $haystack)
                    && preg_match('/псих|mental/iu', $haystack)) {
                    $score += 80;
                }

                if (str_contains($haystack, 'psychiatry')) {
                    $score += 120;
                }

                $item['_psy_score'] = $score;

                return $item;
            })
            ->filter(fn (array $item): bool => (int) ($item['_psy_score'] ?? 0) > 0)
            ->sortByDesc(function (array $item): float {
                return (float) ($item['_psy_score'] ?? 0)
                    - min((float) ($item['distance_m'] ?? 0) / 10000, 10);
            })
            ->map(function (array $item): array {
                unset($item['_psy_score']);
                return $item;
            })
            ->unique(fn (array $item): string => mb_strtolower(
                trim((string) ($item['name'] ?? ''))
                . '|'
                . trim((string) ($item['address'] ?? ''))
            ))
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param array{0: float, 1: float} $center
     * @return array<int, array<string, mixed>>
     */
    /**
     * Резервный поиск обычных медицинских организаций через Nominatim.
     * Используется только когда Overpass недоступен/ничего не вернул.
     *
     * @param array{0: float, 1: float} $center
     * @return array<int, array<string, mixed>>
     */
    private function searchMedicalOrganizationsNominatim(
        string $query,
        string $city,
        array $center,
        int $limit
    ): array {
        $query = trim($query);
        $city = trim(preg_replace('/^\s*(?:г\.?|город)\s+/iu', '', $city) ?? $city);

        if ($query === '' || $city === '') {
            return [];
        }

        $rows = $this->nominatimSearch(
            $query . ', ' . $city,
            min(12, max(4, $limit))
        );

        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $lat = $row['lat'] ?? null;
            $lon = $row['lon'] ?? null;

            if (!is_numeric($lat) || !is_numeric($lon)) {
                continue;
            }

            $displayName = trim((string) ($row['display_name'] ?? ''));
            $namedetails = is_array($row['namedetails'] ?? null)
                ? $row['namedetails']
                : [];
            $addressData = is_array($row['address'] ?? null)
                ? $row['address']
                : [];
            $extraTags = is_array($row['extratags'] ?? null)
                ? $row['extratags']
                : [];

            $fallbackNameParts = preg_split('/\s*,\s*/u', $displayName);
            $fallbackName = is_array($fallbackNameParts)
                ? (string) ($fallbackNameParts[0] ?? '')
                : '';

            $name = trim((string) (
                $namedetails['name:ru']
                ?? $namedetails['name']
                ?? $row['name']
                ?? $fallbackName
            ));

            if ($name === '') {
                continue;
            }

            $address = $this->formatNominatimSearchAddress(
                $addressData,
                $displayName
            );

            $category = trim((string) (
                $extraTags['healthcare']
                ?? $row['type']
                ?? $row['category']
                ?? 'medical'
            ));

            $items[] = [
                'id' => 'nominatim:'
                    . (string) ($row['osm_type'] ?? 'place')
                    . ':'
                    . (string) ($row['osm_id'] ?? sha1($name . $address)),
                'name' => $name,
                'aliases' => array_values(array_unique(array_filter([
                    $namedetails['official_name:ru'] ?? null,
                    $namedetails['official_name'] ?? null,
                    $namedetails['short_name:ru'] ?? null,
                    $namedetails['short_name'] ?? null,
                ]))),
                'address' => $address,
                'phone' => $extraTags['contact:phone']
                    ?? $extraTags['phone']
                    ?? null,
                'categories' => $this->humanize($category)
                    ?? 'Медицинская организация',
                'speciality' => trim((string) (
                    $extraTags['healthcare:speciality']
                    ?? ''
                )),
                'speciality_label' => $this->humanizeSpecialities(
                    trim((string) (
                        $extraTags['healthcare:speciality']
                        ?? ''
                    ))
                ),
                'hours' => $extraTags['opening_hours'] ?? null,
                'url' => $extraTags['contact:website']
                    ?? $extraTags['website']
                    ?? null,
                'coords' => [(float) $lon, (float) $lat],
                'distance_m' => (int) round($this->distanceMeters(
                    (float) $center[0],
                    (float) $center[1],
                    (float) $lon,
                    (float) $lat
                )),
                'kind' => 'organization',
            ];
        }

        return collect($items)
            ->unique(fn (array $item): string => mb_strtolower(
                trim((string) ($item['name'] ?? ''))
                . '|'
                . trim((string) ($item['address'] ?? ''))
            ))
            ->sortBy(fn (array $item): int => (int) ($item['distance_m'] ?? PHP_INT_MAX))
            ->take($limit)
            ->values()
            ->all();
    }

    private function searchPsychiatricDestinationsNominatim(string $city, array $center, int $limit): array
    {
        $city = trim(preg_replace('/^\s*(?:г\.?|город)\s+/iu', '', $city) ?? $city);

        if ($city === '') {
            return [];
        }

        $queries = [
            "психиатрическая больница, {$city}",
            "НИИ психического здоровья, {$city}",
            "психоневрологический диспансер, {$city}",
        ];

        $items = [];

        foreach ($queries as $query) {
            foreach ($this->nominatimSearch($query, min(8, max(3, $limit))) as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $lat = $row['lat'] ?? null;
                $lon = $row['lon'] ?? null;

                if (!is_numeric($lat) || !is_numeric($lon)) {
                    continue;
                }

                $displayName = trim((string) ($row['display_name'] ?? ''));
                $namedetails = is_array($row['namedetails'] ?? null) ? $row['namedetails'] : [];
                $addressData = is_array($row['address'] ?? null) ? $row['address'] : [];

                $fallbackNameParts = preg_split('/\s*,\s*/u', $displayName);
                $fallbackName = is_array($fallbackNameParts) ? (string) ($fallbackNameParts[0] ?? '') : '';

                $name = trim((string) (
                    $namedetails['name:ru']
                    ?? $namedetails['name']
                    ?? $row['name']
                    ?? $fallbackName
                ));

                if ($name === '') {
                    continue;
                }

                $address = $this->formatNominatimSearchAddress($addressData, $displayName);

                $items[] = [
                    'id' => 'nominatim:' . (string) ($row['osm_type'] ?? 'place') . ':' . (string) ($row['osm_id'] ?? sha1($name . $address)),
                    'name' => $name,
                    'aliases' => array_values(array_unique(array_filter([
                        $namedetails['official_name:ru'] ?? null,
                        $namedetails['official_name'] ?? null,
                        $namedetails['short_name:ru'] ?? null,
                        $namedetails['short_name'] ?? null,
                    ]))),
                    'address' => $address,
                    'phone' => null,
                    'categories' => 'Медицинское учреждение',
                    'speciality' => 'psychiatry',
                    'speciality_label' => 'Психиатрия',
                    'hours' => null,
                    'url' => null,
                    'coords' => [(float) $lon, (float) $lat],
                    'distance_m' => (int) round($this->distanceMeters(
                        (float) $center[0],
                        (float) $center[1],
                        (float) $lon,
                        (float) $lat
                    )),
                    'kind' => 'organization',
                ];
            }
        }

        return $this->filterPsychiatricDestinationItems($items, $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function nominatimSearch(string $query, int $limit): array
    {
        $cacheKey = 'nominatim-medical-search:v2:' . sha1(mb_strtolower(trim($query)) . '|' . $limit);

        try {
            return Cache::remember(
                $cacheKey,
                now()->addDays(7),
                function () use ($query, $limit): array {
                    return Cache::lock('nominatim-medical-search-global-lock', 10)
                        ->block(6, function () use ($query, $limit): array {
                            $lastRequestAt = (float) Cache::get('nominatim-medical-search-last-request-at', 0);
                            $waitSeconds = 1.05 - (microtime(true) - $lastRequestAt);

                            if ($waitSeconds > 0) {
                                usleep((int) ($waitSeconds * 1_000_000));
                            }

                            $response = Http::acceptJson()
                                ->withHeaders([
                                    'User-Agent' => config('app.name', 'MedicalApplication') . '/1.0 (' . config('app.url', '') . ')',
                                    'Accept-Language' => 'ru',
                                ])
                                ->connectTimeout(4)
                                ->timeout(10)
                                ->get('https://nominatim.openstreetmap.org/search', [
                                    'format' => 'jsonv2',
                                    'q' => $query,
                                    'limit' => $limit,
                                    'addressdetails' => 1,
                                    'namedetails' => 1,
                                    'extratags' => 1,
                                    'countrycodes' => 'ru',
                                    'accept-language' => 'ru',
                                ]);

                            Cache::put(
                                'nominatim-medical-search-last-request-at',
                                microtime(true),
                                now()->addMinutes(5)
                            );

                            if (!$response->successful()) {
                                return [];
                            }

                            $payload = $response->json();

                            return is_array($payload) ? $payload : [];
                        });
                }
            );
        } catch (Throwable $exception) {
            report($exception);
            return [];
        }
    }

    /** @param array<string, mixed> $address */
    private function formatNominatimSearchAddress(array $address, string $displayName): string
    {
        $city = trim((string) (
            $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? ''
        ));

        $road = trim((string) (
            $address['road']
            ?? $address['pedestrian']
            ?? $address['residential']
            ?? ''
        ));

        $house = trim((string) ($address['house_number'] ?? ''));
        $streetHouse = trim($road . (($road !== '' && $house !== '') ? ', ' : '') . $house);
        $value = collect([$city, $streetHouse])->filter()->implode(', ');

        return $value !== '' ? $value : $displayName;
    }

    /**
     * @param array<int, array<string, mixed>> $primary
     * @param array<int, array<string, mixed>> $fallback
     * @return array<int, array<string, mixed>>
     */
    private function mergeDestinationItems(array $primary, array $fallback, int $limit): array
    {
        return collect(array_merge($primary, $fallback))
            ->unique(function (array $item): string {
                $name = mb_strtolower(trim((string) ($item['name'] ?? '')));
                $address = mb_strtolower(trim((string) ($item['address'] ?? '')));
                return $name . '|' . $address;
            })
            ->sortBy(fn (array $item): int => (int) ($item['distance_m'] ?? PHP_INT_MAX))
            ->take($limit)
            ->values()
            ->all();
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
         * Специализированный поиск для направления в психиатрический стационар.
         *
         * В OSM учреждение нередко хранится аббревиатурой (ТКПБ, ПНД,
         * ПНБ, НИИ ПЗ), а healthcare:speciality может отсутствовать.
         * Поэтому сначала берём широкий набор медицинских и научных объектов,
         * а затем фильтруем их в PHP по name/official_name/short_name/operator.
         */
        if ($this->isPsychiatricDestinationQuery($normalized)) {
            return <<<OVERPASS
[out:json][timeout:20];
(
  nwr["healthcare:speciality"~"psychiatr|mental_health",i]({$bboxText});
  nwr["amenity"~"^(hospital|clinic)$"]({$bboxText});
  nwr["healthcare"~"^(hospital|clinic|centre|center)$",i]({$bboxText});
  nwr["amenity"="research_institute"]({$bboxText});
  nwr["office"="research"]({$bboxText});
  nwr["name"~"псих|psychiatr|mental.?health|НИИ ?ПЗ|НИИПЗ|ТКПБ|ПНД|ПНБ",i]({$bboxText});
  nwr["official_name"~"псих|psychiatr|mental.?health|НИИ ?ПЗ|НИИПЗ|ТКПБ|ПНД|ПНБ",i]({$bboxText});
  nwr["short_name"~"НИИ ?ПЗ|НИИПЗ|ТКПБ|ПНД|ПНБ",i]({$bboxText});
  nwr["operator"~"псих|psychiatr|mental.?health|НИИ ?ПЗ|НИИПЗ|ТКПБ|ПНД|ПНБ",i]({$bboxText});
);
out center tags;
OVERPASS;
        }

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
        string $osmSpeciality,
        array $bbox
    ): string {
        $bboxText =
            $this->bbox($bbox);

        $doctorName =
            trim($query);

        $isSpecialityOnly =
            $doctorName !== ''
            && mb_strtolower($doctorName) ===
               mb_strtolower($speciality);

        $mappedSpeciality =
            $osmSpeciality !== ''
                ? $osmSpeciality
                : (
                    $this->mapSpeciality(
                        $speciality ?: $query
                    )
                    ?? ''
                );

        $specialityFilter = '';

        if ($mappedSpeciality !== '') {
            $variants =
                collect(
                    preg_split(
                        '/[;,]+/',
                        $mappedSpeciality
                    )
                )
                    ->map(
                        fn (string $value): string =>
                            trim($value)
                    )
                    ->filter()
                    ->map(
                        fn (string $value): string =>
                            $this->overpassRegex($value)
                    )
                    ->implode('|');

            if ($variants !== '') {
                $specialityFilter =
                    '["healthcare:speciality"~"('
                    . $variants
                    . ')",i]';
            }
        }

        /*
         * По одной специальности ищем только объекты doctor/doctors.
         * На следующем этапе normalize() отфильтрует групповые практики
         * и организации, оставив только записи, похожие на ФИО человека.
         */
        if ($isSpecialityOnly || $doctorName === '') {
            if ($specialityFilter !== '') {
                return <<<OVERPASS
[out:json][timeout:12];
(
  nwr["amenity"="doctors"]{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]{$specialityFilter}({$bboxText});
);
out center tags;
OVERPASS;
            }

            return <<<OVERPASS
[out:json][timeout:12];
(
  nwr["amenity"="doctors"]({$bboxText});
  nwr["healthcare"="doctor"]({$bboxText});
);
out center tags;
OVERPASS;
        }

        /*
         * При поиске по ФИО проверяем несколько тегов.
         *
         * В OSM имя врача может лежать не только в name,
         * но и в operator / contact:person / doctor:name.
         */
        $nameRegex =
            $this->overpassRegex(
                $doctorName
            );

        $specialityBlocks = '';

        if ($specialityFilter !== '') {
            $specialityBlocks = <<<OVERPASS
  nwr["amenity"="doctors"]["name"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]["name"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});

  nwr["amenity"="doctors"]["operator"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]["operator"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});

  nwr["amenity"="doctors"]["contact:person"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]["contact:person"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});

  nwr["amenity"="doctors"]["doctor:name"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});
  nwr["healthcare"="doctor"]["doctor:name"~"{$nameRegex}",i]{$specialityFilter}({$bboxText});
OVERPASS;
        }

        /*
         * Fallback без speciality:
         * у конкретного врача специальность в OSM часто не заполнена.
         */
        return <<<OVERPASS
[out:json][timeout:12];
(
{$specialityBlocks}

  nwr["amenity"="doctors"]["name"~"{$nameRegex}",i]({$bboxText});
  nwr["healthcare"="doctor"]["name"~"{$nameRegex}",i]({$bboxText});

  nwr["amenity"="doctors"]["operator"~"{$nameRegex}",i]({$bboxText});
  nwr["healthcare"="doctor"]["operator"~"{$nameRegex}",i]({$bboxText});

  nwr["amenity"="doctors"]["contact:person"~"{$nameRegex}",i]({$bboxText});
  nwr["healthcare"="doctor"]["contact:person"~"{$nameRegex}",i]({$bboxText});

  nwr["amenity"="doctors"]["doctor:name"~"{$nameRegex}",i]({$bboxText});
  nwr["healthcare"="doctor"]["doctor:name"~"{$nameRegex}",i]({$bboxText});
);
out center tags;
OVERPASS;
    }

    private function mapSpeciality(
        string $value
    ): ?string {
        $value =
            mb_strtolower(trim($value));

        if ($value === '') {
            return null;
        }

        $map = [
            'аллерголог' => 'allergology',
            'иммунолог' => 'immunology',
            'анестезиолог' => 'anaesthetics',
            'реаниматолог' => 'anaesthetics',

            'невролог' => 'neurology',
            'невропатолог' => 'neurology',
            'нейрохирург' => 'neurosurgery',

            'психиатр' => 'psychiatry',
            'психотерапевт' => 'psychotherapy',

            'кардиолог' => 'cardiology',
            'кардиохирург' => 'cardiothoracic_surgery',

            'эндокринолог' => 'endocrinology',
            'гастроэнтеролог' => 'gastroenterology',
            'гематолог' => 'haematology',
            'генетик' => 'genetics',
            'гериатр' => 'geriatrics',

            'дерматолог' => 'dermatology',
            'дерматовенеролог' => 'dermatology',

            'офтальмолог' => 'ophthalmology',
            'окулист' => 'ophthalmology',

            'лор' => 'otolaryngology',
            'оториноларинголог' => 'otolaryngology',
            'отоларинголог' => 'otolaryngology',

            'уролог' => 'urology',
            'андролог' => 'urology',

            'гинеколог' => 'gynaecology',
            'акушер-гинеколог' => 'gynaecology',

            'педиатр' => 'paediatrics',

            'терапевт' => 'general',
            'врач общей практики' => 'general',
            'семейный врач' => 'general',

            'хирург' => 'surgery',
            'ортопед' => 'orthopaedics',
            'травматолог' => 'orthopaedics',

            'онколог' => 'oncology',
            'маммолог' => 'oncology',

            'нефролог' => 'nephrology',
            'пульмонолог' => 'pulmonology',
            'ревматолог' => 'rheumatology',

            'стоматолог' => 'dentistry',
            'рентгенолог' => 'radiology',
            'радиолог' => 'radiology',
            'фтизиатр' => 'tuberculosis',
        ];

        foreach ($map as $needle => $mapped) {
            if (str_contains($value, $needle)) {
                return $mapped;
            }
        }

        if (
            preg_match(
                '/^[a-z][a-z0-9_;, -]+$/',
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
        string $osmSpeciality,
        array $center,
        int $limit
    ): array {
        $items =
            collect($elements)
                ->map(
                    function (
                        mixed $element
                    ) use (
                        $kind,
                        $center
                    ): ?array {

                        if (!is_array($element)) {
                            return null;
                        }

                        $tags =
                            is_array(
                                $element['tags']
                                ?? null
                            )
                                ? $element['tags']
                                : [];

                        if ($kind === 'doctor') {
                            /*
                             * amenity=doctors в OSM часто означает не человека,
                             * а клинику/кабинет/медцентр.
                             *
                             * Поэтому для выдачи врачей берём только значение,
                             * которое реально похоже на ФИО.
                             */
                            $name =
                                $this->extractDoctorPersonName(
                                    $tags
                                );

                            if ($name === null) {
                                return null;
                            }
                        } else {
                            $name =
                                trim(
                                    (string) (
                                        $tags['name']
                                        ?? $tags['official_name']
                                        ?? $tags['short_name']
                                        ?? ''
                                    )
                                );

                            if ($name === '') {
                                return null;
                            }
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
                            !is_numeric($latitude)
                            || !is_numeric($longitude)
                        ) {
                            return null;
                        }

                        $rawSpeciality =
                            trim(
                                (string) (
                                    $tags['healthcare:speciality']
                                    ?? ''
                                )
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

                            'name' => $name,

                            'aliases' => collect([
                                $tags['official_name'] ?? null,
                                $tags['short_name'] ?? null,
                                $tags['operator'] ?? null,
                                $tags['brand'] ?? null,
                            ])
                                ->filter()
                                ->map(fn ($value) => trim((string) $value))
                                ->filter()
                                ->unique()
                                ->values()
                                ->all(),

                            'address' =>
                                $this->address($tags),

                            'phone' =>
                                trim(
                                    (string) (
                                        $tags['contact:phone']
                                        ?? $tags['phone']
                                        ?? ''
                                    )
                                ),

                            'categories' =>
                                collect([
                                    $this->humanize(
                                        $tags['healthcare']
                                        ?? null
                                    ),
                                    $this->humanize(
                                        $tags['amenity']
                                        ?? null
                                    ),
                                    $this->humanize(
                                        $tags['office']
                                        ?? null
                                    ),
                                ])
                                    ->filter()
                                    ->unique()
                                    ->implode(', '),

                            'speciality' =>
                                $rawSpeciality,

                            'speciality_label' =>
                                $this->humanizeSpecialities(
                                    $rawSpeciality
                                ),

                            'hours' =>
                                trim(
                                    (string) (
                                        $tags['opening_hours']
                                        ?? ''
                                    )
                                )
                                ?: null,

                            'url' =>
                                trim(
                                    (string) (
                                        $tags['contact:website']
                                        ?? $tags['website']
                                        ?? ''
                                    )
                                )
                                ?: null,

                            'coords' => [
                                (float) $longitude,
                                (float) $latitude,
                            ],

                            'distance_m' =>
                                (int) round(
                                    $this->distanceMeters(
                                        (float) $center[0],
                                        (float) $center[1],
                                        (float) $longitude,
                                        (float) $latitude
                                    )
                                ),

                            'kind' => $kind,
                        ];
                    }
                )
                ->filter();

        $needle =
            mb_strtolower(trim($query));

        $specialityNeedle =
            mb_strtolower(trim($speciality));

        $mappedSpeciality =
            mb_strtolower(
                trim(
                    $osmSpeciality !== ''
                        ? $osmSpeciality
                        : (
                            $this->mapSpeciality($speciality)
                            ?? ''
                        )
                )
            );

        return $items
            ->sortBy(
                function (
                    array $item
                ) use (
                    $needle,
                    $specialityNeedle,
                    $mappedSpeciality,
                    $kind
                ): float {

                    $haystack =
                        mb_strtolower(
                            implode(
                                ' ',
                                [
                                    $item['name'],
                                    $item['address'],
                                    $item['categories'],
                                    $item['speciality'],
                                    $item['speciality_label'],
                                ]
                            )
                        );

                    $score = 100.0;

                    if (
                        $needle !== ''
                        && str_contains(
                            $haystack,
                            $needle
                        )
                    ) {
                        $score -= 50;
                    }

                    if ($kind === 'doctor') {
                        if (
                            $specialityNeedle !== ''
                            && str_contains(
                                $haystack,
                                $specialityNeedle
                            )
                        ) {
                            $score -= 20;
                        }

                        if ($mappedSpeciality !== '') {
                            $firstMapped =
                                trim(
                                    preg_split(
                                        '/[;,]+/',
                                        $mappedSpeciality
                                    )[0]
                                    ?? ''
                                );

                            if (
                                $firstMapped !== ''
                                && str_contains(
                                    mb_strtolower(
                                        $item['speciality']
                                    ),
                                    $firstMapped
                                )
                            ) {
                                $score -= 25;
                            }
                        }

                        $score +=
                            min(
                                (
                                    (float)
                                    $item['distance_m']
                                ) / 1000,
                                20
                            );
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
            ->take($limit)
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

            'research_institute' =>
                'Научно-исследовательский институт',

            'research' =>
                'Научная организация',

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
    /**
     * Возвращает ФИО врача из OSM-тегов либо null.
     *
     * Мы специально НЕ показываем в поле врача:
     * клиники, лаборатории, женские консультации, стоматологии и т.п.
     *
     * @param array<string, mixed> $tags
     */
    private function extractDoctorPersonName(
        array $tags
    ): ?string {
        $candidates = [
            $tags['doctor:name'] ?? null,
            $tags['contact:person'] ?? null,
            $tags['person:name'] ?? null,
            $tags['operator'] ?? null,
            $tags['name'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $candidate =
                trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        (string) $candidate
                    )
                );

            if ($candidate === '') {
                continue;
            }

            /*
             * "Врач Иванов Иван Иванович",
             * "Доктор Иванов И.И.",
             * "ИП Иванов Иван Иванович"
             */
            $candidate =
                preg_replace(
                    '/^(?:врач|доктор|dr\.?|doctor|ип)\s+/iu',
                    '',
                    $candidate
                );

            $candidate =
                trim($candidate);

            if (
                $candidate !== ''
                && $this->looksLikePersonName(
                    $candidate
                )
            ) {
                return $candidate;
            }
        }

        return null;
    }

    private function looksLikePersonName(
        string $value
    ): bool {
        $value =
            trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    $value
                )
            );

        if ($value === '') {
            return false;
        }

        $lower =
            mb_strtolower(
                $value
            );

        /*
         * Явные признаки названия организации.
         */
        $organizationWords = [
            'клиника',
            'поликлиника',
            'больница',
            'госпиталь',
            'медцентр',
            'медицинский центр',
            'медицинская организация',
            'медицинская компания',
            'медицинская клиника',
            'консультация',
            'женская консультация',
            'стоматология',
            'стоматологическая',
            'лаборатория',
            'диагностический',
            'диагностика',
            'центр здоровья',
            'лечебный центр',
            'диспансер',
            'санаторий',
            'инвитро',
            'хеликс',
            'гемотест',
            'медси',
            'см-клиника',
            'отделение',
            'филиал',
            'ооо',
            'ао ',
            'зао',
            'clinic',
            'medical center',
            'hospital',
            'laboratory',
        ];

        foreach ($organizationWords as $word) {
            if (
                str_contains(
                    $lower,
                    $word
                )
            ) {
                return false;
            }
        }

        /*
         * Иванов И.И.
         * Иванов И. И.
         * Ivanov I.I.
         */
        if (
            preg_match(
                '/^[\p{L}][\p{L}\-]{1,40}\s+'
                . '(?:[\p{L}]\.?\s*){1,3}$/u',
                $value
            )
        ) {
            return true;
        }

        /*
         * Иванов Иван
         * Иванов Иван Иванович
         * Анна-Мария Иванова
         *
         * 2–4 слов, каждое состоит из букв/дефиса,
         * минимум два "полноценных" слова длиной >= 2.
         */
        $parts =
            preg_split(
                '/\s+/u',
                $value
            );

        if (
            !is_array($parts)
            || count($parts) < 2
            || count($parts) > 4
        ) {
            return false;
        }

        $fullWords = 0;

        foreach ($parts as $part) {
            $part =
                trim(
                    $part,
                    " \t\n\r\0\x0B.,"
                );

            if (
                !preg_match(
                    '/^[\p{L}][\p{L}\-]*$/u',
                    $part
                )
            ) {
                return false;
            }

            if (
                mb_strlen(
                    str_replace(
                        '-',
                        '',
                        $part
                    )
                ) >= 2
            ) {
                $fullWords++;
            }
        }

        if ($fullWords < 2) {
            return false;
        }

        /*
         * "Женская консультация" формально похожа на два слова,
         * поэтому дополнительно отсекаем несколько типовых общих слов.
         */
        $genericWords = [
            'женская',
            'детская',
            'городская',
            'областная',
            'центральная',
            'районная',
            'клиническая',
            'семейная',
            'частная',
            'многопрофильная',
            'консультативная',
            'диагностическая',
        ];

        foreach ($parts as $part) {
            if (
                in_array(
                    mb_strtolower($part),
                    $genericWords,
                    true
                )
            ) {
                return false;
            }
        }

        return true;
    }

    private function humanizeSpecialities(
        string $value
    ): string {
        if (trim($value) === '') {
            return '';
        }

        $map = [
            'allergology' => 'Аллергология',
            'immunology' => 'Иммунология',
            'anaesthetics' => 'Анестезиология',
            'cardiology' => 'Кардиология',
            'cardiothoracic_surgery' => 'Сердечно-сосудистая хирургия',
            'dermatology' => 'Дерматология',
            'dentistry' => 'Стоматология',
            'endocrinology' => 'Эндокринология',
            'gastroenterology' => 'Гастроэнтерология',
            'general' => 'Общая практика',
            'genetics' => 'Генетика',
            'geriatrics' => 'Гериатрия',
            'gynaecology' => 'Гинекология',
            'haematology' => 'Гематология',
            'infectious_diseases' => 'Инфекционные болезни',
            'nephrology' => 'Нефрология',
            'neurology' => 'Неврология',
            'neurosurgery' => 'Нейрохирургия',
            'nutrition' => 'Диетология',
            'oncology' => 'Онкология',
            'ophthalmology' => 'Офтальмология',
            'orthopaedics' => 'Ортопедия',
            'otolaryngology' => 'Оториноларингология',
            'paediatrics' => 'Педиатрия',
            'proctology' => 'Колопроктология',
            'psychiatry' => 'Психиатрия',
            'psychotherapy' => 'Психотерапия',
            'pulmonology' => 'Пульмонология',
            'radiology' => 'Радиология',
            'rheumatology' => 'Ревматология',
            'surgery' => 'Хирургия',
            'tuberculosis' => 'Фтизиатрия',
            'urology' => 'Урология',
            'oral_surgery' => 'Челюстно-лицевая хирургия',
        ];

        return collect(
            preg_split('/[;,]+/', $value)
        )
            ->map(
                static fn (string $item): string =>
                    trim($item)
            )
            ->filter()
            ->map(
                static function (
                    string $item
                ) use ($map): string {
                    return $map[
                        mb_strtolower($item)
                    ] ?? $item;
                }
            )
            ->unique()
            ->implode(', ');
    }

    private function distanceMeters(
        float $longitude1,
        float $latitude1,
        float $longitude2,
        float $latitude2
    ): float {
        $earthRadius = 6371000.0;

        $lat1 = deg2rad($latitude1);
        $lat2 = deg2rad($latitude2);

        $deltaLat =
            deg2rad(
                $latitude2 - $latitude1
            );

        $deltaLon =
            deg2rad(
                $longitude2 - $longitude1
            );

        $a =
            sin($deltaLat / 2) ** 2
            + cos($lat1)
            * cos($lat2)
            * sin($deltaLon / 2) ** 2;

        $c =
            2
            * atan2(
                sqrt($a),
                sqrt(1 - $a)
            );

        return $earthRadius * $c;
    }

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
