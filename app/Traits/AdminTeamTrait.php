<?php namespace App\Traits;

use App\Models\GameUserTeamRole;
use App\Models\JsonSchema;
use App\Models\Team;

/*
 * Please be noted that team in KGB is team instance in Playlyfe
 */
trait AdminTeamTrait
{
    public function createRemoteInstance()
    {
        $res = $this->conn()->post('/admin/teams', [], [
            'name' => $this->jsonGet('name'),
            'definition' => $this->jsonGet('definition')
        ]);

        $this->remote_id = $res['id'];
        $this->json = json_encode($res);
    }


    public function deleteRemoteInstanceIfThere()
    {
        $this->conn()->delete('/admin/teams/' . $this->remote_id, []);
    }


    public static function sync($game)
    {
        $remoteTeams = Team::connByGame($game)->get('/admin/teams', [])['data'];
        $remoteTeamIds = array_column($remoteTeams, 'id');

        Team::where('game_id', $game->id)
            ->whereNotIn('remote_id', $remoteTeamIds)
            ->update(['remote_id' => 0]);

        Team::where('remote_id', 0)->delete();

        // update existing ones
        foreach ($remoteTeams as $remoteTeam) {
            $updateArray = array_except($remoteTeam, JsonSchema::names('team', 'edit'));

            $team = Team::firstOrCreate([
                'game_id' => $game->id,
                'remote_id' => $remoteTeam['id']
            ])->jsonUpdate($updateArray);

            // remove members of deleted roles
            $roles = $remoteTeam['roles'];
            GameUserTeamRole::where('team_id', $team->id)
                ->whereNotIn('role', $roles)
                ->delete();
        }
    }
}