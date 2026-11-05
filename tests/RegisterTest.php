<?php

require_once __DIR__ . "/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use src\adapter\user_repository\FakeUserRepository;
use src\adapter\user_session\FakeUserSession;
use src\service\AuthService;

final class RegisterTest extends TestCase
{
    private FakeUserRepository $user_repo;
    private FakeUserSession $session;

    private AuthService $service;

    protected function setUp(): void {
        $this->user_repo = new FakeUserRepository();
        $this->session = new FakeUserSession();

        $this->service = new AuthService($this->user_repo, $this->session);
    }

    public function testWhenProvidingEmptyString_ShouldNotCreateAccount()
    {
        $this->service->register("", "", "");

        $this->assertEmpty($this->user_repo->getAll());
    }

    public function testWhenProvidingData_ShouldCreateAccount()
    {
        $this->service->register("ftom", "phigros", "phigros");

        $this->assertNotEmpty($this->user_repo->getAll());
    }

    public function testWhenPasswordDoNotMatch_ShouldNotCreateAccount()
    {
        $this->service->register("ftom", "phigros", "lanota");

        $this->assertEmpty($this->user_repo->getAll());
    }
}
