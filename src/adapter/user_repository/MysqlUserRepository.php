<?php

namespace src\adapter\user_repository;

use Override;
use mysqli;
use src\entity\User;

class MysqlUserRepository implements UserRepositoryInterface
{
    private mysqli $_db;

    public function __construct(mysqli $db)
    {
        $this->_db = $db;
    }

    #[Override]
    public function getAll()
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function getByUsername(string $username)
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function insert(User $user)
    {
        throw new \Exception('Not implemented');
    }
}
