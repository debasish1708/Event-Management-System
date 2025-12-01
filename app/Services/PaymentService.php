<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;
use Exception;

class PaymentService {
    /**
     * Simulate a payment.
     * Returns Payment instance.
     */
    public function process(Booking $booking, float $amount, array $meta = []): Payment {
        // Simulate success roughly 80% of time
        $success = rand(1,100) <= 80;

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'status' => $success ? 'success' : 'failed',
            'transaction_id' => Str::uuid(),
        ]);

        if($success){
            $booking->status = 'confirmed';
            $booking->save();
        } else {
            $booking->status = 'pending';
            $booking->save();
        }
        return $payment;
    }
}
