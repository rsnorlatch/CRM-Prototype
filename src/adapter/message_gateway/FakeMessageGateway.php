<?php

namespace src\adapter\message_gateway;

use src\entity\BroadcastMessage;

class FakeMessageGateway
{
    private array $message_queue = [];

    public function count_message()
    {
        return count($this->message_queue);
    }

    public function enqueue_message(BroadcastMessage $message)
    {
        array_unshift($this->message_queue, $message);
    }

    public function dequeue_message(): BroadcastMessage
    {
        return array_shift($this->message_queue);
    }
}
