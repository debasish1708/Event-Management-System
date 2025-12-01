<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmed;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // Not used – specific endpoint is pay()
        return $this->respondMethodNotAllowed('Use /bookings/{id}/payment to process a payment');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::with('booking.ticket.event')->findOrFail($id);

        return $this->respondWithMessageAndPayload(
            $payment,
            'Payment retrieved successfully'
        );
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->respondMethodNotAllowed('Deleting payments is not supported');
    }

    /**
     * Process (mock) payment for a booking.
     */
    public function pay(Request $request, string $id, PaymentService $paymentService)
    {
        $user = $request->user();
        $booking = Booking::with('ticket.event', 'payment')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return $this->respondBadRequest('Cannot pay for a cancelled booking');
        }

        if ($booking->payment && $booking->payment->status === 'success') {
            return $this->respondBadRequest('Booking already paid successfully');
        }

        $amount = $booking->ticket->price * $booking->quantity;

        $payment = $paymentService->process($booking, $amount);

        // Notify on successful confirmation
        if ($payment->status === 'success') {
            $booking->fresh('ticket.event');
            $user->notify(new BookingConfirmed($booking));
        }

        return $this->respondWithMessageAndPayload(
            $payment->fresh('booking.ticket.event'),
            'Payment processed (mock) successfully'
        );
    }
}
