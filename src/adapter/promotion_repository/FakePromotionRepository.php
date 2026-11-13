<?php

namespace src\adapter\promotion_repository;

use src\entity\Promotion;

class FakePromotionRepository
{
    private array $promotion = [];

    public function insert(Promotion $promotion)
    {
        array_push($this->promotion, $promotion);
    }
}
