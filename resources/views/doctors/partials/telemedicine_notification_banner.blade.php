@once
<link rel="stylesheet" href="{{ asset('css/doctor-telemedicine-reminders.css') }}?v=1">
@endonce
@if(auth()->user()?->hasRole('doctors') && \Illuminate\Support\Facades\Schema::hasTable('notifications'))
    @php
        $tmrnRecent = auth()->user()->unreadNotifications()
            ->where('type', \App\Notifications\DoctorTelemedicineReminderNotification::class)
            ->latest()->limit(3)->get();
    @endphp
    @if($tmrnRecent->isNotEmpty())
        <div class="tmrn-alert" role="status">
            <i class="bi bi-camera-video" aria-hidden="true"></i>
            <div>
                <strong>Скоро видеоконсультация</strong>
                @foreach($tmrnRecent as $notice)
                    <p>{{ $notice->data['message'] ?? 'Подготовьтесь к предстоящему сеансу.' }}</p>
                @endforeach
            </div>
            <a href="{{ route('doctors.telemedicine-reminders.index') }}">Уведомления</a>
        </div>
    @endif
@endif
