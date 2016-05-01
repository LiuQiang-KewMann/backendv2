<?php namespace App\Models;

use App\Traits\AdminGameUserTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeGameUserTrait;

class GameUser extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use AdminGameUserTrait;
    use RuntimeGameUserTrait;

    const ROLE_DESIGNER = 'designer';
    const ROLE_ADMIN = 'admin';
    const ROLE_PLAYER = 'player';

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $gameUser) {
            $gameUser->addPlayerInEngine();
        });


        self::deleting(function (self $gameUser) {
            $gameUser->removePlayerInEngine();
        });
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail(['game_id']);
        $userArray = $this->user->toArray();

        // merge with userArray
        // while gameUser will overwrite userArray if same key exists
        $array = array_merge(
            $userArray,
            $array
        );

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }


    public function getProfileAttribute()
    {
        return parent::brief([
            'email',
            'image',
            'image_thumb',
            'first_name',
            'last_name',
            'nickname'
        ]);
    }


    public function getScoresAttribute()
    {
        $scores = $this->jsonGet('scores');

        foreach ($scores as &$score) {
            Metric::localizeScoreMetric($this->game_id, $score);
        }

        return $scores;
    }


    public function getTeamsAttribute()
    {

    }
}