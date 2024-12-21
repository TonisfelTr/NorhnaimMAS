<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicManipulationRequest;
use App\Http\Requests\ClinicUpdateRequest;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Image\Image;

class ClinicController extends Controller
{
    public function index(Request $request): View {
        if ($request->has('search')) {
            $search = $request->get('search');
            $clinics = Clinic::whereNot('id', 0)
                ->where('name', 'ilike', "%$search%")
                ->orWhere('phone', 'ilike', "%$search%")
                ->orWhere('city', 'ilike', "%$search%")
                ->paginate(20);
        } else {
            $clinics = Clinic::whereNot('id', 0)->paginate(20);
        }

        return view('adminpanel.sub-dictionaries.clinic', compact('clinics'));
    }

    public function create(): View {
        // $services =
        return view('adminpanel.service.clinic_new');
    }

    public function store(ClinicManipulationRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            // Получение данных из запроса
            $data = $request->validated();

            // Создание новой клиники
            $clinic = Clinic::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'],
            ]);

            // Обработка загрузки фотографий
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $key => $photo) {
                    $originalExtension = $photo->getClientOriginalExtension();
                    $filename = uniqid() . '.' . $originalExtension;
                    $filePath = $photo->storeAs('clinic_photos', $filename, 'public');

                    // Конвертация в WebP
                    $webpFilename = uniqid() . '.webp';
                    $webpPath = storage_path('app/public/clinic_photos/' . $webpFilename);

                    Image::load($photo->getRealPath())
                         ->format('webp')
                         ->width(1024)
                         ->save($webpPath);

                    $clinic->photos()->create([
                        'photo' => $filePath,
                        'is_cover' => $key == $request->input('cover_photo_index'),
                    ]);
                }
            }

            // Сохранение услуг
            if (!empty($data['services'])) {
                $services = collect($data['services'])->map(function ($name) use ($clinic) {
                    return [
                        'clinic_id' => $clinic->id,
                        'name' => $name,
                    ];
                })->toArray();

                DB::table('services')->insert($services);
            }
        });

        return redirect()->route('admin.dictionary.clinics')->with([
            'status' => 'clinics.success',
            'message' => "Клиника \"{$request->post('name')}\" была успешно добавлена!",
        ]);
    }

    public function edit(int $clinic_id) {
        $clinic = Clinic::findOrFail($clinic_id);
        $services = DB::table('services')->where('clinic_id', $clinic_id)->get();

        return view('adminpanel.service.clinic_edit', compact('clinic', 'services'));
    }

    public function save(ClinicUpdateRequest $request, int $clinic_id): RedirectResponse
    {
        $clinic = Clinic::with('photos')->findOrFail($clinic_id);

        DB::transaction(function () use ($request, $clinic, $clinic_id) {
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $key => $photo) {
                    $originalExtension = $photo->getClientOriginalExtension();
                    $filename = uniqid() . '.' . $originalExtension;
                    $filePath = $photo->storeAs('clinic_photos', $filename, 'public');

                    $webpFilename = uniqid() . '.webp';
                    $webpPath = storage_path('app/public/clinic_photos/' . $webpFilename);

                    Image::load($photo->getRealPath())
                         ->format('webp')
                         ->width(1024)
                         ->save($webpPath);

                    $clinic->photos()->create([
                        'photo' => $filePath,
                        'is_cover' => $key == $request->input('cover_photo_index'),
                    ]);
                }
            }

            $clinic->update([
                'name' => $request->post('name'),
                'description' => $request->post('description'),
                'address' => $request->post('address'),
                'phone' => $request->post('phone'),
            ]);

            $services = collect($request->post('services', []))->map(function ($name) use ($clinic_id) {
                return [
                    'clinic_id' => $clinic_id,
                    'name' => $name,
                ];
            })->toArray();

            DB::table('services')->where('clinic_id', $clinic_id)->delete();
            if (!empty($services)) {
                DB::table('services')->insert($services);
            }
        });

        return redirect()->route('admin.dictionary.clinics')
                         ->with('success', 'Изменения были успешно сохранены!');
    }


}
