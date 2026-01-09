<?php
require_once __DIR__ . '/../entities/Project.php';

class ProjectRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Get all projects
    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM projects");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $projects = [];
        foreach ($rows as $row) {
            $projects[] = new Project(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['created_by'],
                $row['status'],
                $row['created_at']
            );
        }
        return $projects;
    }

    // Get projects by manager (created_by)
    public function getByManager(int $managerId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE created_by = ?");
        $stmt->execute([$managerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $projects = [];
        foreach ($rows as $row) {
            $projects[] = new Project(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['created_by'],
                $row['status'],
                $row['created_at']
            );
        }
        return $projects;
    }

    // Get single project by ID
    public function getById(int $id): ?Project {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new Project(
            $row['id'],
            $row['title'],
            $row['description'],
            $row['created_by'],
            $row['status'],
            $row['created_at']
        );
    }

    // Create new project
    public function create(array $data): Project {
        $stmt = $this->pdo->prepare(
            "INSERT INTO projects (title, description, created_by, status) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['created_by'],
            $data['status'] ?? 'active'
        ]);

        return new Project(
            $this->pdo->lastInsertId(),
            $data['title'],
            $data['description'],
            $data['created_by'],
            $data['status'] ?? 'active',
            date('Y-m-d H:i:s')
        );
    }

    // Update project
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE projects SET title = ?, description = ?, status = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['status'] ?? 'active',
            $id
        ]);
    }

  
public function delete(int $id): bool {
    try {
        // Disable foreign key checks (MySQL specific)
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Delete the project
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        // Re-enable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        return $result;
    } catch (Exception $e) {
        // Re-enable foreign key checks even if error occurs
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        return false;
    }
}

    // COUNT METHODS - Add these
    public function countAll(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM projects");
        return (int)$stmt->fetchColumn();
    }

    public function countByManager(int $managerId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM projects WHERE created_by = ?");
        $stmt->execute([$managerId]);
        return (int)$stmt->fetchColumn();
    }

    // GET RECENT - Add this
    public function getRecent(int $limit = 5): array {
        $stmt = $this->pdo->prepare("SELECT * FROM projects ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $projects = [];
        foreach ($rows as $row) {
            $projects[] = new Project(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['created_by'],
                $row['status'],
                $row['created_at']
            );
        }
        return $projects;
    }

    // For dashboard display - get projects with manager info
    public function getAllWithManager(): array {
        $stmt = $this->pdo->query("
            SELECT p.*, u.name as manager_name 
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            ORDER BY p.created_at DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $projects = [];
        foreach ($rows as $row) {
            $project = new Project(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['created_by'],
                $row['status'],
                $row['created_at']
            );
            $project->manager_name = $row['manager_name'] ?? '';
            $projects[] = $project;
        }
        return $projects;
    }
}