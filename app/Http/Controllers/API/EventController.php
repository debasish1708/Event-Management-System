<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventCollection;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->get('page',1);
        $cacheKey = "events:page:$page:".md5(json_encode($request->query()));
        $events = Cache::remember($cacheKey, 60, function() use ($request){
            $q = Event::query()->with('tickets');
            $q->searchByTitle($request->get('search'));
            $q->filterByDate($request->get('from'), $request->get('to'));
            if($request->has('location')) {
                $q->where('location', $request->get('location'));
            }
            return $q->orderBy('date','asc')->paginate(10);
        });
        return $this->respondWithMessageAndPayload(new EventCollection($events), 'Events retrieved successfully');
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
        ]);

        $data['created_by'] = $request->user()->id;

        $event = Event::create($data);

        // Invalidate cached lists
        Cache::flush();

        return $this->respondCreatedWithPayload(
            new EventResource($event),
            'Event created successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('tickets');

        return $this->respondWithMessageAndPayload(
            new EventResource($event),
            'Event fetched successfully'
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
        $event = Event::findOrFail($id);

        $user = $request->user();
        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return $this->respondForbidden('You can only update your own events');
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'location' => 'sometimes|required|string|max:255',
        ]);

        $event->update($data);

        Cache::flush();

        return $this->respondUpdatedWithPayload(
            new EventResource($event->fresh('tickets')),
            'Event updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        $user = request()->user();

        if ($user->role === 'organizer' && $event->created_by !== $user->id) {
            return $this->respondForbidden('You can only delete your own events');
        }

        $event->delete();
        Cache::flush();

        return $this->respondDeleted('Event deleted successfully');
    }
}
