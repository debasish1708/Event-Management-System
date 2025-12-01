<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();

        $bookings = Booking::with(['ticket.event', 'payment'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->respondWithMessageAndPayload(
            $bookings,
            'Bookings retrieved successfully'
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ticketId = $request->route('id');
        $ticket = Ticket::findOrFail($ticketId);
        $user = $request->user();

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Basic availability check (no overbooking)
        $alreadyBookedQty = Booking::where('ticket_id', $ticket->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->sum('quantity');

        $available = max(0, $ticket->quantity - $alreadyBookedQty);

        if ($data['quantity'] > $available) {
            return $this->respondValidationError('Not enough tickets available', [
                'available' => $available,
            ]);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => $data['quantity'],
            'status' => 'pending',
        ]);

        return $this->respondCreatedWithPayload(
            $booking->load('ticket.event'),
            'Booking created successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Not used – cancellation has a dedicated endpoint.
        return $this->respondMethodNotAllowed('Use /bookings/{id}/cancel to cancel a booking');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->respondMethodNotAllowed('Direct deletion is not supported; cancel the booking instead');
    }

    /**
     * Cancel a booking (customer).
     */
    public function cancel(Request $request, string $id)
    {
        $user = $request->user();
        $booking = Booking::with('payment')->where('user_id', $user->id)->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return $this->respondBadRequest('Booking already cancelled');
        }

        $booking->status = 'cancelled';
        $booking->save();

        if ($booking->payment) {
            $booking->payment->status = 'refunded';
            $booking->payment->save();
        }

        return $this->respondUpdatedWithPayload(
            $booking->fresh('ticket.event', 'payment'),
            'Booking cancelled successfully'
        );
    }
}
