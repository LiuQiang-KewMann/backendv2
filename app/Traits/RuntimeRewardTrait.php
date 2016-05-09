<?php namespace App\Traits;

use App\Models\GameUser;
use App\Models\RewardDispatcher;

trait RuntimeRewardTrait
{
    public function giveOutRewards()
    {
        // re-arrange rewards and group by receiver
        $receiverRewards = [];
        foreach ($this->rewards as $reward) {
            // get dispatcher
            $dispatcher = $reward->dispatcher;

            /* --------------------------------------------
             * checking
             * --------------------------------------------
             */
            // the dispatcher self_class must be same as current object class
            // if it is not the same, then skip current one and go to next loop
            if ($dispatcher->self_class != self::class) continue;

            // get attributes
            $leftAttr = $dispatcher->jsonGet('left');
            $receiverAttr = $dispatcher->jsonGet('receiver');

            // there MUST NOT be '(' or ')' in attributes
            // if there is any, then skip current one and go to next loop
            // this is to get avoid of code injection
            $joinedString = $leftAttr . $receiverAttr;
            $forbiddenRegExpr = '/(\(|\))/';
            if (preg_match($forbiddenRegExpr, $joinedString)) continue;

            /* --------------------------------------------
             * main
             * --------------------------------------------
             */
            $operator = $dispatcher->jsonGet('operator', RewardDispatcher::OPERATOR_FREE);
            $left = $this->$leftAttr;
            $right = $dispatcher->jsonGet('right');

            $result = self::getResult($left, $right, $operator);
            $receiver = $this->$receiverAttr ?: null;

            if ($result && $receiver) {
                // if result is true, then add this reward under corresponding receiver
                if (!array_has($receiverRewards, $receiver->id)) {
                    array_set($receiverRewards, $receiver->id, []);
                }

                array_push($receiverRewards[$receiver->id], $reward->jsonGet('reward'));
            };
        }

        // give out consolidated rewards for each receiver
        foreach ($receiverRewards as $receiverId => $rewards) {
            $gameUser = GameUser::firstOrNew([
                'game_id' => $this->game->id,
                'user_id' => $receiverId,
                'role' => GameUser::ROLE_PLAYER
            ]);

            if ($gameUser->exists) {
                $res = $this->conn()->patch('/admin/players/' . $gameUser->user->playerId . '/scores', [], [
                    'rewards' => $rewards
                ]);

                $scores = array_get($res, 'scores', []);
                $gameUser->updateScores($scores);
            }
        }
        
        return $receiverRewards;
    }


    /*
     * return result based on comparison of left and right
     */
    private static function getResult($left, $right, $operator)
    {
        switch ($operator) {
            // equal
            case RewardDispatcher::OPERATOR_EQ:
                $result = ($left == $right);
                break;

            // not equal
            case RewardDispatcher::OPERATOR_NE:
                $result = ($left != $right);
                break;

            // not null
            case RewardDispatcher::OPERATOR_NOTNULL:
                $result = ($left != null);
                break;

            // default is free
            default:
                $result = true;
        }

        return $result;
    }
}