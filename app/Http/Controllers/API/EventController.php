<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
            if($request->has('location')) $q->where('location', $request->get('location'));
            return $q->orderBy('date','asc')->paginate(10);
        });
        return $this->respondWithMessageAndPayload($events, 'Events retrieved successfully');
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
