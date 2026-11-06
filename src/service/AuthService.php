<?php

namespace src\service;

use src\adapter\user_repository\UserRepositoryInterface;
use src\adapter\user_session\UserSessionInterface;
use src\entity\User;

class AuthService
{
    private UserRepositoryInterface $user;
    private UserSessionInterface $session;

    public function __construct(UserRepositoryInterface $user_repo, UserSessionInterface $user_session) {
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
