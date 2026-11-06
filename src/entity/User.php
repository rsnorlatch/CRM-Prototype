<?php

namespace src\entity;

class User
{
    public int $id = 0;
    public string $username;
    public string $password;
    public UserRole $role;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->role = UserRole::Cashier;
    }

    public function withId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function withRole(UserRole $role)
    {
        $this->role = $role;
        return $this;
    }
}
