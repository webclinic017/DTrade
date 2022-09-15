<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActionFailed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private $user;

    public $action;

    public $message;

    /**
     * Create a new event instance.
     *
     * @param User   $user    - The user who initiated the action
     * @param string $action  - The string name of the action which has failed
     * @param string $message - The reason that the action failed
     */
    public function __construct(User $user, string $action, string $message)
    {
        $this->user = $user;
        $this->action = $action;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('actions.'.$this->user->id);
    }
}
