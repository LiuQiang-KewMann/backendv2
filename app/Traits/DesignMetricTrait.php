<?php namespace App\Traits;

use App\Models\JsonSchema;
use App\Models\Metric;
use App\Models\MetricItem;

trait DesignMetricTrait
{
    static public function syncList($game)
    {
        // get headers
        $headers = self::connByGame($game)->get('/design/versions/latest/metrics');

        // delete those not exist
        Metric::where('game_id', $game->id)
            ->whereNotIn('remote_id', array_column($headers, 'id'))
            ->delete();


        // first or create
        foreach ($headers as $header) {
            Metric::firstOrCreate([
                'game_id' => $game->id,
                'remote_id' => $header['id']
            ]);
        }
    }


    public function syncDesign()
    {
        $res = $this->conn()->get('/design/versions/latest/metrics/' . $this->remote_id);

        // 1 .===============================
        // Metric
        // remove local attributes and update
        $metricUpdateJson = array_except($res, JsonSchema::names('metric', 'edit'));
        $this->jsonUpdate($metricUpdateJson);


        // 2 .===============================
        // MetricItem
        $type = array_get($res, 'type');
        $itemPath = array_get([
            'set' => 'constraints.items',
            'state' => 'constraints.states'
        ], $type);

        if ($itemPath) {
            $items = array_get($res, $itemPath);
            $itemIds = array_column($items, 'id');

            // delete those not exist
            MetricItem::where('metric_id', $this->id)
                ->whereNotIn('remote_id', $itemIds)
                ->delete();

            // update or create
            foreach ($items as $item) {
                $metricItem = MetricItem::firstOrCreate([
                    'metric_id' => $this->id,
                    'remote_id' => array_get($item, 'id')
                ]);

                // remove local attributes and update
                $metricItemUpdateJson = array_except($item, JsonSchema::names('metric', 'edit_' . $type));
                $metricItem->jsonUpdate($metricItemUpdateJson);
            }
        }

        return $this;
    }
}