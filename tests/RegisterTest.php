<?php

require_once __DIR__ . "/../vendor/autoload.php";

use PHPUnit\Framework\TestCase;
use src\adapter\FakeUserRepository;
use src\service\AuthService;

final class RegisterTest extends TestCase
{
    public function testWhenProvidingEmptyString_ShouldNotCreateAccount()
    {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->register("", "", "");

        $this->assertEmpty($repo->getAll());
    }

    public function testWhenProvidingData_ShouldCreateAccount()
    {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->register("ftom", "phigros", "phigros");

        $this->assertNotEmpty($repo->getAll());
    }

    public function testWhenPasswordDoNotMatch_ShouldNotCreateAccount()
    {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->register("ftom", "phigros", "lanota");

        $this->assertEmpty($repo->getAll());
    }
}
