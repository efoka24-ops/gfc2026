<?php

namespace App\Events;

use App\Models\GameMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public readonly GameMatch $match) {}

    public function broadcastOn(): Channel
    {
        return new Channel('match.' . $this->match->id);
    }

    public function broadcastAs(): string { return 'score.updated'; }

    public function broadcastWith(): array
    {
        return [
            'match_id'   => $this->match->id,
            'home_score' => $this->match->home_score,
            'away_score' => $this->match->away_score,
            'minute'     => $this->match->minute,
        ];
    }
}
