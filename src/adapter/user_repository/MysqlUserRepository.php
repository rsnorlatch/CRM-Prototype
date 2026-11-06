<?php

namespace src\adapter\user_repository;

use Override;
use mysqli;
use src\entity\User;
use src\entity\UserRole;

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
        $stmt = $this->_db->prepare("SELECT * FROM user");
        $stmt->execute();

        $queried = $stmt->get_result();

        if ($queried == null) {
            return [];
        }

        $mapped_users = [];

        while ($row = $queried->fetch_assoc()) {
            $current_user = new User($row["username"], $row["password"])
                ->withRole(UserRole::fromString($row["role"]))
                ->withId($row["id"]);

            array_push($mapped_users, $current_user);
        }

        return $mapped_users;
    }

    #[Override]
    public function getByUsername(string $username): ?User
    {
        $stmt = $this->_db->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return new User($row["username"], $row["password"])
            ->withId($row["id"])
            ->withRole(UserRole::fromString($row["role"]));
    }

    #[Override]
    public function insert(User $user)
    {
        $stmt = $this->_db->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");

        $role = $user->role->toString();
        $stmt->bind_param("sss", $user->username, $user->password, $role);

        $stmt->execute();
    }
}
