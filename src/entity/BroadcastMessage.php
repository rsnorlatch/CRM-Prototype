<?php

namespace src\entity;

use DateTime;

class BroadcastMessage
{
    public int $id;
    public string $message;
    public DateTime $created_at;
    public ?DateTime $sent_date;

    public function __construct(int $id, string $message)
    {
        $this->id = $id;
        $this->message = $message;
        $this->created_at = new DateTime();
        $this->sent_date = new DateTime();
    }

    public function withCreattedAt(DateTime $with): BroadcastMessage
    {
        $this->created_at = $with;

        return $this;
    }

    public function withSentDate(DateTime $with): BroadcastMessage
    {
        $this->sent_date = $with;

        return $this;
    }
}
