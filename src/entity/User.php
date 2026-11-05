<?php

namespace src\entity;

class User
{
    public int $id = 0;
    public string $username;
    public string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function withId(int $id)
    {
        $this->id = $id;

        return $this;
    }
}
