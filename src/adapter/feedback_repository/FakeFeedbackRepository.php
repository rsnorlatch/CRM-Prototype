<?php

namespace src\adapter\feedback_repository;

use src\entity\Feedback;

class FakeFeedbackRepository {
    private array $feedbacks;

    public function __construct()
    {
        $this->feedbacks = [];
    }

    public function insert(Feedback $feedback) {
        array_push(
            $this->feedbacks,
            $feedback->withId(count($this->feedbacks) + 1)
        );
    }

    public function getAll() {
        return $this->feedbacks;
    }

    public function getById(int $id): ?Feedback {
        $selected_feedback = array_filter(
            $this->feedbacks,
            function (Feedback $feedback) use ($id) {
                return $feedback->id == $id;
            }
        )[0];

        if ($selected_feedback == null) return null;

        return $selected_feedback;
    }

    public function update(Feedback $newFeedback) {
        $this->feedbacks[$newFeedback->id - 1] = $newFeedback;
    }
}