<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
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
        $data = $request->validate([
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $eventId = $request->route('event_id');
        $event = Event::findOrFail($eventId);

        $user = $request->user();
        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return $this->respondForbidden('You can only manage tickets for your own events');
        }

        $data['event_id'] = $event->id;

        $ticket = Ticket::create($data);

        return $this->respondCreatedWithPayload(
            new TicketResource($ticket),
            'Ticket created successfully'
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
        $ticket = Ticket::with('event')->findOrFail($id);
        $user = $request->user();

        if ($user->role === 'organizer' && $ticket->event->created_by !== $user->id) {
            return $this->respondForbidden('You can only update tickets for your own events');
        }

        $data = $request->validate([
            'type' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
        ]);

        $ticket->update($data);

        return $this->respondUpdatedWithPayload(
            new TicketResource($ticket->fresh()),
            'Ticket updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ticket = Ticket::with('event')->findOrFail($id);
        $user = request()->user();

        if ($user->role === 'organizer' && $ticket->event->created_by !== $user->id) {
            return $this->respondForbidden('You can only delete tickets for your own events');
        }

        $ticket->delete();

        return $this->respondDeleted('Ticket deleted successfully');
    }
}
