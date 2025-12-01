<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $admins = User::factory(2)->admin()->create();
    $organizers =User::factory()->count(3)->organizer()->create();
    $customers = User::factory()->count(10)->create();
    // User::factory()->create([
    //   'name' => 'Test User',
    //   'email' => 'test@example.com',
    // ]);

    $events = collect();
    foreach(range(1,5) as $i){
        $creator = $organizers->random();
        $e = \App\Models\Event::factory()->create(['created_by' => $creator->id]);
        $events->push($e);
    }

    // tickets 15
    foreach($events as $event){
        Ticket::factory()->count(3)->create(['event_id'=>$event->id]);
    }

    // bookings 20 (random combinations)
    $tickets = Ticket::all();
    foreach(range(1,20) as $i){
        $customer = $customers->random();
        $ticket = $tickets->random();
        $qty = rand(1,3);
        $booking = Booking::create([
            'user_id'=>$customer->id,
            'ticket_id'=>$ticket->id,
            'quantity'=>$qty,
            'status'=> (rand(0,1) ? 'confirmed' : 'pending'),
        ]);
        Payment::create([
            'booking_id'=>$booking->id,
            'amount'=> $ticket->price * $qty,
            'status'=>($booking->status === 'confirmed' ? 'success' : 'failed'),
            'transaction_id'=>\Illuminate\Support\Str::uuid()
        ]);
    }
  }
}
