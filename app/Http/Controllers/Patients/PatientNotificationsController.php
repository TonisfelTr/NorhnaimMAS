<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PatientNotificationsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->patientUser($request);
        $patient = $user->patient()->firstOrFail();

        $filter = (string) $request->query('filter', 'all');
        if (!in_array($filter, ['all', 'unread'], true)) {
            $filter = 'all';
        }

        $query = $user->notifications()->latest();
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('patients.notifications.index', [
            'patient' => $patient,
            'notifications' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['count' => $this->patientUser($request)->unreadNotifications()->count()]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $user = $this->patientUser($request);
        $item = $user->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $target = data_get($item->data, 'url');
        if (!is_string($target) || !Str::startsWith($target, '/') || Str::startsWith($target, '//')) {
            $target = route('patients.notifications.index', [], false);
        }

        return redirect($target);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $this->patientUser($request);
        $user->unreadNotifications()->update(['read_at' => now(), 'updated_at' => now()]);

        return redirect()->route('patients.notifications.index')
            ->with('status', 'Все уведомления отмечены как прочитанные.');
    }

    private function patientUser(Request $request): object
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients') && $user->patient()->exists(), 403);

        return $user;
    }
}
