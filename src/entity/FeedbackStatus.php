<?php

namespace src\entity;

enum FeedbackStatus: string {
    case Pending = "Pending";
    case Resolved = "Resolved";
}