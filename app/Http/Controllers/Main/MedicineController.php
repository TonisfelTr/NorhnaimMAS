<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;

class MedicineController extends Controller
{
    public function __invoke() {
        return view('main.medicines');
    }
}
