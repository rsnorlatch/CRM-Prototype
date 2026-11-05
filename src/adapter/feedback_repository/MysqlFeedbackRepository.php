<?php

namespace src\adapter\type\adapter\feedback_repository;

use mysqli;
use Override;
use src\adapter\feedback_repository\FeedbackRepositoryInterface;
use src\entity\Feedback;

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
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function getAll()
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function getById(int $id): ?Feedback
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function update(Feedback $newFeedback)
    {
        throw new \Exception('Not implemented');
    }
}
