<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use \Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function medicineList(int $groupIdentificator): JsonResponse {
        return response()->json([
            "data" => Drug::whereGroup($groupIdentificator)->with('receptors')->get()
        ]);
    }

    public function getDoctors(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Doctor::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }

    public function getPatient(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Patient::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }

    public function getDiagnoses(Request $request): JsonResponse
    {
        $q   = trim((string) $request->get('q', ''));
        $by  = $request->get('by', 'any');        // any|code|title
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(max((int) $request->get('per_page', 20), 5), 50);
        $offset = ($page - 1) * $perPage;

        // Базовый запрос: id, code, title
        $builder = DB::table('diagnoses')->select('id', 'code', 'title');

        if ($q !== '') {
            // Для pgsql используем ILIKE и unaccent() для названий
            if ($by === 'code') {
                $builder->where('code', 'ILIKE', "%{$q}%");
            } elseif ($by === 'title') {
                $builder->whereRaw('unaccent(title) ILIKE unaccent(?)', ["%{$q}%"]);
            } else {
                $builder->where(function ($w) use ($q) {
                    $w->where('code', 'ILIKE', "%{$q}%")
                        ->orWhereRaw('unaccent(title) ILIKE unaccent(?)', ["%{$q}%"]);
                });
            }
        }

        // Приоритизация результатов: точный код -> начинается с кода -> в коде -> в названии
        $builder->orderByRaw(
        // CASE возвращает «вес»: меньше — выше в списке
            "CASE
            WHEN code = ? THEN 0
            WHEN code ILIKE ? THEN 1
            WHEN code ILIKE ? THEN 2
            WHEN unaccent(title) ILIKE unaccent(?) THEN 3
            ELSE 4
         END, code ASC",
            [
                $q,            // точное совпадение кода
                $q.'%',        // начинается с
                '%'.$q.'%',    // содержит в коде
                '%'.$q.'%',    // содержит в названии
            ]
        );

        $total = (clone $builder)->count();
        $rows  = $builder->offset($offset)->limit($perPage)->get();

        return response()->json([
            'results' => $rows->map(fn($r) => [
                'id'    => $r->id,
                'code'  => $r->code,
                'title' => $r->title,
                'text'  => "{$r->code} — {$r->title}",
            ]),
            'pagination' => ['more' => $page * $perPage < $total],
        ]);
    }

}
