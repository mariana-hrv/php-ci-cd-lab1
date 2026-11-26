<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Listing;
use App\Services\ListingService;
use PHPUnit\Framework\TestCase;

class ListingServiceTest extends TestCase
{
    public function test_service_updates_listing(): void
    {
        $data = [
            'price' => 2700000,
            'status' => 'published',
        ];

        $listing = $this->createMock(Listing::class);

        $listing->expects($this->once())
            ->method('update')
            ->with($data)
            ->willReturn(true);

        $service = new ListingService;

        $result = $service->update($listing, $data);

        $this->assertSame($listing, $result);
    }

    public function test_service_deletes_listing(): void
    {
        $listing = $this->createMock(Listing::class);

        $listing->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $service = new ListingService;

        $service->delete($listing);
    }
}
