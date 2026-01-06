<?php

class Auth
{
    public static function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id'   => $user->id,
            'name' => $user->name,
            'role' => $user->role
        ];
    }

    public static function logout(): void
    {
        session_start();
        session_destroy();
        header("Location: index.php");
        exit;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }
}
