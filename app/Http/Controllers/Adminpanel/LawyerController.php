<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\LawyerCreateRequest;
use App\Http\Requests\LawyerUpdateRequest;
use App\Http\Requests\MassDeleteRequest;
use App\Models\Lawyer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LawyerController extends Controller
{
    public function index(Request $request) {
        if ($request->has('search')) {
            $search = $request->get('search');
            $lawyers = Lawyer::where('name', 'ilike', "%$search%")
                ->orWhere('surname', 'ilike', "%$search%")
                ->orWhere('phone', 'ilike', "%$search%")
                ->orWhere('profession', 'ilike', "%$search%")
                ->paginate(20);
        } else {
            $lawyers = Lawyer::paginate(20);
        }

        return view('adminpanel.lawyers', compact('lawyers'));
    }

    public function create(): View {
        return view('adminpanel.service.lawyers_new');
    }

    public function store(LawyerCreateRequest $request): RedirectResponse {
        Lawyer::create($request->validated());

        return redirect()->back()->with([
            'status' => 'lawyers.success',
            'message' => 'Запись юриста была успешно создана.'
        ]);
    }

    public function delete(int $lawyer_id): RedirectResponse {
        if (!(is_authed() && group()->lawyer_remove)) {
            return redirect()->route('admin.jurisprudence.lawyers')->withErrors('У вас недостаточно прав');
        }

        if ($lawyer = Lawyer::find($lawyer_id)) {
            $lawyer->delete();
        } else {
            return redirect()->route('admin.jurisprudence.lawyers')->withErrors("Запись юриста с ID $lawyer_id не найдена.");
        }

        return redirect()->back()->with([
            'message' => 'Запись была успешно удалена.'
        ]);
    }

    public function massDelete(MassDeleteRequest $request): RedirectResponse {
        if (!(is_authed() && group()->lawyer_remove)) {
            return redirect()->route('admin.jurisprudence.lawyers')->withErrors('У вас недостаточно прав');
        }

        Lawyer::whereIn('id', $request->post('selected'))->delete();

        return redirect()->route('admin.jurisprudence.lawyers')->with([
            'message' => 'Выделенные записи были успешно удалены.'
        ]);
    }

    public function edit(int $lawyer_id) {
        if ($lawyer = Lawyer::find($lawyer_id)) {
            return view('adminpanel.service.lawyers_edit', compact('lawyer'));
        }

        return redirect()->route('admin.jurisprudence.lawyers')->withErrors("Запись юриста с ID $lawyer_id не найдена.");
    }

    public function update(LawyerUpdateRequest $request, int $lawyer_id): RedirectResponse {
        if (!$lawyer = Lawyer::find($lawyer_id)) {
            return redirect()->route('admin.jurisprudence.lawyers')->withErrors("Запись юриста с ID $lawyer_id не найдена.");
        }

        $lawyer->update($request->validated());

        return redirect()->route('admin.jurisprudence.lawyers')->with([
            'message' => 'Запись юриста была успешно отредактирована.'
        ]);
    }
}
