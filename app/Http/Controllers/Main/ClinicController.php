<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Support\Facades\Http;

class ClinicController extends Controller
{
    public function list() {
        $clinics = Clinic::paginate(12, ['*'], 'clinics_page');
        $doctors = Doctor::paginate(12, ['*'], 'doctors_page');

        return view('main.clinics', compact('clinics', 'doctors'));
    }

    public function index(int $clinic_id) {
        $clinic = Clinic::findOrFail($clinic_id);

        $address = $clinic->address;
        $apiKey = env('YANDEX_MAPS_API_KEY');

        $response = Http::get("https://geocode-maps.yandex.ru/1.x/", [
            'apikey' => $apiKey,
            'format' => 'json',
            'geocode' => $address,
        ]);

        $responseData = $response->json();

        if (!isset($responseData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'])) {
            throw new \Exception('Не удалось получить координаты для адреса: ' . $address);
        }

        $geoObject = $responseData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
        $coordinates = explode(' ', $geoObject['Point']['pos']);

        $latitude = $coordinates[1];
        $longitude = $coordinates[0];

        return view('main.forms.clinic', compact('clinic', 'address', 'latitude', 'longitude'));
    }
}
