<?php namespace App\Models;

use App\Traits\AdminProcessTrait;
use App\Traits\DesignProcessTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeProcessTrait;

class Process extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use DesignProcessTrait;
    use RuntimeProcessTrait;
    use AdminProcessTrait;

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_PROCESS'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_PROCESS')
        ], $array);

        return $array;
    }

    public function toArray()
    {
        return parent::brief([
            'db_id',
            'remote_id',
            'image',
            'image_thumb',
            'name'
        ]);
    }


    public function tasks()
    {
        // gateway with underscore in remote_id will not be shown here
        return $this->hasMany('App\Models\Task')
            ->where('remote_id', 'not like', '%\_%')
            ->orderBy('sequence');
    }

}