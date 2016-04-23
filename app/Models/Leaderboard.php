<?php namespace App\Models;

use App\Traits\AdminLeaderboardTrait;
use App\Traits\DesignLeaderboardTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;

class Leaderboard extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use AdminLeaderboardTrait;
    use DesignLeaderboardTrait;


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_LEADERBOARD'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_LEADERBOARD')
        ], $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }
}