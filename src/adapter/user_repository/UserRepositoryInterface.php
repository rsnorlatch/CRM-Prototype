<?php

namespace src\adapter\user_repository;

use src\entity\User;

interface UserRepositoryInterface {
    public function getAll();

    public function insert(User $user);

    public function getByUsername(string $username);
}