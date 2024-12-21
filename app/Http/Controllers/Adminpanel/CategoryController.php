<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Http\Requests\CategoriesUpdateRequest;
use App\Models\TopicsCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function list(Request $request) {
        if ($search = $request->get('search')) {
            $categories = TopicsCategory::where('name', 'ilike', "%$search%")->paginate(10);
        } else {
            $categories = TopicsCategory::paginate(10);
        }

        return view('adminpanel.categories', compact('categories'));
    }

    public function edit(int $category_id) {
        $category = TopicsCategory::findOrFail($category_id);

        return view('adminpanel.service.category_edit', compact('category'));
    }

    public function create() {
        return view('adminpanel.service.category_new');
    }

    public function store(Request $request) {
        if (!group()->blog_new_category) {
            return redirect()->back()->withErrors('У вас недостаточно прав.');
        }

        TopicsCategory::create($request->post());

        return redirect()->route('admin.blog.categories')->with('success', 'Категория была создана.');
    }

    public function save(CategoriesUpdateRequest $request, int $category_id) {
        $category = TopicsCategory::findOrFail($category_id);
        $category->name = $request->post('name');
        $category->save();

        return redirect()->route('admin.blog.categories')->with([
            'status' => 'categories.success',
            'message' => 'Категория была успешно отредактирована.'
        ]);
    }

    public function delete(int $category_id) {
        if (!group()->blog_remove_category) {
            return redirect()->back()->withErrors('У вас недостаточно прав.');
        }

        TopicsCategory::findOrFail($category_id)->delete();

        return redirect()->back()->with([
            'status' => 'categories.success',
            'message' => 'Категория была успешно удалена.'
        ]);
    }

    public function massDelete(MassDeleteRequest $request) {
        if (!group()->blog_remove_category) {
            return redirect()->back()->withErrors('У вас недостаточно прав.');
        }

        TopicsCategory::whereIn('id', $request->post('selected'))->delete();

        return redirect()->back()->with([
            'status' => 'categories.success',
            'message' => 'Категории были успешно удалены.'
        ]);
    }
}
