<?php
require_once __DIR__ . '/../entities/Sprint.php';

class SprintRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Get all sprints
    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM sprints");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sprints = [];
        foreach ($rows as $row) {
            $sprints[] = new Sprint(
                $row['id'],
                $row['project_id'],
                $row['name'],
                $row['start_date'],
                $row['end_date']
            );
        }
        return $sprints;
    }

    // Get sprints by project ID
    public function getByProject(int $project_id): array {
        if (!$project_id) return [];
        
        $stmt = $this->pdo->prepare("SELECT * FROM sprints WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sprints = [];
        foreach ($rows as $row) {
            $sprints[] = new Sprint(
                $row['id'],
                $row['project_id'],
                $row['name'],
                $row['start_date'],
                $row['end_date']
            );
        }
        return $sprints;
    }

    // Get single sprint by ID
    public function getById(int $id): ?Sprint {
        $stmt = $this->pdo->prepare("SELECT * FROM sprints WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new Sprint(
            $row['id'],
            $row['project_id'],
            $row['name'],
            $row['start_date'],
            $row['end_date']
        );
    }

    // Create new sprint
    public function create(array $data): Sprint {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sprints (project_id, name, start_date, end_date) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['project_id'],
            $data['name'],
            $data['start_date'],
            $data['end_date']
        ]);

        return new Sprint(
            $this->pdo->lastInsertId(),
            $data['project_id'],
            $data['name'],
            $data['start_date'],
            $data['end_date']
        );
    }

    // Update sprint
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE sprints SET name = ?, start_date = ?, end_date = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $id
        ]);
    }

    // Delete sprint
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sprints WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // COUNT METHODS - Add these
    public function countAll(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM sprints");
        return (int)$stmt->fetchColumn();
    }

    public function countByProject(int $projectId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sprints WHERE project_id = ?");
        $stmt->execute([$projectId]);
        return (int)$stmt->fetchColumn();
    }

    // GET RECENT - Add this
    public function getRecent(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT s.*, p.title as project_title 
            FROM sprints s
            LEFT JOIN projects p ON s.project_id = p.id
            ORDER BY s.start_date DESC LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sprints = [];
        foreach ($rows as $row) {
            $sprint = new Sprint(
                $row['id'],
                $row['project_id'],
                $row['name'],
                $row['start_date'],
                $row['end_date']
            );
            $sprint->project_title = $row['project_title'] ?? '';
            $sprints[] = $sprint;
        }
        return $sprints;
    }

    // GET ACTIVE SPRINTS - Add this
    public function getActiveSprints(int $projectId = null): array {
        $today = date('Y-m-d');
        
        if ($projectId) {
            $stmt = $this->pdo->prepare("
                SELECT s.*, p.title as project_title 
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                WHERE s.project_id = ? 
                AND s.start_date <= ? 
                AND s.end_date >= ?
                ORDER BY s.start_date DESC
            ");
            $stmt->execute([$projectId, $today, $today]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT s.*, p.title as project_title 
                FROM sprints s
                LEFT JOIN projects p ON s.project_id = p.id
                WHERE s.start_date <= ? 
                AND s.end_date >= ?
                ORDER BY s.start_date DESC
            ");
            $stmt->execute([$today, $today]);
        }
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sprints = [];
        foreach ($rows as $row) {
            $sprint = new Sprint(
                $row['id'],
                $row['project_id'],
                $row['name'],
                $row['start_date'],
                $row['end_date']
            );
            $sprint->project_title = $row['project_title'] ?? '';
            $sprints[] = $sprint;
        }
        return $sprints;
    }

    // Get sprints with project info for dashboard
    public function getAllWithProjectInfo(): array {
        $stmt = $this->pdo->query("
            SELECT s.*, p.title as project_title 
            FROM sprints s
            LEFT JOIN projects p ON s.project_id = p.id
            ORDER BY s.start_date DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sprints = [];
        foreach ($rows as $row) {
            $sprint = new Sprint(
                $row['id'],
                $row['project_id'],
                $row['name'],
                $row['start_date'],
                $row['end_date']
            );
            $sprint->project_title = $row['project_title'] ?? '';
            $sprints[] = $sprint;
        }
        return $sprints;
    }
}