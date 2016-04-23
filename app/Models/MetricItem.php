<?php namespace App\Models;

use App\Traits\JsonTrait;

class MetricItem extends BaseModel
{
    use JsonTrait;


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail();

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_METRIC_ITEM'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_METRIC_ITEM'),
        ], $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }

    public function metric()
    {
        return $this->belongsTo('App\Models\Metric');
    }
}