<?php namespace App\Models;

use App\Traits\JsonTrait;

class Reward extends BaseModel
{
    use JsonTrait;

    public static $classMapping = [
        'task' => Task::class,
        'process' => Process::class,
    ];

    public static $classRequirements = [
        'task' => [
            'result' => ['pass', 'fail'],
            'status' => ['released', 'viewed', 'completed', 'pending', 'expired'],
        ]
    ];


    public static function getMappedClass($class)
    {
        return array_get(self::$classMapping, $class);
    }

    public function toArray()
    {
        $array = parent::brief([
            'metric',
            'verb',
            'value'
        ]);
        return $array;
    }


    public function metric()
    {
        return $this->belongsTo('App\Models\Metric');
    }
}