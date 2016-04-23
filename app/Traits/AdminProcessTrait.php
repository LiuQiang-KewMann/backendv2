<?php namespace App\Traits;

use App\Models\Process;
use App\User;
use App\Models\Game;
use App\Models\ProcessHistory;
use Playlyfe\Sdk\PlaylyfeException;

trait AdminProcessTrait
{
    static public function deleteInstanceInEngine(ProcessHistory $processHistory)
    {
        $player = $processHistory->user;
        $instanceId = $processHistory->id_in_engine;

        try {
            Process::connByGame($processHistory->game)->delete('/admin/processes/' . $instanceId, ['player_id' => $player->playerId], []);
        } catch (PlaylyfeException $e) {
            // do nothing...
        }
    }


    static public function openProcessInstances(Game $game, User $player)
    {
        try {
            return Game::connByGame($game)->get('/admin/processes', ['player_id' => $player->playerId], []);
        } catch (PlaylyfeException $e) {
            // do nothing...
        }
    }
}