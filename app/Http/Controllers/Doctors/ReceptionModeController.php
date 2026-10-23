<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Совместимость с маршрутом, который уже использует контроллер режимов.
 * Область поиска и переход к оформлению определяются в одном месте.
 */
class ReceptionModeController extends Controller
{
    public function index(Request $request): View
    {
        return app(ReceptionController::class)->index($request);
    }
}
