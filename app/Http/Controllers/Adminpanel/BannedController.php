<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Banned;
use Illuminate\View\View;

class BannedController extends Controller
{
    public function index(): View {
        $banneds = Banned::paginate(20);

        return view('adminpanel.sub-users.banned', compact('banneds'));
    }
}
