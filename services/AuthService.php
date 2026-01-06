<?php
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../entities/User.php';

class AuthService
{
    private UserRepository $userRepo;

    public function __construct(PDO $pdo)
    {
        $this->userRepo = new UserRepository($pdo);
    }

    // LOGIN
    public function login(string $email, string $password): ?User
    {
        // Use getByEmail() from UserRepository
        $data = $this->userRepo->getByEmail($email);

        if (!$data || !password_verify($password, $data['password'])) {
            return null;
        }

        return new User(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['role']
        );
    }

    // SIGNUP
    public function signup(string $name, string $email, string $password, string $role): array
    {
        $errors = [];

        if (!$name || !$email || !$password || !$role) {
            $errors[] = "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (!in_array($role, ['admin','project_manager','member'])) {
            $errors[] = "Invalid role selected.";
        }

        // Check if user exists
        if ($this->userRepo->getByEmail($email)) {
            $errors[] = "Email already exists.";
        }

        if ($errors) return ['errors' => $errors];

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $hashed,
            'role' => $role
        ];

        $user = $this->userRepo->create($userData);

        return ['user' => $user];
    }
}
