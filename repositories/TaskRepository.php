<?php
require_once __DIR__ . '/../entities/Task.php';

class TaskRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Get all tasks
    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM tasks");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];
        foreach ($rows as $row) {
            $task = new Task();
            $task->id = (int)$row['id'];
            $task->sprint_id = (int)$row['sprint_id'];
            $task->title = $row['title'];
            $task->description = $row['description'];
            $task->status = $row['status'];
            $task->priority = $row['priority'];
            $task->created_at = $row['created_at'];
            
            $tasks[] = $task;
        }
        return $tasks;
    }

    // Get tasks by sprint ID
    public function getBySprint(int $sprint_id): array {
        if (!$sprint_id || $sprint_id <= 0) {
            return [];
        }
        
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE sprint_id = ?");
        $stmt->execute([$sprint_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];
        foreach ($rows as $row) {
            $task = new Task();
            $task->id = (int)$row['id'];
            $task->sprint_id = (int)$row['sprint_id'];
            $task->title = $row['title'];
            $task->description = $row['description'];
            $task->status = $row['status'];
            $task->priority = $row['priority'];
            $task->created_at = $row['created_at'];
            
            $tasks[] = $task;
        }
        return $tasks;
    }

    // Get tasks assigned to a specific user (using task_users table)
    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT t.* 
            FROM tasks t
            INNER JOIN task_users tu ON t.id = tu.task_id
            WHERE tu.user_id = ?
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];
        foreach ($rows as $row) {
            $task = new Task();
            $task->id = (int)$row['id'];
            $task->sprint_id = (int)$row['sprint_id'];
            $task->title = $row['title'];
            $task->description = $row['description'];
            $task->status = $row['status'];
            $task->priority = $row['priority'];
            $task->created_at = $row['created_at'];
            
            $tasks[] = $task;
        }
        return $tasks;
    }

    // Get single task by ID
    public function getById(int $id): ?Task {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $task = new Task();
        $task->id = (int)$row['id'];
        $task->sprint_id = (int)$row['sprint_id'];
        $task->title = $row['title'];
        $task->description = $row['description'];
        $task->status = $row['status'];
        $task->priority = $row['priority'];
        $task->created_at = $row['created_at'];

        return $task;
    }

    // Create new task
    public function create(array $data): Task {
        // Validate required fields
        if (empty($data['sprint_id']) || empty($data['title'])) {
            throw new Exception("Sprint ID and title are required");
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO tasks (sprint_id, title, description, status, priority) 
             VALUES (:sprint_id, :title, :description, :status, :priority)"
        );
        
        $result = $stmt->execute([
            ':sprint_id' => $data['sprint_id'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':status' => $data['status'] ?? 'todo',
            ':priority' => $data['priority'] ?? 'medium'
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Failed to create task: " . ($errorInfo[2] ?? 'Unknown error'));
        }

        // Get the newly created task
        $taskId = (int)$this->pdo->lastInsertId();
        
        // If assigned user is provided, assign the task
        if (isset($data['assigned_to']) && $data['assigned_to']) {
            $this->assignToUser($taskId, (int)$data['assigned_to']);
        }

        return $this->getById($taskId);
    }

    // Assign task to user (using task_users table)
    public function assignToUser(int $taskId, int $userId, string $role = 'owner'): bool {
        // First, check if assignment already exists
        $stmt = $this->pdo->prepare("SELECT id FROM task_users WHERE task_id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
        
        if ($stmt->fetch()) {
            return true; // Already assigned
        }

        // Create new assignment
        $stmt = $this->pdo->prepare(
            "INSERT INTO task_users (task_id, user_id, role) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$taskId, $userId, $role]);
    }

    // Update task
    public function update(int $id, array $data): bool {
        $currentTask = $this->getById($id);
        if (!$currentTask) {
            throw new Exception("Task not found");
        }

        $stmt = $this->pdo->prepare(
            "UPDATE tasks SET 
                title = :title, 
                description = :description, 
                status = :status, 
                priority = :priority 
             WHERE id = :id"
        );
        
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'] ?? $currentTask->title,
            ':description' => $data['description'] ?? $currentTask->description,
            ':status' => $data['status'] ?? $currentTask->status,
            ':priority' => $data['priority'] ?? $currentTask->priority
        ]);
    }

    // Update only task status
    public function updateStatus(int $id, string $status): bool {
        $allowedStatuses = ['todo', 'in_progress', 'done'];
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception("Invalid status. Allowed: " . implode(', ', $allowedStatuses));
        }

        $stmt = $this->pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    // Delete task
    public function delete(int $id): bool {
        // First delete from task_users (foreign key constraint)
        $stmt = $this->pdo->prepare("DELETE FROM task_users WHERE task_id = ?");
        $stmt->execute([$id]);
        
        // Then delete the task
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Count tasks by sprint
    public function countBySprint(int $sprintId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE sprint_id = ?");
        $stmt->execute([$sprintId]);
        return (int)$stmt->fetchColumn();
    }

    // Count tasks by status
    public function countByStatus(string $status): int {
        $allowedStatuses = ['todo', 'in_progress', 'done'];
        if (!in_array($status, $allowedStatuses)) {
            return 0;
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    // Get tasks with sprint info (for dashboard display)
    public function getAllWithSprintInfo(): array {
        $stmt = $this->pdo->query("
            SELECT t.*, s.name as sprint_name, s.project_id
            FROM tasks t
            LEFT JOIN sprints s ON t.sprint_id = s.id
            ORDER BY t.created_at DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [];
        foreach ($rows as $row) {
            $task = new Task();
            $task->id = (int)$row['id'];
            $task->sprint_id = (int)$row['sprint_id'];
            $task->title = $row['title'];
            $task->description = $row['description'];
            $task->status = $row['status'];
            $task->priority = $row['priority'];
            $task->created_at = $row['created_at'];
            // Add extra info
            $task->sprint_name = $row['sprint_name'] ?? '';
            $task->project_id = (int)($row['project_id'] ?? 0);
            
            $tasks[] = $task;
        }
        return $tasks;
    }

public function countAll(): int {
    $stmt = $this->pdo->query("SELECT COUNT(*) FROM tasks");
    return (int)$stmt->fetchColumn();
}

// GET RECENT
public function getRecent(int $limit = 5): array {
    $stmt = $this->pdo->prepare("
        SELECT t.*, s.name as sprint_name 
        FROM tasks t
        LEFT JOIN sprints s ON t.sprint_id = s.id
        ORDER BY t.created_at DESC LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tasks = [];
    foreach ($rows as $row) {
        $task = new Task();
        $task->id = (int)$row['id'];
        $task->sprint_id = (int)$row['sprint_id'];
        $task->title = $row['title'];
        $task->description = $row['description'];
        $task->status = $row['status'];
        $task->priority = $row['priority'];
        $task->created_at = $row['created_at'];
        $task->sprint_name = $row['sprint_name'] ?? '';
        $tasks[] = $task;
    }
    return $tasks;
}
// TaskRepository.php - Add this method
public function isTaskAssignedToUser(int $taskId, int $userId): bool {
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM task_users WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    return (int)$stmt->fetchColumn() > 0;
}

// OR add this method to get user's tasks in a specific sprint
public function getByUserAndSprint(int $userId, int $sprintId): array {
    $stmt = $this->pdo->prepare("
        SELECT t.* 
        FROM tasks t
        INNER JOIN task_users tu ON t.id = tu.task_id
        WHERE tu.user_id = ? AND t.sprint_id = ?
    ");
    $stmt->execute([$userId, $sprintId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tasks = [];
    foreach ($rows as $row) {
        $task = new Task();
        $task->id = (int)$row['id'];
        $task->sprint_id = (int)$row['sprint_id'];
        $task->title = $row['title'];
        $task->description = $row['description'];
        $task->status = $row['status'];
        $task->priority = $row['priority'];
        $task->created_at = $row['created_at'];
        
        $tasks[] = $task;
    }
    return $tasks;
}}
