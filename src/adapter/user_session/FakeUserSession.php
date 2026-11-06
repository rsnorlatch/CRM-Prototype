<?php
namespace src\adapter\user_session;

use src\adapter\user_session\UserSessionInterface;
use src\entity\User;

class FakeUserSession implements UserSessionInterface {
    private ?User $current_user;

    public function __construct()
    {
        $this->current_user = null;
    }

    public function getCurrentUser() {
        return $this->current_user;
    }

    public function setCurrentUser(User $current_user) {
        $this->current_user = $current_user;
    }

    public function logout() {
        $this->current_user = null;
    }
}