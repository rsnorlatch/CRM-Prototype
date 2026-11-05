<?php

namespace src\adapter\user_session;

use src\entity\User;

interface UserSessionInterface
{
    public function getCurrentUser();

    public function setCurrentUser(User $current_user);

    public function logout();
}

