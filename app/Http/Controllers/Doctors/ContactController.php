<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientContactRequest;
use App\Models\Contact;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StorePatientContactRequest $request): RedirectResponse
    {
        Contact::create($request->all());

        return redirect()->back()->with([
            'result' => 'success',
            'message' => 'Контакт успешно добавлен.'
        ], 200);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'result' => 'success',
            'message' => 'Контакт успешно удалён.'
        ]);
    }
}
