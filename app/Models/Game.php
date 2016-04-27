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

    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_GAME'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_GAME')
        ], $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'code',
            'db_id',
            'image',
            'image_thumb',
            'title',
            'description',
            'header_color'
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