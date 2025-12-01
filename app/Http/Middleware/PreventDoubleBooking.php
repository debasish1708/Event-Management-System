<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ticketId = $request->route('id') ?? $request->ticket_id ?? $request->route('ticket');

        if(!$user || !$ticketId) return $next($request);

        $exists = Booking::where('user_id',$user->id)
            ->where('ticket_id',$ticketId)
            ->whereIn('status', ['pending','confirmed'])
            ->exists();
            
        if($exists){
            return response()->json(['message'=>'User already has a pending/confirmed booking for this ticket'], 422);
        }
        return $next($request);
    }
}
