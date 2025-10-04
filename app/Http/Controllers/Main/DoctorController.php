<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicFeedback;
use App\Models\Doctor;
use App\Models\DoctorFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DoctorController extends Controller
{
    public function index(int $doctor_id) {
        $doctor = Doctor::findOrFail($doctor_id);
        $dpTable = $doctor->priceList();

        $address = $doctor->address_job;
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

        $feedbacks = $doctor->feedbacks()->with('user')->paginate(10);

        $totalFeedbacks = array_sum($doctor->feedbackMarksCount());
        $feedbackMarks = array_reverse($doctor->feedbackMarksCount(), true);

        return view('main.forms.doctor', compact('doctor', 'dpTable', 'latitude', 'longitude', 'totalFeedbacks', 'feedbackMarks', 'feedbacks'));
    }

    public function feedback(Request $request, int $doctor_id) {
        if (Doctor::findOrFail($doctor_id)->hasFeedback()) {
            return redirect()->back()->withErrors('Нельзя оставить больше одного отзыва для клиник.');
        }

        $clinicFeedback = new DoctorFeedback();
        $clinicFeedback->user_id = auth()->user()->id;
        $clinicFeedback->doctor_id = $doctor_id;
        $clinicFeedback->positive_feedback = $request->post('positive_feedback');
        $clinicFeedback->negative_feedback = $request->post('negative_feedback');
        $clinicFeedback->description = $request->post('description');
        $clinicFeedback->mark = $request->post('mark');
        $clinicFeedback->save();

        return redirect()->back()->with('success', 'Отзыв был успешно оставлен!');
    }
}
