<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/** Only numeric counters are broadcast; never send medical data in WS events. */
final class PatientNotificationCountsChanged implements ShouldBroadcastNow
{
    use InteractsWithSockets;
    use InteractsWithBroadcasting;

    public function __construct(
        public readonly int $userId,
        public readonly int $unreadCount,
        public readonly int $readCount,
    ) {
        // Existing NORHNAIM Reverb server, independent from the default broadcast driver.
        $this->broadcastVia('reverb');
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('patient.notifications.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'patient.notifications.counts.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'unread' => $this->unreadCount,
            'read' => $this->readCount,
        ];
    }
}
