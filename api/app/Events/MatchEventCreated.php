<?php

namespace App\Events;

use App\Models\GameMatch;
use App\Models\MatchEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MatchEventCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public readonly GameMatch  $match,
        public readonly MatchEvent $event
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('match.' . $this->match->id);
    }

    public function broadcastAs(): string { return 'event.created'; }

    public function broadcastWith(): array
    {
        return [
            'match_id'   => $this->match->id,
            'home_score' => $this->match->home_score,
            'away_score' => $this->match->away_score,
            'event'      => [
                'id'          => $this->event->id,
                'type'        => $this->event->type,
                'minute'      => $this->event->minute,
                'player_id'   => $this->event->player_id,
                'team_id'     => $this->event->team_id,
                'description' => $this->event->description,
            ],
        ];
    }
}
