<?php

namespace src\entity;

enum FeedbackCategory: string
{
    case Service = "Service";
    case MenuTaste = "MenuTaste";
    case Cleanliness = "Cleanliness";
    case WaitingTime = "WaitingTime";
    case Other = "Other";
}
