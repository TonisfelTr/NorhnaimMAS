<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View {
        $groups = Group::paginate(20);

        return view('adminpanel.groups', compact('groups'));
    }

    public function delete(MassDeleteRequest $request): RedirectResponse {
        $selectedIDs = $request->all()['selected'];

        if (array_intersect($selectedIDs, [1,2,3])) {
            return redirect()->back()->withErrors('Вы не можете удалить группы администраторов, модераторов или пользователей.');
        }

        Group::whereIn('id', $selectedIDs)->delete();

        return redirect()->back()->with([
            'status' => 'group.success',
            'message' => 'Выделенные группы были удалены!'
                                            ]);
    }

    public function edit(int $group_id): View {
        $group = Group::findOrFail($group_id);

        return view('adminpanel.service.group_edit', compact('group'));
    }

    public function save(Request $request, int $group_id): RedirectResponse {
        return Group::findOrFail($group_id)->update($request->post())
            ? redirect()->route('admin.groups')->with('success', 'Изменения в группе сохранены.')
            : redirect()->back()->withErrors('Не удалось сохранить изменения.');
    }

    public function create(): View {
        return view('adminpanel.service.group_new');
    }

    public function store(Request $request): RedirectResponse {
        Group::create($request->post());

        return redirect()->route('admin.groups')->with('success', 'Группа была успешно создана.');
    }
}
