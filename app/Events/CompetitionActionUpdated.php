<?php
// app/Events/CompetitionActionUpdated.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CompetitionActionUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('competition-' . $this->data['results']['competitionId']);
    }

    public function broadcastAs(): string
    {
        return 'action-updated';
    }
}
