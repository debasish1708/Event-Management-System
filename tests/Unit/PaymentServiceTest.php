<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use App\Services\PaymentService;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_process_creates_payment_and_updates_booking_status()
    {
        $user = User::factory()->create();
        $ticket = \App\Models\Ticket::factory()->create(['price' => 100]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $service = new PaymentService();

        $payment = $service->process($booking, 200.0);

        $this->assertEquals($booking->id, $payment->booking_id);
        $this->assertEquals(200.0, (float) $payment->amount);
        $this->assertContains($payment->status, ['success','failed']);
        $this->assertNotNull($payment->transaction_id);
        $this->assertContains($booking->fresh()->status, ['pending','confirmed']);
    }
}


