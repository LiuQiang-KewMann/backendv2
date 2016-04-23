<?php namespace App\Http\Controllers\Client;

use App\Models\FileManager;
use App\Models\GameUser;
use App\User;
use JWTAuth;
use Response;
use Request;
use App\Models\Game;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    public function getProfile($gameId)
    {
        $user = JWTAuth::parseToken()->toUser();

        $gameUser = GameUser::firstOrNew([
            'game_id' => $gameId,
            'user_id' => $user->id,
            'role' => User::ROLE_PLAYER
        ]);

        return Response::json($gameUser->detail());
    }


    public function postProfile($gameId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $input = Request::only(['nickname']);
        $game = Game::find($gameId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $gameId,
            'user_id' => $user->id,
            'role' => User::ROLE_PLAYER
        ]);

        // player uploaded image
        if(Request::hasFile('fileImage')) {
            $file = Request::file('fileImage');

            // 1. image ......................................................................
            // delete existing image if any
            FileManager::delete($gameUser->jsonGet('image'));
            // upload new image
            $imagePath = FileManager::put($game->code, FileManager::FOLDER_USER, $file);
            array_set($input, 'image', $imagePath);


            // 2. thumb ......................................................................
            // delete existing thumb if any
            FileManager::delete($gameUser->jsonGet('image_thumb'));
            // upload new thumb
            $thumbPath = FileManager::putThumb($file, $imagePath);
            array_set($input, 'image_thumb', $thumbPath);
        }

        $gameUser->jsonUpdate($input);

        return Response::json($gameUser->fresh()->detail());
    }
}