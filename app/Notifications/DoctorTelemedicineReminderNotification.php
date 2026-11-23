<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class DoctorTelemedicineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $label,
        private readonly string $url,
        private readonly int $clinicId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'category' => 'telemedicine',
            'title' => 'Скоро видеоконсультация',
            'message' => 'Видеосеанс назначен на ' . $this->label . '. Проверьте готовность подключения.',
            'url' => $this->url,
            'icon' => 'camera-video',
            'clinic_id' => $this->clinicId,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
