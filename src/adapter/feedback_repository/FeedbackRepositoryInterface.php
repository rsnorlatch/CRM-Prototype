<?php

namespace src\adapter\feedback_repository;

use src\entity\Feedback;

interface FeedbackRepositoryInterface {
    public function insert(Feedback $feedback);

    public function getAll();

    public function getById(int $id): ?Feedback;

    public function update(Feedback $newFeedback);
}