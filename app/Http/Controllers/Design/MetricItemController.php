<?php namespace App\Http\Controllers\Design;

use App\Models\JsonSchema;
use App\Models\Metric;
use App\Models\MetricItem;
use Response;
use Request;
use App\Http\Controllers\Controller;

class MetricItemController extends Controller
{
    public function getList($id)
    {
        $metric = Metric::find($id);

        return Response::json([
            'items' => $metric->items ?: [],
            'parent' => $metric
        ]);
    }

    public function getDetail($id)
    {
        $item = MetricItem::find($id);
        $metric = $item->metric;

        return Response::json([
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('metricItem', 'edit_' . $metric->jsonGet('type')),
        ]);
    }

    public function postUpdate($id)
    {
        $item = MetricItem::find($id);
        $metric = $item->metric;

        $updateArray = Request::only(JsonSchema::names('metricItem', 'edit_' . $metric->jsonGet('type')));

        $item->jsonUpdate($updateArray);

        return Response::json([
            'item' => $item->detail(),
            'msg' => 'metricItem_updated'
        ]);
    }
}