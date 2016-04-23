<?php namespace App\Http\Controllers\Design;

use App\Models\Game;
use App\Models\JsonSchema;
use App\Models\Metric;
use Response;
use Request;
use App\Http\Controllers\Controller;

class MetricController extends Controller
{
    public function getList($gameId)
    {
        $game = Game::find($gameId);

        return [
            'items' => $game->metrics,
            'parent' => $game
        ];
    }


    public function getDetail($id)
    {
        $item = Metric::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('metric', 'edit'),
            'schema_config' => JsonSchema::items('metric', 'config')
        ];
    }


    public function getSyncList($gameId)
    {
        $game = Game::find($gameId);
        Metric::syncList($game);

        return [
            'items' => $game->metrics,
            'parent' => $game
        ];
    }


    public function getSync($id)
    {
        $item = Metric::find($id);
        $item->syncDesign();

        return [
            'item' => $item->detail()
        ];
    }


    public function postUpdate($id)
    {
        $jsonArray = Request::only(JsonSchema::names('metric', 'edit'));

        $item = Metric::find($id);
        $item->jsonUpdate($jsonArray);

        return [
            'item' => $item->detail()
        ];
    }
}