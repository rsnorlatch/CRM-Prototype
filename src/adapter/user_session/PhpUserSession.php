<?php

use src\adapter\user_repository\UserRepositoryInterface;
use src\adapter\user_session\UserSessionInterface;
use src\entity\User;

class PhpUserSession implements UserSessionInterface
{
    private UserRepositoryInterface $user;

    public function __construct(UserRepositoryInterface $user)
    {
        session_start();
        $this->user = $user;
    }

    #[Override]
    public function getCurrentUser()
    {
        $username = $_SESSION["username"];
        $userid = $_SESSION["user_id"];

        if ($username == null || $userid == null) return null;

        $current_user = $this->user->getByUsername($username);

        return $current_user;
    }

    #[Override]
    public function setCurrentUser(User $current_user)
    {
        $_SESSION["username"] = $current_user->username;
        $_SESSION["user_id"] = $current_user->id;
    }

    #[Override]
    public function logout()
    {
        session_destroy();
    }
}
