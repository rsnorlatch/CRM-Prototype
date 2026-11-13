<?php

use PHPUnit\Framework\TestCase;

use src\service\PromotionBroadcastService;
use src\entity\Promotion;
use src\adapter\message_gateway\FakeMessageGateway;
use src\adapter\promotion_repository\FakePromotionRepository;
use src\adapter\user_session\FakeUserSession;
use src\entity\User;
use src\entity\UserRole;

class BroadcastingTest extends TestCase
{
    private FakeMessageGateway $message_gateway;
    private FakePromotionRepository $promotion_repo;
    private FakeUserSession $session;

    private PromotionBroadcastService $_service;

    #[Override]
    public function setUp(): void
    {
        $this->message_gateway = new FakeMessageGateway();
        $this->promotion_repo = new FakePromotionRepository();
        $this->session = new FakeUserSession();
        $this->_service = new PromotionBroadcastService($this->message_gateway, $this->promotion_repo, $this->session);

        $this->session->setCurrentUser(new User("achilles", "Password123")->withRole(UserRole::Admin));
    }

    public function testWhenNoPromotionIsSetup_ShouldNotHaveSendableMessage()
    {
        $this->assertSame(0, $this->message_gateway->count_message());
    }

    public function testWhenAPromotionIsSetup_ShouldHaveSendableMessage()
    {
        $this->_service->addPromotion(
            new Promotion(1, "diskon 50%", new DateTime(), new DateTime()->add(DateInterval::createFromDateString("7 days")))
        );

        $this->assertSame(1, $this->message_gateway->count_message());
    }

    public function testWhenUserIsNotLoggedIn_ShouldNotBeAbleToCreatePromotion()
    {
        $this->session->logout();

        $this->_service->addPromotion(
            new Promotion(1, "diskon 50%", new DateTime(), new DateTime()->add(DateInterval::createFromDateString("7 days")))
        );

        $this->assertSame(0, $this->message_gateway->count_message());
    }

    public function testWhenUserIsNotAdmin_ShoudlNotBeAbleToCreatePromotion()
    {
        $this->session->logout();
        $this->session->setCurrentUser(new User("tortoise", "Password123")->withRole(UserRole::Cashier));

        $this->_service->addPromotion(
            new Promotion(1, "diskon 50%", new DateTime(), new DateTime()->add(DateInterval::createFromDateString("7 days")))
        );

        $this->assertSame(0, $this->message_gateway->count_message());
    }

    public function testWhenYouHaveASendableMessage_SendingItShouldSendTheMessageToTheUser()
    {
        $this->_service->addPromotion(
            new Promotion(1, "diskon 50%", new DateTime(), new DateTime()->add(DateInterval::createFromDateString("7 days")))
        );

        $this->_service->sendMessage();

        $this->assertSame(0, $this->message_gateway->count_message());
    }
}
