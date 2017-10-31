<?php namespace BackupManager\Procedures;

use BackupManager\Tasks\Task;

/**
 * Class Sequence
 * @package BackupManager\Procedures
 */
class Sequence {

    /** @var array|Task[] */
    private $tasks = [];

    /**
     * @param \BackupManager\Tasks\Task $task
     */
    public function add(Task $task) {
        $this->tasks[] = $task;
    }

    /**
     * Run the procedure.
     * @return array
     */
    public function execute() {
        $output = [];
        foreach ($this->tasks as $key => $task) {
            $output[$key]['task'] = get_class($task);
            $output[$key]['output'] = $task->execute();
        }
        return $output;
    }
}
