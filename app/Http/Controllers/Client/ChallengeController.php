<?php namespace App\Http\Controllers\Client;

use App\Models\Challenge;
use Response;
use Request;
use App\Http\Controllers\Controller;

class ChallengeController extends Controller
{
    public function getDetail($id)
    {
        $item = Challenge::find($id);

        // END
        return Response::json([
            'item' => $item->detail(),
            'instance' => $item->instance()
        ]);
    }
}