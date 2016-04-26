<?php namespace App\Traits;

trait DesignRewardTrait
{
    public function formalizeReward()
    {
        $jsonArray = $this->jsonArray();
        $type = $this->metric->jsonGet('type');

        $reward = [];
        array_set($reward, 'metric.type', $type);
        array_set($reward, 'metric.id', $this->metric->remote_id);
        array_set($reward, 'verb', array_get($jsonArray, 'verb'));

        if ($type == 'set') {
            // set
            array_set($reward, 'value', [
                array_get($jsonArray, 'item_remote_id') => array_get($jsonArray, 'value')
            ]);

        } else if ($type == 'state') {
            // state
            array_set($reward, 'value', array_get($jsonArray, 'item_remote_id'));

        } else {
            // point
            array_set($reward, 'value', array_get($jsonArray, 'value'));
        }

        $this->json = json_encode(['reward' => $reward]);
    }
}