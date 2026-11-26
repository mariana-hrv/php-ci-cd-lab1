<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendViewingRequestNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $buyerId,
        public readonly int $viewingRequestId,
        public readonly string $status
    ) {}

    public function handle(): void
    {
        $decision = $this->status === 'confirmed'
            ? 'підтверджено'
            : 'відхилено';

        Notification::create([
            'user_id' => $this->buyerId,
            'message' => "Вашу заявку №{$this->viewingRequestId} на перегляд нерухомості {$decision} продавцем.",
            'type' => 'viewing_request',
            'is_read' => false,
        ]);
    }
}
