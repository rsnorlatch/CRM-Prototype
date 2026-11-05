<?php

use PHPUnit\Framework\TestCase;
use src\adapter\user_repository\MysqlUserRepository;
use src\entity\User;

class UserRepositoryTest extends TestCase
{
    private mysqli $db;
    private MysqlUserRepository $repository;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->db = new mysqli("localhost", "root", "", "crm_prototype");
        $this->db->begin_transaction();
        $this->repository = new MysqlUserRepository($this->db);
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();
        $this->db->close();
    }

    public function testWhenInsertingOneUser_ShouldNotBeEmpty()
    {
        $this->repository->insert(new User("username1", "password"));
        $this->repository->insert(new User("username2", "password"));

        $all = $this->repository->getAll();

        $this->assertNotEmpty($all);
    }

    public function testWhenInsertingOneUserAndGettingItsUsername_ShouldFetchTheUser()
    {
        $this->repository->insert(new User("username1", "password"));
        $this->repository->insert(new User("username2", "password"));

        $user = $this->repository->getByUsername("username1");

        $this->assertsame("username1", $user->username);
    }
}
