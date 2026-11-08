<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Reuse the EXACT filters and clinic scoping already in WorkspaceController.
 * The event itself never carries patient data; this GET enforces clinic access.
 */
final class RealtimeFragmentController extends Controller
{
    public function __invoke(Request $request, WorkspaceController $workspace): Response
    {
        $indexView = $workspace->index($request);

        return response()->view('laboratory.partials.researches-table', [
            'researches' => $indexView->getData()['researches'],
        ])->header('Cache-Control', 'private, no-store');
    }
}
