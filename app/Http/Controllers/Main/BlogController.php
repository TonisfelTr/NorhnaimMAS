<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\TopicsCategory;

class BlogController extends Controller
{
    public function list(int $category_id = null) {
        $categories = TopicsCategory::all();

        if (!is_null($category_id)) {
            $topics = Topic::where('topics_category_id', $category_id)
                           ->with('topics_category')
                           ->paginate(20);
        } else {
            $topics = Topic::with('topics_category')->paginate(20);
        }

        return view('main.blog', compact('categories', 'topics', 'category_id'));
    }

    public function index(int $topic_id) {
        $topic = Topic::findOrFail($topic_id);

        return view('main.forms.topic', compact('topic'));
    }
}
