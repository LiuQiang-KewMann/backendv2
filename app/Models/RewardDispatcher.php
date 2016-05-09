<?php namespace App\Models;

use App\Traits\JsonTrait;

class RewardDispatcher extends BaseModel
{
    use JsonTrait;
    
    const OPERATOR_FREE = 'free';
    const OPERATOR_EQ = 'eq';
    const OPERATOR_NE = 'ne';
    const OPERATOR_NOTNULL = 'nn';

    /*
     * this function returns the full class
     */
    public static function getClass($class)
    {
        return array_get([
            'task' => Task::class,
            'process' => Process::class,
            'task_history' => TaskHistory::class,
            'process_history' => ProcessHistory::class
        ], $class);
    }

    
    public function toArray()
    {
        return parent::brief([
            'db_id',
            'label'
        ]);
    }
}