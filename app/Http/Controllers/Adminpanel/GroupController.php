<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDeleteRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
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
}
