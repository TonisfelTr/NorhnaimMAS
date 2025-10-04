<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Lawyer;

class JurisprudenceController extends Controller
{
    public function list() {
        $lawyers = Lawyer::paginate(12);

        return view('main.jurisprudence', compact('lawyers'));
    }
}
