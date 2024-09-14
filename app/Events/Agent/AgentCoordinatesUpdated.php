<?php

namespace App\Events\Agent;

use App\Models\Coordinate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentCoordinatesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $coordinate;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Coordinate $coordinate)
    {
        $this->coordinate = $coordinate;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('agent-coordinates');
    }

    public function broadcastAs()
    {
        return 'coordinate-event';
    }
}
