<?php

use PHPUnit\Framework\TestCase;
use src\adapter\feedback_repository\FakeFeedbackRepository;
use src\adapter\user_session\FakeUserSession;
use src\entity\Feedback;
use src\entity\FeedbackStatus;
use src\entity\FeedbackType;
use src\entity\User;
use src\service\FeedbackService;

class FeedbackServiceTest extends TestCase
{
    private FakeFeedbackRepository $feedback;
    private FakeUserSession $session;
    private FeedbackService $service;

    public function setUp(): void {
        $this->feedback = new FakeFeedbackRepository();
        $this->session = new FakeUserSession();
        $this->service = new FeedbackService($this->feedback, $this->session);

        $this->session->setCurrentUser(new User("user1", "password123"));
    }

    public function testWhenNoFeedbackIsProvided_ShouldHaveEmptyFeedback() {
        $this->assertEmpty($this->feedback->getAll());
    }

    public function testWhenFeedbackIsProvided_ShouldNotHaveEmptyFeedback() {
        $this->service->addFeedback(new Feedback("feedback", FeedbackType::Complaint));

        $this->assertNotEmpty($this->feedback->getAll());
    }

    public function testWhenMarkingAFeedbackAsResolved_ThatFeedbackShouldHaveResolvedType() {
        $this->service->addFeedback(new Feedback("feedback1", FeedbackType::Complaint)->withid(1));

        $this->service->markFeedbackAsResolved(1);

        $current_feedback = $this->feedback->getById(1);

        $this->assertSame(FeedbackStatus::Resolved, $current_feedback->status);
    }

    public function testWhenUserIsNotLoggedIn_ShouldNotBeAbleToMarkAFeedbackAsResolved() {
        $this->service->addFeedback(new Feedback("feedback1", FeedbackType::Complaint)->withId(1));
        $this->session->logout();

        $this->service->markFeedbackAsResolved(1);

        $current_feedback = $this->feedback->getById(1);

        $this->assertSame(FeedbackStatus::Pending, $current_feedback->status);
    }

    public function testWhenUserIsNotLoggedInAndTryToSetPIC_ShouldNotSetPIC() {
        $this->service->addFeedback(new Feedback("feedback1", FeedbackType::Complaint)->withId(1));

        $this->session->logout();

        $this->service->setPIC(1, "some franchise");

        $current_feedback = $this->feedback->getById(1);

        $this->assertSame(null, $current_feedback->person_in_charge);
    }

    public function testWhenUserIsLoggedInAndTryToSetPIC_SHoudlSetPIC() {
        $this->service->addFeedback(new Feedback("feedback1", FeedbackType::Complaint)->withId(1));

        $this->service->setPIC(1, "idk someone");

        $current_feedback = $this->feedback->getById(1);

        $this->assertSame("idk someone", $current_feedback->person_in_charge);
    }

    public function testWhenUserIsNotLoggedInAndTryToViewFeedback_ShouldNotProvideFeedback() {
        $this->service->addFeedback(new Feedback("feedback1", FeedbackType::Complaint));

        $this->session->logout();

        $feedbacks = $this->service->getFeedbackData();

        $this->assertSame([], $feedbacks);
    }

    public function testWhenUserIsLoggedInAndTryToViewFeedback_ShouldProvideFeedback() {
        $feedback = new Feedback("feedback1", FeedbackType::Complaint);
        $this->service->addFeedback($feedback);

        $feedbacks = $this->service->getFeedbackData();

        $this->assertSame([$feedback], $feedbacks);
    }
}
