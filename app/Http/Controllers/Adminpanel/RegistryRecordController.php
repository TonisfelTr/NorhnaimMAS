<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\RegistryRecord;
use Illuminate\View\View;

class RegistryRecordController extends Controller
{
    public function index(): View {
        $records = RegistryRecord::paginate(20);

        return view('adminpanel.registry', compact('records'));
    }
}
