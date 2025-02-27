<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller implements HasMiddleware
{
    use CanLoadRelationships, AuthorizesRequests;
    private array $relations = ["user", "attendees", "attendees.user"];

    public static function middleware():array {
        return [
            new Middleware("auth:sanctum", except: ["index", "show"]),
            new Middleware('can:delete,event', only: ['destroy']),
            new Middleware('can:update,event', only: ['udpate']),
            new Middleware("throttle:60,1", only: ["store", "update", "destroy"]),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = $this->loadRelationships(Event::query(), $this->relations);
        return EventResource::collection($query->latest()->paginate());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $event = Event::create([
            ...$request->validate([
                "name" => "required|string|max:255",
                "description" => "nullable|string",
                "start_time" => "required|date",
                "end_time" => "required|date|after:start_time"
            ]),
            "user_id" => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {

        $this->authorize("update-event", $event);

        $event->update($request->validate([
            "name" => "sometimes|string|max:255",
            "description" => "nullable|string",
            "start_time" => "sometimes|date",
            "end_time" => "sometimes|date|after:start_time"
        ]));

        return new EventResource($this->loadRelationships($event));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response(status: 204);
    }
}
