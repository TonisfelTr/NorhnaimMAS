<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicManipulationRequest;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Image\Image;

class ClinicController extends Controller
{
    public function index(): View {
        $clinics = Clinic::whereNot('id', 0)->paginate(20);

        return view('adminpanel.sub-dictionaries.clinic', compact('clinics'));
    }

    public function store(ClinicManipulationRequest $request): RedirectResponse|View {
        if ($request->getMethod() == 'POST') {
            $data = $request->post();

            $clinic = new Clinic();
            $clinic->name = $data['name'];
            $clinic->address = $data['address'];
            $clinic->description = $data['description'];
            $clinic->save();

            return redirect()->route('admin.dictionary.clinics')->with([
                                                                           'status' => 'clinics.success',
                                                                           'message' => "Клиника \"{$clinic->name}\" была успешно добавлена!"
                                                                       ]);
        } else {
            return view('adminpanel.sub-dictionaries.clinic_new');
        }
    }

    public function edit(int $clinic_id) {
        $clinic = Clinic::findOrFail($clinic_id);
        $services = DB::table('services')->where('clinic_id', $clinic_id)->get();

        return view('adminpanel.service.clinic_edit', compact('clinic', 'services'));
    }

    public function save(Request $request, int $clinic_id): RedirectResponse
    {
        $clinic = Clinic::with('photos')->findOrFail($clinic_id);

        // Обработка загрузки фотографий
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $key => $photo) {
                // Оригинальное изображение
                $originalExtension = $photo->getClientOriginalExtension();
                $filename = uniqid() . '.' . $originalExtension;
                $filePath = $photo->storeAs('clinic_photos', $filename, 'public');

                // Конвертация в WebP
                $webpFilename = uniqid() . '.webp';
                $webpPath = storage_path('app/public/clinic_photos/' . $webpFilename);

                Image::load($photo->getRealPath())
                     ->format('webp')
                     ->width(1024) // Измените ширину, если нужно
                     ->save($webpPath);

                // Сохранение записи в базе данных
                $clinic->photos()->create([
                    'photo' => $filePath,
                    'is_cover' => $key == $request->input('cover_photo_index'), // Укажите обложку
                ]);
            }
        }

        // Обновление данных клиники
        $clinic->name = $request->post('name');
        $clinic->description = $request->post('description');
        $clinic->address = $request->post('address');
        $clinic->phone = $request->post('phone');
        $clinic->save();

        // Обновление услуг
        $servicesTable = DB::table('services');
        $names = $request->post('services');

        $services = array_map(function ($name) use ($clinic_id) {
            return [
                'clinic_id' => $clinic_id,
                'name' => $name,
            ];
        }, $names ?? []);

        $servicesTable->where('clinic_id', $clinic_id)->delete();
        $servicesTable->insert($services);

        return redirect()->route('admin.dictionary.clinics')->with('success', 'Изменения были успешно сохранены!');
    }
}
