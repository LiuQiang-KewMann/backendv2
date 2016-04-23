<?php namespace App\Http\Controllers\Admin;

use App\Models\Game;
use App\Models\GameUser;
use App\Models\GameUserTeamRole;
use App\Models\JsonSchema;
use App\Models\Team;
use App\User;
use Playlyfe\Sdk\PlaylyfeException;
use Response;
use Request;
use App\Http\Controllers\Controller;


class TeamRoleController extends Controller
{
    public function getList($teamId)
    {
        $team = Team::find($teamId);

        return [
            'items' => $team->roles,
            'parent' => $team->detail()
        ];
    }


    public function getDetail($teamId, $role)
    {
        $members = GameUserTeamRole::where([
            'team_id' => $teamId,
            'role' => $role
        ])->get();

        $item = [
            'id' => $role,
            'name' => $role,
            'members' => $members
        ];

        return ['item' => $item];
    }


    public function postAddMember($teamId, $role)
    {
        $email = Request::get('email');

        $user = User::firstOrNew(['email' => $email]);
        $team = Team::find($teamId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $team->game->id,
            'user_id' => $user->id,
        ]);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $gameUserTeamRoleQuery = GameUserTeamRole::where([
            'game_user_id' => $gameUser->id,
            'team_id' => $teamId,
            'role' => $role
        ]);

        if ($gameUserTeamRoleQuery->exists()) {
            return Response::json(['msg' => 'player_has_this_role_already'], 406);
        }

        $gameUserTeamRole = GameUserTeamRole::create([
            'game_user_id' => $gameUser->id,
            'team_id' => $teamId,
            'role' => $role
        ]);



        return [
            'item' => $gameUserTeamRole->detail()
        ];
    }


    public function postRemoveMember($teamId, $role)
    {
        $email = Request::get('email');

        $user = User::firstOrNew(['email' => $email]);
        $team = Team::find($teamId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $team->game->id,
            'user_id' => $user->id,
        ]);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $gameUserRole = GameUserTeamRole::firstOrNew([
            'game_user_id' => $gameUser->id,
            'team_id' => $teamId,
            'role' => $role
        ]);

        if (!$gameUserRole->exists) {
            return Response::json(['msg' => 'player_does_not_have_this_role'], 406);
        }

        $gameUserRole->delete();

        return Response::json(['msg' => 'member_removed']);
    }
}