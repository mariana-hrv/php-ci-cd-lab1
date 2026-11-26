<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendViewingRequestNotification;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Support\Facades\DB;

class ViewingRequestService
{
    public function create(User $buyer, array $data): ViewingRequest
    {
        return $buyer->viewingRequests()->create([
            ...$data,
            'status' => 'pending',
        ]);
    }

    public function update(
        ViewingRequest $viewingRequest,
        array $data
    ): ViewingRequest {
        $viewingRequest->update($data);

        return $viewingRequest;
    }

    public function cancel(ViewingRequest $viewingRequest): void
    {
        $viewingRequest->update([
            'status' => 'cancelled',
        ]);
    }

    public function decide(
        ViewingRequest $viewingRequest,
        string $status
    ): void {
        DB::transaction(function () use ($viewingRequest, $status) {
            $viewingRequest->update([
                'status' => $status,
            ]);

            SendViewingRequestNotification::dispatch(
                $viewingRequest->buyer_id,
                $viewingRequest->id,
                $status
            )->afterCommit();
        });
    }
}
