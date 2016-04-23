<?php namespace App\Models;

use App\Traits\DesignChallengeTrait;
use App\Traits\JsonTrait;
use App\Traits\RuntimeChallengeTrait;

class Challenge extends BaseModel
{
    use JsonTrait;
    use DesignChallengeTrait;
    use RuntimeChallengeTrait;

    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_CHALLENGE'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_CHALLENGE')
        ], $array, [
            'challenge_db_id' => (int)$this->id,
            'challenge_number' => (int)$this->number
        ]);

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'title',
            'description',
            'challenge_number',
            'db_id',
            'image',
            'image_thumb'
        ]);
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function components()
    {
        return $this->hasMany('App\Models\Component')->orderBy('sequence');
    }
}