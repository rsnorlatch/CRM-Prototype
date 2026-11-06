<?php

use PHPUnit\Framework\TestCase;
use src\adapter\feedback_repository\MysqlFeedbackRepository;
use src\entity\Feedback;
use src\entity\FeedbackCategory;
use src\entity\FeedbackType;

class FeedbackRepositoryTest extends TestCase
{
    private mysqli $db;
    private MysqlFeedbackRepository $repository;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->db = new mysqli("localhost", "root", "", "crm_prototype");
        $this->db->begin_transaction();
        $this->repository = new MysqlFeedbackRepository($this->db);
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();
        $this->db->close();
    }

    public function testWhenInsertingAFeedback_ShouldNotHaveEmptyFeedbackList()
    {
        $this->repository->insert(new Feedback("feedback1", FeedbackType::Suggestion)->withCategory(FeedbackCategory::Other));

        $this->assertNotEmpty($this->repository->getAll());
    }

    public function testWhenInsertingAFeedbackAndThenGettingById_ShouldRetrieveTheCorrectFeedback()
    {
        $this->repository->insert(new Feedback("feedback1", FeedbackType::Suggestion)
            ->withCategory(FeedbackCategory::Other));

        $first_feedback_id = $this->repository->getAll()[0]->id;

        $feedback = $this->repository->getById($first_feedback_id);

        $this->assertSame("feedback1", $feedback->feedback);
    }

    public function testWhenInsertingAFeedbackAndThenUpdatingIt_ShouldReflectTheUpdatedChanges()
    {
        $this->repository->insert(new Feedback("feedback1", FeedbackType::Suggestion)
            ->withCategory(FeedbackCategory::Other));

        $first_feedback = $this->repository->getAll()[0];

        $first_feedback->feedback = "feedback2";

        $this->repository->update($first_feedback);

        $updated_feedback = $this->repository->getById($first_feedback->id);

        $this->assertSame("feedback2", $updated_feedback->feedback);
    }
}
