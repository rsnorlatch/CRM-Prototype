<?php

namespace src\entity;

enum FeedbackType: string
{
    case Complaint = "Complaint";
    case Suggestion = "Suggestion";

    public function toString()
    {
        return match ($this) {
            FeedbackType::Complaint => "Complaint",
            FeedbackType::Suggestion => "Suggestion",
            default => null,
        };
    }

    public static function fromString(string $from)
    {
        return match ($from) {
            "Complaint" => FeedbackType::Complaint,
            "Suggestion" => FeedbackType::Suggestion,
            default => null
        };
    }
}

