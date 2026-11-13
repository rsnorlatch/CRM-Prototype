<?php

namespace src\entity;

use DateTime;

class Promotion
{
    public int $id;
    public string $name;
    public DateTime $start_date;
    public DateTime $end_date;

    public function __construct(int $id, string $name, DateTime $start_date, DateTime $end_date)
    {
        $this->id = $id;
        $this->name = $name;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
}
