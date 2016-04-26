<?php namespace App\Models;

use DB;
use App\Traits\JsonTrait;

class Component extends BaseModel
{
    use JsonTrait;

    const OPERATOR_FREE = 'free';
    const OPERATOR_GT = '>';
    const OPERATOR_LT = '<';
    const OPERATOR_NGT = '<=';
    const OPERATOR_NLT = '>=';
    const OPERATOR_EQ = '=';

    public static function boot()
    {
        parent::boot();

        // listeners
        Component::deleting(function (self $component) {
            // decrease sequence of items after
            Component::where('challenge_id', $component->challenge_id)
                ->where('sequence', '>', $component->sequence)
                ->update(['sequence' => DB::raw('sequence - 1')]);
        });
    }


    public function challenge()
    {
        return $this->belongsTo('App\Models\Challenge');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'sequence' => (int)$this->sequence,
            'image' => env('KGB_DEFAULT_IMAGE_COMPONENT'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_COMPONENT')
        ], $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }
}