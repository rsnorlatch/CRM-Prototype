<?php
namespace src\entity;

enum FeedbackType: string {
    case Complaint = "Complaint";
    case Suggestion = "Suggestion";
}