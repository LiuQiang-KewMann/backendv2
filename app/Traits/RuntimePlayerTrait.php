<?php namespace App\Traits;

use DB;
use App\Models\Game;
use App\Models\GameUser;
use App\User;

trait RuntimePlayerTrait
{
    static public function refreshProfile(Game $game, User $user)
    {
        $profile = User::connByGame($game)->get('/runtime/player', [
            'player_id' => $user->playerId
        ]);

        GameUser::firstOrNew([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'role' => User::ROLE_PLAYER

        ])->jsonUpdate([
            'profile' => $profile
        ], 'remote');
    }


    public function hasGameRole($gameId, $role)
    {
        return DB::table('game_user')->where('game_id', $gameId)
            ->where('user_id', $this->id)
            ->where('role', $role)
            ->exists();
    }


    static public function playableGames(User $user)
    {
        // game list consists of two parts, public and played
        $publicGames = Game::ofAccess('public')->get();
        $playedGames = $user->playGames;

        // merge two groups of games
        return $publicGames->merge($playedGames);
    }
}