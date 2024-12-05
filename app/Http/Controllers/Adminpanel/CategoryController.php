<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\TopicsCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function listCategories() {
        $categories = TopicsCategory::paginate(10);

        return view('adminpanel.categories', compact('categories'));
    }

    public function editCategory(int $category_id) {
        $category = TopicsCategory::findOrFail($category_id);

        return view('adminpanel.service.category_edit', compact('category'));
    }

    public function createCategory() {
        return view('adminpanel.service.category_new');
    }

    public function storeCategory(Request $request) {
        TopicsCategory::create($request->post());

        return redirect()->route('admin.blog.categories')->with('success', 'Категория была создана.');
    }

    public function saveCategory(Request $request, int $category_id) {

    }
}
