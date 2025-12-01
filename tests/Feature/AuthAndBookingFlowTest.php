<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndBookingFlowTest extends TestCase
{
    use WithFaker;

    public function test_registration_and_login()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ];

        $res = $this->postJson('/v1/api/register', $payload);
        $res->assertStatus(200)
            ->assertJsonFragment(['result' => true]);

        $loginRes = $this->postJson('/v1/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonStructure(['payload' => ['access_token']]);
    }

    public function test_organizer_can_create_event_and_ticket()
    {
        $organizer = User::factory()->organizer()->create();

        Sanctum::actingAs($organizer);

        $eventRes = $this->postJson('/v1/events', [
            'title' => 'My Event',
            'description' => 'Desc',
            'date' => now()->addDays(5)->toISOString(),
            'location' => 'City',
        ]);

        $eventRes->assertStatus(201);
        $eventId = $eventRes->json('payload.id');

        $ticketRes = $this->postJson("/v1/events/{$eventId}/tickets", [
            'type' => 'VIP',
            'price' => 150,
            'quantity' => 10,
        ]);

        $ticketRes->assertStatus(201);
    }

    public function test_customer_can_book_and_pay_for_ticket()
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 20,
        ]);

        $customer = User::factory()->create();
        Sanctum::actingAs($customer);

        $bookingRes = $this->postJson("/v1/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ]);

        $bookingRes->assertStatus(201);
        $bookingId = $bookingRes->json('payload.id');

        $payRes = $this->postJson("/v1/bookings/{$bookingId}/payment", []);
        $payRes->assertStatus(200)
            ->assertJsonFragment(['result' => true]);
    }
}


