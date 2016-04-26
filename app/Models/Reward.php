<?php namespace App\Models;

use App\Traits\DesignRewardTrait;
use App\Traits\JsonTrait;
use App\Traits\RuntimeRewardTrait;

class Reward extends BaseModel
{
    use JsonTrait;
    use DesignRewardTrait;
    use RuntimeRewardTrait;

    public static function boot()
    {
        parent::boot();

        Reward::creating(function (Reward $reward) {
            // when creating reward, make sure the reward is compliance to Playlyfe Schema
            $reward->formalizeReward();
        });
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail($additionalAttributes);

        $array = array_merge($array, [
            'metric' => $this->metric,
            'dispatcher' => $this->dispatcher
        ]);

        return $array;
    }

    public function toArray()
    {
        $array = parent::brief([
            'db_id',
            'metric',
            'dispatcher',
            'reward'
        ]);
        return $array;
    }


    public function metric()
    {
        return $this->belongsTo('App\Models\Metric');
    }


    public function dispatcher()
    {
        return $this->belongsTo('App\Models\RewardDispatcher', 'reward_dispatcher_id');

    }
}