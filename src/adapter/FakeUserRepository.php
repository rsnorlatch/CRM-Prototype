<?php

namespace src\adapter;

use src\entity\User;

class FakeUserRepository {
    private array $users;    

    public function __construct()
    {
        $this->users = [];
    }

    public function getAll() {
        return $this->users;
    }

    public function insert(User $user) {
        array_push($this->users, $user);
    }
}
