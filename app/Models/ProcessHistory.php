<?php namespace App\Models;

use App\Traits\JsonTrait;

class ProcessHistory extends BaseModel
{
    use JsonTrait;

    protected $guarded = ['id'];


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


    public function getTriggersAttribute()
    {
        return $this->jsonGet('triggers', [], ['local']);
    }
}