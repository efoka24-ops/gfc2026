<?php

namespace App\Events;

use App\Models\GameMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MatchMinuteUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public readonly GameMatch $match) {}

    public function broadcastOn(): Channel
    {
        return new Channel('match.' . $this->match->id);
    }

    public function broadcastAs(): string { return 'minute.updated'; }

    public function broadcastWith(): array
    {
        return ['match_id' => $this->match->id, 'minute' => $this->match->minute];
    }
}
