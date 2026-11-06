<?php

namespace src\entity;

enum FeedbackStatus: string
{
    case Pending = "Pending";
    case Processed = "Processed";
    case Resolved = "Resolved";

    public function toString()
    {
        return match ($this) {
            FeedbackStatus::Pending => "Pending",
            FeedbackStatus::Processed => "Processed",
            FeedbackStatus::Resolved => "Resolved",
            default => null,
        };
    }

    public static function fromString(string $from)
    {
        return match ($from) {
            "Pending" => FeedbackStatus::Pending,
            "Processed" => FeedbackStatus::Processed,
            "Resolved" => FeedbackStatus::Resolved,
            default => null,
        };
    }
}
