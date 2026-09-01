<?php

namespace App\Events;

use App\Models\GameMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MatchStatusChanged implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public readonly GameMatch $match) {}

    public function broadcastOn(): Channel
    {
        return new Channel('match.' . $this->match->id);
    }

    public function broadcastAs(): string { return 'status.changed'; }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->match->id,
            'status'     => $this->match->status,
            'minute'     => $this->match->minute,
            'home_score' => $this->match->home_score,
            'away_score' => $this->match->away_score,
        ];
    }
}
