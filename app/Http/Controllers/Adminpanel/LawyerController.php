<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class LawyerController extends Controller
{
    public function list() {
        $lawyers = Lawyer::paginate(12);

        return view('adminpanel.lawyers', compact('lawyers'));
    }
}
