<?php namespace App\Traits;

/*
 * GameUser got $game attribute, hence can use $this->conn() directly
 */
trait RuntimeGameUserTrait
{
    public function updateScores($newScore)
    {
        $jsonArray = $this->jsonArray();

        $oldScores = array_get($jsonArray, 'scores');

        array_set($jsonArray, 'old_scores', $oldScores);
        array_set($jsonArray, 'scores', $newScore);
        
        $this->jsonUpdate($jsonArray);
    }
}