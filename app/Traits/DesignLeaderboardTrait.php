<?php namespace App\Traits;

use App\Models\JsonSchema;
use App\Models\Leaderboard;

trait DesignLeaderboardTrait
{
    static public function syncList($game)
    {
        $headers = Leaderboard::connByGame($game)->get('/design/versions/latest/leaderboards');

        // delete those not exist
        Leaderboard::where('game_id', $game->id)
            ->whereNotIn('remote_id', array_column($headers, 'id'))
            ->delete();

        // first or create
        foreach ($headers as $header) {
            Leaderboard::firstOrCreate([
                'game_id' => $game->id,
                'remote_id' => $header['id']
            ]);
        }
    }


    public function syncDesign()
    {
        $res = $this->conn()->get('/design/versions/latest/leaderboards/' . $this->remote_id);

        // remove local attributes and update
        $leaderboardUpdateJson = array_except($res, JsonSchema::names('leaderboard', 'edit'));
        $this->jsonUpdate($leaderboardUpdateJson);

        // END
        return $this;
    }
}