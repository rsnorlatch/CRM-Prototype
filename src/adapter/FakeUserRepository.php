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

    public function getByUsername(string $username) {
        $all_user = $this->getAll();
        
        /** @var User $selected_user */
        $selected_user = array_filter($all_user, function (User $user) use ($username) {
            return $user->username == $username;
        })[0];

        return $selected_user;
    }
}
