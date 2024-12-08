<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Topic;

class TopicController extends Controller
{
    public function list() {
        $topics = Topic::paginate(20);

        return view('adminpanel.topic', compact('topics'));
    }
}
