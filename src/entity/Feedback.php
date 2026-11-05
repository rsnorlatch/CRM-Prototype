<?php

namespace src\entity;

use DateTime;

class Feedback {
    public int $id = 0;
    public string $feedback;
    public ?string $phone_number;
    public FeedbackType $type;
    public FeedbackStatus $status = FeedbackStatus::Pending;
    public DateTime $created_at;

    public function __construct(string $feedback, FeedbackType $type)
    {
        $this->feedback = $feedback;
        $this->type = $type;
        $this->created_at = new DateTime();
    }

    public function withId(int $id) {
        $this->id = $id;
        return $this;
    }

    public function withStatus(FeedbackStatus $status) {
        $this->status = $status;

        return $this;
    }
}