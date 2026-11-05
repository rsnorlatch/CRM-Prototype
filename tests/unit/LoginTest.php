<?php

use PHPUnit\Framework\TestCase;
use src\adapter\user_repository\FakeUserRepository;
use src\adapter\user_session\FakeUserSession;
use src\service\AuthService;

class LoginTest extends TestCase {
    private FakeUserRepository $user_repo;
    private FakeUserSession $session;

    private AuthService $service;

    protected function setUp(): void {
        $this->user_repo = new FakeUserRepository();
        $this->session = new FakeUserSession();

        $this->service = new AuthService($this->user_repo, $this->session);
    }
    
    public function testWhenNotProvidingAnyCredential_ShouldNotLogin() {
        $this->service->login("", "");

        $this->assertNull($this->session->getCurrentUser());
    }

    public function testWhenProvidingCorrectCredential_ShouldLogin() {
        $this->service->register("ftom", "phigros", "phigros");
        $this->service->login("ftom", "phigros");

        $this->assertNotNull($this->session->getCurrentUser());
    }

    public function testWhenProvidingIncorrectCredential_ShouldNotLogin() {
        $this->service->register("ftom", "phigros", "phigros");
        $this->service->login("ftom", "phira");

        $this->assertNull($this->session->getCurrentUser());
    }
}