<?php
require_once __DIR__ . '/BaseRepository.php';

class UserRepository extends BaseRepository
{
    public function __construct(PDO $pdo)
    {
        $this->table = 'users';
        parent::__construct($pdo);
    }

    public function getByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (name, email, password, role, active)
             VALUES (:name, :email, :password, :role, :active)"
        );
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':role' => $data['role'],
            ':active' => $data['active'] ?? 1
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET name = :name, email = :email, role = :role, active = :active WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':role' => $data['role'],
            ':active' => $data['active'] ?? 1
        ]);
    }

    // COUNT METHODS - Add these
    public function countAll(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
        return (int)$stmt->fetchColumn();
    }

    // GET RECENT - Add this
    public function getRecent(int $limit = 5): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all users (for dropdowns)
    public function getAllUsers(): array {
        $stmt = $this->pdo->query("SELECT id, name, email, role FROM {$this->table} ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}