<?php

namespace src\entity;

enum FeedbackCategory: string
{
    case Service = "Service";
    case MenuTaste = "MenuTaste";
    case Cleanliness = "Cleanliness";
    case WaitingTime = "WaitingTime";
    case Other = "Other";

    public function toString()
    {
        return match ($this) {
            FeedbackCategory::Service => "Service",
            FeedbackCategory::MenuTaste => "MenuTaste",
            FeedbackCategory::Cleanliness => "Cleanliness",
            FeedbackCategory::WaitingTime => "WaitingTime",
            FeedbackCategory::Other => "Other",
            default => null,
        };
    }

    public static function fromString(string $from)
    {
        return match ($from) {
            "Service" => FeedbackCategory::Service,
            "MenuTaste" => FeedbackCategory::MenuTaste,
            "Cleanliness" => FeedbackCategory::Cleanliness,
            "WaitingTime" => FeedbackCategory::WaitingTime,
            "Other " => FeedbackCategory::Other,
            default => null,
        };
    }
}
