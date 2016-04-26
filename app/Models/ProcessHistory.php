<?php namespace App\Models;

use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeProcessHistoryTrait;

class ProcessHistory extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimeProcessHistoryTrait;

    public static function boot()
    {
        parent::boot();

        ProcessHistory::creating(function (ProcessHistory $processHistory) {
            // make sure to create remote instance in Playlyfe
            $processHistory->createRemoteInstance();
        });


        ProcessHistory::created(function (ProcessHistory $processHistory) {
            // make sure triggers are synced
            $processHistory->syncTriggers();
        });
    }


    public function gameUser()
    {
        return $this->belongsTo('App\Models\GameUser');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function process()
    {
        return $this->belongsTo('App\Models\Process');
    }
    
    public function taskHistories()
    {
        return $this->hasMany('App\Models\TaskHistory');
    }
}