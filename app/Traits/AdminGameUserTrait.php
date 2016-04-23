<?php namespace App\Traits;

use Playlyfe\Sdk\PlaylyfeException;

/*
 * GameUser got $game attribute, hence can use $this->conn() directly
 */
trait AdminGameUserTrait
{
    public function addPlayerInEngine()
    {
        try {
            $this->conn()->post('/admin/players', [], [
                'id' => $this->user->playerId,
                'alias' => $this->user->playerId,
            ]);
        } catch (PlaylyfeException $e) {
            // do nothing...
        }
    }


    public function removePlayerInEngine()
    {
        try {
            $this->conn()->delete('/admin/players/' . $player->playerId, []);

        } catch (PlaylyfeException $e) {
            // do nothing...
        }
    }


    public function resetPlayerInEngine()
    {
        try {
            $this->conn()->post('/admin/players/' . $player->playerId . '/reset', []);

        } catch (PlaylyfeException $e) {
            // do nothing...
        }
    }
}