<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\MassDeleteRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View {
        $search = $request->get('search');

        $groups = Group::when($search, function ($query, $search) {
            $query->where('name', 'ilike', "%$search%");
        })->paginate(20);

        return view('adminpanel.groups', compact('groups'));
    }

    public function delete(int $group_id): RedirectResponse {
        if (in_array($group_id, [1, 2, 3])) {
            return redirect()->back()->withErrors(__('Вы не можете удалить группы администраторов, модераторов или пользователей.'));
        }

        if (!group()->group_remove) {
            return redirect()->back()->withErrors(__('У вас недостаточно прав.'));
        }

        Group::findOrFail($group_id)->delete();

        return redirect()->back()->with([
            'status' => 'group.success',
            'message' => __('Группа была удалена.'),
        ]);
    }

    public function massDelete(MassDeleteRequest $request): RedirectResponse {
        if (!group()->group_remove) {
            return redirect()->back()->withErrors(__('У вас недостаточно прав.'));
        }

        $selectedIDs = $request->validated()['selected'];

        if (array_intersect($selectedIDs, [1, 2, 3])) {
            return redirect()->back()->withErrors(__('Вы не можете удалить группы администраторов, модераторов или пользователей.'));
        }

        Group::whereIn('id', $selectedIDs)->delete();

        return redirect()->back()->with([
            'status' => 'group.success',
            'message' => __('Выделенные группы были удалены!'),
        ]);
    }

    public function edit(int $group_id): View {
        $group = Group::findOrFail($group_id);

        return view('adminpanel.service.group_edit', compact('group'));
    }

    public function save(Request $request, int $group_id): RedirectResponse {
        $group = Group::findOrFail($group_id);

        $updated = $group->update($request->post());

        return $updated
            ? redirect()->route('admin.groups')->with('success', __('Изменения в группе сохранены.'))
            : redirect()->back()->withErrors(__('Не удалось сохранить изменения.'));
    }

    public function create(): View {
        return view('adminpanel.service.group_new');
    }

    public function store(GroupStoreRequest $request): RedirectResponse {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Group::create($validated);

        return redirect()->route('admin.groups')->with('success', __('Группа была успешно создана.'));
    }
}
