<?php

namespace src\service;

use src\adapter\feedback_repository\FeedbackRepositoryInterface;
use src\adapter\user_session\UserSessionInterface;
use src\entity\Feedback;
use src\entity\FeedbackStatus;

class FeedbackService {
    private FeedbackRepositoryInterface $feedback;
    private UserSessionInterface $session;

    public function __construct(FeedbackRepositoryInterface $feedback, UserSessionInterface $session)
    {
        $this->feedback = $feedback;
        $this->session = $session;
    }

    public function addFeedback(Feedback $feedback) {
        $this->feedback->insert($feedback);
    }

    public function markFeedbackAsResolved(int $id) {
        if ($this->session->getCurrentUser() == null) return;

        $selected_feedback = $this->feedback->getById($id);
        $selected_feedback->status = FeedbackStatus::Resolved;

        $this->feedback->update($selected_feedback);
    }

    public function setPIC(int $id, string $PIC) {
        if ($this->session->getCurrentUser() == null) return;

        $selected_feedback = $this->feedback->getById($id);
        $selected_feedback->person_in_charge = $PIC;

        $this->feedback->update($selected_feedback);
    }

    public function getFeedbackData() {
        if ($this->session->getCurrentUser() == null) return [];

        return $this->feedback->getAll();
    }
}
