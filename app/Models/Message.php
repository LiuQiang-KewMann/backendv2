<?php namespace App\Models;

use App\Traits\JsonTrait;

class Message extends BaseModel
{
    use JsonTrait;

    public $timestamps = true;

    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function from()
    {

    }

    public function to()
    {

    }
}