<?php

namespace src\entity;

enum UserRole: string
{
    case Cashier = "Cashier";
    case Admin = "Admin";

    public function toString()
    {
        return match ($this) {
            UserRole::Cashier => "Cashier",
            UserRole::Admin => "Admin",
        };
    }

    public static function fromString(string $from)
    {
        return match ($from) {
            "Cashier" => UserRole::Cashier,
            "Admin" => UserRole::Admin,
        };
    }
}
