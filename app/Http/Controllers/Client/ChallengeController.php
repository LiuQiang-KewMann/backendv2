<?php namespace App\Http\Controllers\Client;

use App\Models\Challenge;
use App\Http\Controllers\Controller;

class ChallengeController extends Controller
{
    public function getDetail($id)
    {
        $item = Challenge::find($id);
        
        return [
            'item' => array_merge($item->detail(), ['components' => $item->components]),
            'instance' => $item->instance()
        ];
    }
}