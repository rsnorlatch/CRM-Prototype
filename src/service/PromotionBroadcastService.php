<?php

namespace src\service;

use src\adapter\message_gateway\FakeMessageGateway;
use src\adapter\promotion_repository\FakePromotionRepository;
use src\entity\BroadcastMessage;
use src\entity\Promotion;
use src\adapter\user_session\FakeUserSession;
use src\entity\UserRole;

class PromotionBroadcastService
{
    private FakePromotionRepository $promotion_repo;
    private FakeMessageGateway $message_gateway;
    private FakeUserSession $session;

    public function __construct(FakeMessageGateway $gateway, FakePromotionRepository $promotion_repo, FakeUserSession $session)
    {
        $this->promotion_repo = $promotion_repo;
        $this->message_gateway = $gateway;
        $this->session = $session;
    }

    public function addPromotion(Promotion $promotion)
    {
        if ($this->session->getCurrentUser() == null) return;

        if ($this->session->getCurrentUser()->role != UserRole::Admin) return;

        $this->promotion_repo->insert($promotion);

        $this->message_gateway->enqueue_message(new BroadcastMessage($this->message_gateway->count_message() + 1, "diskon 50%"));
    }

    public function sendMessage()
    {
        $this->message_gateway->dequeue_message();
    }
}
