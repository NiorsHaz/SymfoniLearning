<?php

namespace App\DTO;
use App\Entity\Project;
use App\Entity\Task;

class ProjectDTO
{
    public $id;
    public $name;
    public $description;
    public $tasks;

    public function __construct(Project $project)
    {
        $this->id = $project->getId();
        $this->name = $project->getName();
        $this->description = $project->getDescription();
        $this->tasks = array_map(function (Task $task) {
            return [
                'id' => $task->getId(),
                'name' => $task->getTitle(),
                'status' => $task->getStatus()
            ];
        }, $project->getTasks()->toArray());
    }
}
?>