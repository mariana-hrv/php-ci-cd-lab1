<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Listing;
use App\Models\ModerationRecord;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    public function moderate(array $data, int $adminId): ModerationRecord
    {
        return DB::transaction(function () use ($data, $adminId) {
            $listing = Listing::findOrFail($data['listing_id']);

            $record = $listing->moderationRecords()->create([
                'admin_id' => $adminId,
                'decision' => $data['decision'],
                'comment' => $data['comment'] ?? null,
            ]);

            $listing->update([
                'status' => $data['decision'] === 'approved'
                    ? 'published'
                    : 'draft',
            ]);

            return $record->load([
                'listing.property',
                'admin:id,name',
            ]);
        });
    }
}
