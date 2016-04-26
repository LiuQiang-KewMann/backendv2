<?php namespace App\Models;

use App\Traits\PlaylyfeTrait;
use App\Traits\RewardTrait;
use App\Traits\RuntimeTaskTrait;
use App\Traits\JsonTrait;
use App\User;

class Task extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimeTaskTrait;
    use RewardTrait;


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_TASK'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_TASK')
        ], $array);


        // if mapped to challenge, then challenge info will overwrite task
        if ($this->challenge->exists) {
            $array = array_merge($array, $this->challenge->brief([
                'image',
                'image_thumb',
                'title',
                'challenge_number',
                'challenge_db_id',
                'description'
            ]));
        }

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'remote_id',
            'db_id',
            'image',
            'image_thumb',
            'challenge_number',
            'title'
        ]);
    }


    public function process()
    {
        return $this->belongsTo('App\Models\Process');
    }


    public function getChallengeAttribute()
    {
        return Challenge::firstOrNew([
            'game_id' => $this->process->game_id,
            'number' => $this->challenge_number ?: 0
        ]);
    }
}