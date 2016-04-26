<?php namespace App\Http\Controllers\Design;

use App\Models\Reward;
use App\Models\RewardDispatcher;
use App\Models\RewardTemplate;
use Response;
use Request;
use App\Http\Controllers\Controller;

class RewardController extends Controller
{
    public function getList($class, $id)
    {
        $class = RewardDispatcher::getClass($class);
        $object = $class::find($id);

        return ['items' => $object->rewards];
    }


    public function getDispatchers($class)
    {
        $class = RewardDispatcher::getClass($class);

        $items = RewardDispatcher::where([
            'self_class' => $class,
        ])->get();

        return ['items' => $items];
    }


    public function postCreate($class, $id)
    {
        $class = RewardDispatcher::getClass($class);
        $object = $class::find($id);


        Reward::create([
            'metric_id' => Request::get('metric_id'),
            'reward_dispatcher_id' => Request::get('dispatcher_id'),

            'belongs_to_class' => $class,
            'belongs_to_id' => $id,

            'json' => json_encode([
                'verb' => Request::get('verb'),
                'item_remote_id' => Request::get('item_remote_id'),
                'value' => (string)Request::get('value')
            ])
        ]);

        return [
            'items' => $object->rewards,
            'msg' => 'reward_added'
        ];
    }


    public function postDelete($id)
    {
        Reward::find($id)->delete();

        return ['msg' => 'reward_deleted'];
    }
}