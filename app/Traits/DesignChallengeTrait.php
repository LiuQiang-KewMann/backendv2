<?php namespace App\Traits;

use DB;

trait DesignChallengeTrait
{
    static public function proposedNumber($gameId)
    {
        $numbersQueryResult = DB::table('challenges')
            ->where('game_id', $gameId)
            ->orderBy('number')
            ->select(['number'])
            ->get();
        $numbersArray = json_decode(json_encode($numbersQueryResult), true);
        $numbers = array_column($numbersArray, 'number');

        // if no records found, then propose 1
        if (sizeof($numbers) == 0) return 1;

        // if last number == size, then return last number + 1
        if (last($numbers) == sizeof($numbers)) return last($numbers) + 1;

        // else, there will be some gap, find the first gap and return it
        foreach ($numbers as $key => $value) {
            if ($key != ($value - 1)) {
                return $key + 1;
            }
        }
    }
}