<?php namespace App\Traits;

use App\Models\Metric;
use App\Models\MetricItem;

trait RuntimeMetricTrait
{
    public static function localizeScoreMetric($gameId, &$score)
    {
        $metricRemoteId = array_get($score, 'metric.id');

        $metric = Metric::firstOrNew([
            'game_id' => $gameId,
            'remote_id' => $metricRemoteId
        ]);

        // set metric name, description, image & image_thumb
        array_set($score, 'metric.name', $metric->jsonGet('name'));
        array_set($score, 'metric.description', $metric->jsonGet('description'));
        array_set($score, 'metric.image', $metric->jsonGet('image'));
        array_set($score, 'metric.image_thumb', $metric->jsonGet('image_thumb'));

        $type = $metric->jsonGet('type');
        if ($type == Metric::TYPE_STATE) {
            self::localizeScoreStateValue($metric->id, $score);

        } else if ($type == Metric::TYPE_SET) {
            self::localizeScoreSetValue($metric->id, $score);
        }
    }


    /*
     * for state only
     */
    public static function localizeScoreStateValue($metricId, &$score)
    {
        // metricItemRemoteName MUST be same as remoteId
        $metricItemRemoteName = array_get($score, 'value.name');

        $metricItem = MetricItem::firstOrNew([
            'metric_id' => $metricId,
            'remote_id' => $metricItemRemoteName
        ]);

        // set value name, description, image & image_thumb
        array_set($score, 'value.name', $metricItem->jsonGet('name'));
        array_set($score, 'value.description', $metricItem->jsonGet('description'));
        array_set($score, 'value.image', $metricItem->jsonGet('image'));
        array_set($score, 'value.image_thumb', $metricItem->jsonGet('image_thumb'));
    }


    /*
     * for set only
     */
    public static function localizeScoreSetValue($metricId, &$score)
    {
        // get all value items
        $valueArray = array_get($score, 'value');

        // do for each value item
        foreach ($valueArray as &$value) {
            // metricItemRemoteName MUST be same as remoteId
            $metricItemRemoteName = array_get($value, 'name');

            $metricItem = MetricItem::firstOrNew([
                'metric_id' => $metricId,
                'remote_id' => $metricItemRemoteName
            ]);

            // set value name, description, image & image_thumb
            array_set($value, 'name', $metricItem->jsonGet('name'));
            array_set($value, 'description', $metricItem->jsonGet('description'));
            array_set($value, 'image', $metricItem->jsonGet('image'));
            array_set($value, 'image_thumb', $metricItem->jsonGet('image_thumb'));
        }

        // write back
        array_set($score, 'value', $valueArray);
    }
}