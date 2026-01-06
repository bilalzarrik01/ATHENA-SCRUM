<?php
class Sprint
{
    public ?int $id = null;
    public int $project_id = 0;
    public string $name = "";
    public string $start_date = "";
    public string $end_date = "";
    
    // Add for joined queries
    public string $project_name = "";
    public string $project_title = "";

    public function __construct(
        ?int $id = null,
        int $project_id = 0,
        string $name = "",
        string $start_date = "",
        string $end_date = ""
    ) {
        $this->id = $id;
        $this->project_id = $project_id;
        $this->name = $name;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    
    // Helper methods
    public function getFormattedStartDate(): string {
        return date('M d, Y', strtotime($this->start_date));
    }
    
    public function getFormattedEndDate(): string {
        return date('M d, Y', strtotime($this->end_date));
    }
}