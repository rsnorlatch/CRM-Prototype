<?php

namespace src\adapter\feedback_repository;

use DateTime;
use mysqli;
use Override;
use src\adapter\feedback_repository\FeedbackRepositoryInterface;
use src\entity\Feedback;
use src\entity\FeedbackCategory;
use src\entity\FeedbackStatus;
use src\entity\FeedbackType;

class MysqlFeedbackRepository implements FeedbackRepositoryInterface
{
    private mysqli $_db;

    public function __construct(mysqli $db)
    {
        $this->_db = $db;
    }

    #[Override]
    public function insert(Feedback $feedback)
    {
        $stmt = $this->_db->prepare("
            INSERT INTO feedback (feedback, phone_number, person_in_charge, type, status, category) 
            VALUES (?, ?, ?, ?, ?, ?)");

        $type = $feedback->type?->toString();
        $status = $feedback->status?->toString();
        $category = $feedback->category?->toString();

        $stmt->bind_param(
            "ssssss",
            $feedback->feedback,
            $feedback->phone_number,
            $feedback->person_in_charge,
            $type,
            $status,
            $category,
        );

        $stmt->execute();
    }

    #[Override]
    public function getAll()
    {
        $stmt = $this->_db->prepare("SELECT * FROM feedback ORDER BY id DESC");
        $stmt->execute();

        $result = $stmt->get_result();

        $all_mapped_feedback = [];

        while ($row = $result->fetch_assoc()) {
            $mapped_feedback = new Feedback($row["feedback"], FeedbackType::fromString($row["type"]))
                ->withId($row["id"])
                ->withPersonInCharge($row["person_in_charge"] ?? "")
                ->withPhoneNumber($row["phone_number"] ?? "")
                ->withStatus($row["status"] !== null ? FeedbackStatus::fromString($row["status"]) : null)
                ->withCategory($row["category"] !== null ? FeedbackCategory::fromString($row["category"]) : null);

            array_push($all_mapped_feedback, $mapped_feedback);
        }

        return $all_mapped_feedback;
    }

    #[Override]
    public function getById(int $id): ?Feedback
    {
        $stmt = $this->_db->prepare("SELECT * FROM feedback WHERE id = ?");
        $stmt->bind_param("i", $id);

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        if ($row === null) {
            return null;
        }

        $mapped_feedback = new Feedback($row["feedback"], FeedbackType::fromString($row["type"]))
            ->withId($row["id"])
            ->withPersonInCharge($row["person_in_charge"] ?? "")
            ->withPhoneNumber($row["phone_number"] ?? "")
            ->withStatus($row["status"] !== null ? FeedbackStatus::fromString($row["status"]) : null)
            ->withCategory($row["category"] !== null ? FeedbackCategory::fromString($row["category"]) : null);

        return $mapped_feedback;
    }

    #[Override]
    public function update(Feedback $newFeedback)
    {
        $stmt = $this->_db->prepare("
            UPDATE feedback
            SET feedback = ?, phone_number = ?, person_in_charge = ?, type = ?, status = ?, category = ?
            WHERE id = ?");

        $type = $newFeedback->type?->toString();
        $status = $newFeedback->status?->toString();
        $category = $newFeedback->category?->toString();

        $stmt->bind_param(
            "ssssssi",
            $newFeedback->feedback,
            $newFeedback->phone_number,
            $newFeedback->person_in_charge,
            $type,
            $status,
            $category,
            $newFeedback->id,
        );

        $stmt->execute();
    }
}
