<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class IndexController extends Controller
{
    public function __invoke() {
        $feedbacks = Feedback::all();

        return view('main.index', compact('feedbacks'));
    }
}
