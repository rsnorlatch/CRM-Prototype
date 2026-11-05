<?php

use PHPUnit\Framework\TestCase;
use src\adapter\FakeUserRepository;
use src\service\AuthService;

class LoginTest extends TestCase {
    public function testWhenNotProvidingAnyCredential_ShouldNotLogin() {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->login("", "");

        $this->assertNull($service->current_user);
    }

    public function testWhenProvidingCorrectCredential_ShouldLogin() {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->register("ftom", "phigros", "phigros");
        $service->login("ftom", "phigros");

        $this->assertNotNull($service->current_user);
    }

    public function testWhenProvidingIncorrectCredential_ShouldNotLogin() {
        $repo = new FakeUserRepository();
        $service = new AuthService($repo);

        $service->register("ftom", "phigros", "phigros");
        $service->login("ftom", "phira");

        $this->assertNull($service->current_user);
    }
}