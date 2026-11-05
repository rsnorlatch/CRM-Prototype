<?php

namespace src\service;

use src\adapter\user_repository\FakeUserRepository;
use src\adapter\user_session\FakeUserSession;
use src\entity\User;

class AuthService
{
    private FakeUserRepository $user;
    private FakeUserSession $session;

    public function __construct(FakeUserRepository $user_repo, FakeUserSession $user_session) {
        $this->user = $user_repo;
        $this->session = $user_session;
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

        $this->session->setCurrentUser($selected_user);
    }
}
