<?php namespace App\Http\Controllers\Design;

use App\Models\Process;
use App\Models\Reward;
use App\Models\Task;
use Response;
use Request;
use App\Http\Controllers\Controller;

class RewardController extends Controller
{
    public function getList($class, $dbId)
    {
        $items = Reward::where([
            'master_class' => Reward::getMappedClass($class),
            'master_db_id' => $dbId
        ])->get();

        return ['items' => $items];
    }


    public function getRequirementJson($class)
    {
        $items = Reward::$classRequirements;

        return ['items' => $items];
    }


    public function postCreate($class, $dbId)
    {

    }


    public function postDelete($id)
    {
        Reward::find($id)->delete();

        return ['msg' => 'reward_deleted'];
    }
}