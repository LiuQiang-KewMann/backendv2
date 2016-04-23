<?php namespace App\Models;

use App\Traits\DesignMetricTrait;
use App\Traits\JsonTrait;
use App\Traits\MetricTrait;
use App\Traits\PlaylyfeTrait;

class Metric extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use DesignMetricTrait;

    public function getVerbsAttribute()
    {
        return array_get([
            'point' => ['add', 'remove', 'set'],
            'state' => ['set'],
            'set' => ['add', 'remove', 'set'],
        ], $this->jsonGet('type'));
    }


    public function getDefaultVerbAttribute()
    {
        return array_get([
            'point' => 'add',
            'state' => 'set',
            'set' => 'add',
        ], $this->jsonGet('type'));
    }


    public function detail($additionalAttributes = [])
    {
        $detail = parent::detail();

        $detail = array_merge([
            'verbs' => $this->verbs,
            'default_verb' => $this->default_verb,
            'items' => $this->items,
            'image' => env('KGB_DEFAULT_IMAGE_METRIC'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_METRIC')
        ], $detail);

        return $detail;
    }


    public function toArray()
    {
        return parent::brief([
            'db_id',
            'remote_id',
            'type',
            'image',
            'image_thumb',
            'name'
        ]);
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function items()
    {
        return $this->hasMany('App\Models\MetricItem')->orderBy('remote_id');
    }


    // this is to fill in local definition
    public static function kgblize($gameId, &$metric)
    {
        $metricRemoteId = array_get($metric, 'id');

        $metric = self::firstOrNew([
            'game_id' => $gameId,
            'remote_id' => $metricRemoteId
        ]);

        if ($metric->exists) {
            $jsonArray = $metric->jsonArray();

            array_set($metric, 'name');
        }
    }
}