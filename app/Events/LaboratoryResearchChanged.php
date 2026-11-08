<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A deliberately small notification. Never broadcast patient names, diagnoses,
 * laboratory values or other protected medical information.
 *
 * The existing default queue worker dispatches the broadcast to Reverb.
 */
final class LaboratoryResearchChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // Independent fast queue: slow LLM jobs in the RabbitMQ default queue
    // must not delay laboratory notifications.
    public string $connection = 'redis';
    public string $queue = 'laboratory-broadcasts';

    public function __construct(
        private readonly int $clinicId,
        private readonly int $researchId,
        private readonly string $change,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('laboratory.clinic.' . $this->clinicId);
    }

    public function broadcastAs(): string
    {
        return 'laboratory.research.changed';
    }

    /** @return array{research_id: int, change: string} */
    public function broadcastWith(): array
    {
        return [
            'research_id' => $this->researchId,
            'change' => $this->change,
        ];
    }
}
