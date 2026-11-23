<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Notifications\DoctorTelemedicineReminderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TelemedicineReminderInboxController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->hasRole('doctors') && $user->doctor()->exists(), 403);
        $items = $user->notifications()
            ->where('type', DoctorTelemedicineReminderNotification::class)
            ->latest()
            ->paginate(20);
        return view('doctors.telemedicine.reminders', compact('items'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasRole('doctors') && $user->doctor()->exists(), 403);
        $notification = $user->notifications()
            ->where('type', DoctorTelemedicineReminderNotification::class)
            ->whereKey($id)->firstOrFail();
        $notification->markAsRead();
        return redirect()->route('doctors.telemedicine-reminders.index');
    }
}
