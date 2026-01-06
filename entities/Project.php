<?php
class Project {
    public int $id;
    public string $title;
    public ?string $description;
    public int $created_by;
    public string $status;
    public ?string $created_at;
    
    // Add for joined queries
    public string $manager_name = "";

    public function __construct(
        int $id, 
        string $title, 
        ?string $description, 
        int $created_by, 
        string $status = 'active',
        ?string $created_at = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->created_by = $created_by;
        $this->status = $status;
        $this->created_at = $created_at;
    }
    
    // Add getter for name (alias for title for compatibility)
    public function getName(): string {
        return $this->title;
    }
}