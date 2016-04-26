<?php namespace App\Traits;

use App\Models\Reward;

trait RewardTrait
{
    public function getRewardsAttribute()
    {
        return Reward::where([
            'belongs_to_class' => self::class,
            'belongs_to_id' => $this->id
        ])->orderBy('reward_dispatcher_id')->get();
    }
}