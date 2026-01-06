<?php
require_once __DIR__ . '/../repositories/ProjectRepository.php';

class ProjectService
{
    private ProjectRepository $repo;

    public function __construct(PDO $pdo)
    {
        $this->repo = new ProjectRepository($pdo);
    }

    public function listProjects(): array
    {
        return $this->repo->getAll();
    }

    public function createProject(string $title, int $created_by, string $description = ''): Project
    {
        return $this->repo->create([
            'title' => $title, // Changed from 'name' to 'title'
            'created_by' => $created_by, // Changed from 'manager_id' to 'created_by'
            'description' => $description
        ]);
    }

    public function getProject(int $id): ?Project
    {
        return $this->repo->getById($id); // Changed from findById to getById
    }

    public function updateProject(int $id, array $data): bool
    {
        return $this->repo->update($id, $data);
    }

    public function deleteProject(int $id): bool
    {
        return $this->repo->delete($id);
    }
}