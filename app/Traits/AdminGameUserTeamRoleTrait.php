<?php namespace App\Traits;

use App\Models\GameUserTeamRole;
use Playlyfe\Sdk\PlaylyfeException;

trait AdminGameUserTeamRoleTrait
{
    static public function applyMemberRolesInPlaylyfe($gameUser, $team, $roles = [])
    {
        $gameUserTeamRoles = self::where([
            'game_user_id' => $gameUser->id,
            'team_id' => $team->id
        ])->get();

        $allRoles = $team->jsonGet('roles', 'remote');

        $memberRoles = [];
        // assume all roles are false
        foreach ($allRoles as $role) {
            array_set($memberRoles, $role, false);
        }

        // overwrite those roles are true
        foreach ($gameUserTeamRoles as $gameUserTeamRole) {
            array_set($memberRoles, $gameUserTeamRole->role, true);
        }

        // merge function passed in
        $memberRoles = array_merge($memberRoles, $roles);

        // try to leave team first
        try {
            GameUserTeamRole::connByGame($team->game)->post('/admin/teams/' . $team->remote_id . '/leave', [], [
                'player_id' => $gameUser->user->playerId
            ]);

        } catch (PlaylyfeException $e) {
            // do nothing...
        }


        // join team if at least one role is true
        if (in_array(true, $memberRoles)) {
            GameUserTeamRole::connByGame($team->game)->post('/admin/teams/' . $team->remote_id . '/join', [], [
                'player_id' => $gameUser->user->playerId,
                'requested_roles' => $memberRoles
            ]);
        }
    }
}