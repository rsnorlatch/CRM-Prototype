<?php

namespace src\entity;

use DateTime;
use src\entity\FeedbackCategory;

class Feedback
{
    public int $id = 0;
    public string $feedback;
    public ?string $phone_number = null;
    public FeedbackType $type;
    public ?string $person_in_charge = null;
    public FeedbackStatus $status = FeedbackStatus::Pending;
    public ?FeedbackCategory $category = null;
    public DateTime $created_at;

    public function __construct(string $feedback, FeedbackType $type)
    {
        $this->feedback = $feedback;
        $this->type = $type;
        $this->created_at = new DateTime();
    }

    public function withId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function withPhoneNumber(string $phone_number)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function withStatus(FeedbackStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    public function withPersonInCharge(string $person_in_charge)
    {
        $this->person_in_charge = $person_in_charge;
        return $this;
    }

    public function withCategory(FeedbackCategory $category)
    {
        $this->category = $category;

        return $this;
    }
}

