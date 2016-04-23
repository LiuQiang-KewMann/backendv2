<?php namespace App\Models;

use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeTaskTrait;
use App\Traits\JsonTrait;
use App\User;

class Task extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimeTaskTrait;

    const STATUS_COMPLETED = 'completed';
    const STATUS_ACTIVE = 'active';
    const STATUS_LOOPING = 'looping';
    const STATUS_EXPIRED = 'expired';

    const RESULT_PASS = 'pass';
    const RESULT_FAIL = 'fail';
    const RESULT_PENDING = 'pending';


//    public function detail()
//    {
//        $array = parent::detail();
//
//
//        // START: process rewards metrics <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//        $rewards = array_get($array, 'mixed.rewards');
//        $gameCode = $this->game_code;
//
//        // add metric name & description
//        array_walk($rewards, function (&$value, $key, $gameCode) {
//            $metricFullCode = $gameCode . '.' . array_get($value, 'metric.id');
//            $metric = Metric::firstOrNew(['fullcode' => $metricFullCode]);
//
//            array_set($value, 'metric.name', $metric->jsonGet('name'));
//            array_set($value, 'metric.description', $metric->jsonGet('description'));
//        }, $gameCode);
//
//        // write back
//        array_set($array, 'mixed.rewards', $rewards);
//        // END: process rewards metrics >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
//
//
//
//        // START: process challengeSet <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//        $challengeSetCode = $this->jsonGet('challenge_set_code');
//        $challengeSet = ChallengeSet::firstOrNew([
//            'game_code' => $this->game_code,
//            'code' => $challengeSetCode
//        ]);
//
//        if ($challengeSet->exists) {
//            array_set($array, 'challenge_set', $challengeSet->detail());
//        }
//        // END: process challengeSet <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//
//        return array_merge($array, [
//            'code' => $this->code,
//            'fullcode' => $this->fullcode
//        ]);
//    }


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
                'challenge_db_id'
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


    public function getRewardsAttribute()
    {
        return Reward::where([
            'master_class' => self::class,
            'master_db_id' => $this->id
        ])->get();
    }


    public function historyByGameAndUser(Game $game, User $user)
    {
        return TaskHistory::firstOrNew([
            'game_id' => $game->id,
            'task_id' => $this->id,
            'user_id' => $user->id,
        ]);
    }

    public function historiesByGame(Game $game)
    {
        return TaskHistory::where([
            'game_id' => $game->id,
            'task_id' => $this->id,
        ])->get();
    }
}