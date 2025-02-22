<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class AttendeeController extends Controller
{
    use CanLoadRelationships, AuthorizesRequests;

    public static function middleware():array {
        return [
            new Middleware("auth:sanctum", except: ["index", "show", "update"]),
            new Middleware("throttle:60,1", only: ["store", "destroy"]),
        ];
    }

    private array $relations = ["user"];
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $query = $this->loadRelationships($event->attendees()->latest());
        return AttendeeResource::collection(
            $query->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create([
            "user_id" => $request->user()->id
        ]);

        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
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
    public function destroy(Event $event, Attendee $attendee)
    {
        $this->authorize("delete-attendee", [$event, $attendee]);
        $attendee->delete();
        return response(status: 204);
    }
}
