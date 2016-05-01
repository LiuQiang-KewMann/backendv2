<?php namespace App\Http\Controllers\Client;

use App\Models\GameUser;
use App\Models\Process;
use App\Http\Controllers\Controller;

class ProcessController extends Controller
{
    public function getList($gameId)
    {
        $gameUser = GameUser::firstOrNew([
            'game_id' => $gameId,
            'user_id' => $this->user->id
        ]);

        $processesArray = $gameUser->game->processes->toArray();
        Process::updateRuntimeStatus($processesArray, $gameUser);

        return ['items' => $processesArray];
    }


    public function getDetail($id)
    {
        $item = Process::find($id);
        
        return ['item' => $item->detail()];
    }
}