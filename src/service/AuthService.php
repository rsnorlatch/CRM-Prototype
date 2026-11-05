<?php

namespace src\service;

use src\adapter\FakeUserRepository;
use src\entity\User;

class AuthService
{
    private FakeUserRepository $user;

    public function __construct(FakeUserRepository $user_repo) {
        $this->user = $user_repo;
    }

    public function register(string $username, string $password, string $confirm_password)
    {
        if ($username == "" || $password == "" || $confirm_password == "") return;

        if ($password != $confirm_password) return;

        $this->user->insert(new User($username, $password));
    }
}
