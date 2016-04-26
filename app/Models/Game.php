<?php namespace App\Models;

use App\Traits\AdminGameTrait;
use App\Traits\DesignGameTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeGameTrait;

class Game extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use DesignGameTrait;
    use RuntimeGameTrait;
    use AdminGameTrait;

    public function toArray()
    {
        return parent::brief([
            'code',
            'db_id',
            'image',
            'image_thumb',
            'title',
            'description'
        ]);
    }


    public function processes()
    {
        return $this->hasMany('App\Models\Process')->orderBy('remote_id');
    }

    public function leaderboards()
    {
        return $this->hasMany('App\Models\Leaderboard')->orderBy('remote_id');
    }

    public function metrics()
    {
        return $this->hasMany('App\Models\Metric')->orderBy('remote_id');
    }

    public function challenges()
    {
        return $this->hasMany('App\Models\Challenge')->orderBy('number');
    }

    public function teams()
    {
        return $this->hasMany('App\Models\Team')->orderBy('remote_id');
    }
}