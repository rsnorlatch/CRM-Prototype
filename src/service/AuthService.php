<?php

namespace src\service;

use src\adapter\FakeUserRepository;
use src\entity\User;

class AuthService
{
    private FakeUserRepository $user;
    public ?User $current_user;

    public function __construct(FakeUserRepository $user_repo) {
        $this->user = $user_repo;
        $this->current_user = null;
    }

    public function register(string $username, string $password, string $confirm_password)
    {
        if ($username == "" || $password == "" || $confirm_password == "") return;

        if ($password != $confirm_password) return;

        $this->user->insert(new User($username, $password));
    }

    public function login(string $username, string $password) {
        if ($username == "" || $password == "") return;

        $selected_user = $this->user->getByUsername($username);

        if ($selected_user->password != $password) return;

        $this->current_user = new User($username, $password);
    }
}
