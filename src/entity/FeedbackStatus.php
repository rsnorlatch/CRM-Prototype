<?php

namespace src\entity;

enum FeedbackStatus: string
{
    case Pending = "Pending";
    case Resolved = "Resolved";

    public function toString()
    {
        return match ($this) {
            FeedbackStatus::Pending => "Pending",
            FeedbackStatus::Resolved => "Resolved",
            default => null,
        };
    }

    public static function fromString(string $from)
    {
        return match ($from) {
            "Pending" => FeedbackStatus::Pending,
            "Resolved" => FeedbackStatus::Resolved,
            default => null,
        };
    }
}

