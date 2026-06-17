<?php

namespace Database\Seeders;

use App\Actions\CreateBooking;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Variant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(CreateBooking $createBooking): void
    {
        $today = CarbonImmutable::today();
        $nextSaturday = $today->next(CarbonInterface::SATURDAY);
        $futureMonday = $today->next(CarbonInterface::MONDAY)->addWeek();

        $this->createBooking(
            $createBooking,
            marker: 'pending-weekend-chairs',
            customer: [
                'name' => 'Lerato Mokoena',
                'email' => 'lerato@example.test',
                'phone' => '0825550101',
            ],
            collectionDate: $nextSaturday,
            returnDate: $nextSaturday->addDay(),
            sku: 'CHAIR-FOLD-WHT',
            quantity: 20,
            status: BookingStatus::Pending,
        );

        $this->createBooking(
            $createBooking,
            marker: 'confirmed-four-day-table-hire',
            customer: [
                'name' => 'Thabo Ndlovu',
                'email' => 'thabo@example.test',
                'phone' => '0835550102',
            ],
            collectionDate: $futureMonday,
            returnDate: $futureMonday->addDays(3),
            sku: 'TABLE-RND-180',
            quantity: 8,
            status: BookingStatus::Confirmed,
        );

        $this->createBooking(
            $createBooking,
            marker: 'collected-pa-speakers',
            customer: [
                'name' => 'Ayesha Khan',
                'email' => 'ayesha@example.test',
                'phone' => '0845550103',
            ],
            collectionDate: $today->subDay(),
            returnDate: $today->addDay(),
            sku: 'AUDIO-PA-12',
            quantity: 2,
            status: BookingStatus::Collected,
        );

        $this->createBooking(
            $createBooking,
            marker: 'completed-linen-hire',
            customer: [
                'name' => 'Sipho Dlamini',
                'email' => 'sipho@example.test',
                'phone' => '0715550104',
            ],
            collectionDate: $today->subWeeks(3),
            returnDate: $today->subWeeks(3)->addDays(3),
            sku: 'LINEN-RECT-24-WHT',
            quantity: 12,
            status: BookingStatus::Completed,
        );

        $this->createBooking(
            $createBooking,
            marker: 'released-festoon-lights',
            customer: [
                'name' => 'Naledi Jacobs',
                'email' => 'naledi@example.test',
                'phone' => '0725550105',
            ],
            collectionDate: $futureMonday->addWeeks(2),
            returnDate: $futureMonday->addWeeks(2)->addDays(2),
            sku: 'LIGHT-FEST-20',
            quantity: 6,
            status: BookingStatus::Released,
        );
    }

    /**
     * @param  array{name: string, email: string, phone: string}  $customer
     */
    private function createBooking(
        CreateBooking $createBooking,
        string $marker,
        array $customer,
        CarbonImmutable $collectionDate,
        CarbonImmutable $returnDate,
        string $sku,
        int $quantity,
        BookingStatus $status,
    ): void {
        $notes = "Seeded demo booking: {$marker}";

        if (Booking::query()->where('notes', $notes)->exists()) {
            return;
        }

        $variant = Variant::query()->where('sku', $sku)->firstOrFail();

        $booking = $createBooking->execute([
            'customer' => $customer,
            'collection_date' => $collectionDate,
            'return_date' => $returnDate,
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                ],
            ],
            'notes' => $notes,
        ]);

        $updates = ['status' => $status->value];

        if (in_array($status, [BookingStatus::Collected, BookingStatus::Completed], true)) {
            $updates['collected_at'] = $collectionDate->setTime(9, 0);
        }

        if ($status === BookingStatus::Completed) {
            $updates['returned_at'] = $returnDate->setTime(16, 0);
        }

        $booking->update($updates);
    }
}
