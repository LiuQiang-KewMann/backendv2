<?php namespace App\Models;

use App\Traits\AdminGameUserTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeGameUserTrait;

class GameUser extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use AdminGameUserTrait;
    use RuntimeGameUserTrait;

    const ROLE_DESIGNER = 'designer';
    const ROLE_ADMIN = 'admin';
    const ROLE_PLAYER = 'player';

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $gameUser) {
            $gameUser->addPlayerInEngine();
        });


        self::deleting(function (self $gameUser) {
            $gameUser->removePlayerInEngine();
        });
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail(['game_id']);

        // merge with user detail while gameUser will overwrite user if same key exists
        $array = array_merge($this->user->detail(), $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }


    public function getProfileAttribute()
    {
        return parent::brief([
            'email',
            'image',
            'image_thumb',
            'first_name',
            'last_name',
            'nickname',
            'db_id'
        ]);
    }


    public function getScoresAttribute()
    {
        $scores = $this->jsonGet('scores', []);

        foreach ($scores as &$score) {
            Metric::localizeScoreMetric($this->game_id, $score);
        }

        return $scores;
    }


    public function getTeamsAttribute()
    {
        // Todo
    }


    public function getChangedScoresAttribute()
    {
        $oldScores = $this->jsonGet('old_scores', []);
        $newScores = $this->jsonGet('scores', []);

        $changedScores = [];
        // iterate each new scores and compare with old values
        foreach ($newScores as $newScore) {
            $metricId = array_get($newScore, 'metric.id');

            // searching for oldScores with same metricId
            $matchedOldScores = array_where($oldScores, function ($key, $value) use ($metricId) {
                return (array_get($value, 'metric.id') == $metricId);
            });
            $matchedOldScores = array_values($matchedOldScores);

            if (sizeof($matchedOldScores)) {
                // if same metric exists in oldScores
                $oldValue = array_get($matchedOldScores, '0.value');
                $newValue = array_get($newScore, 'value');


                // get metric type
                $type = array_get($newScore, 'metric.type');

                // check values for different type and add newScore to changedScores array
                if ($type == Metric::TYPE_POINT) {
                    // point type: check value ...........................................
                    if ($oldValue != $newValue) {
                        array_push($changedScores, $newScore);
                    }


                } else if ($type == Metric::TYPE_SET) {
                    // set type, need to iterate value as value is array .................
                    // and check valueItem.name and count
                    foreach ($newValue as $key => $newValueItem) {
                        $newValueItemName = array_get($newValueItem, 'name');
                        $newValueItemCount = array_get($newValueItem, 'count');

                        $matchedOldValues = array_where($oldValue, function ($key, $oldValue) use ($newValueItemName) {
                            return (array_get($oldValue, 'name') == $newValueItemName);
                        });

                        if (sizeof($matchedOldValues)) {
                            // if same item found in oldValue
                            $oldValueItemCount = array_get($matchedOldValues, '0.count');

                            // if count is the same, then forgot this in newValue
                            if ($oldValueItemCount == $newValueItemCount) {
                                array_forget($newValue, $key);
                            }
                        } else {
                            // if no item found in oldValue, which means this is new item
                        }
                    }

                    // if newValue is not empty, then update to score and push to changedScores
                    if (sizeof($newValue)) {
                        array_set($newScore, 'value', $newValue);
                        array_push($changedScores, $newScore);
                    }


                } else if ($type == Metric::TYPE_STATE) {
                    // state type: check value.name .......................................
                    if (array_get($oldValue, 'name') != array_get($newValue, 'name')) {
                        array_push($changedScores, $newScore);
                    }
                }

            } else {
                // if same metric does not exist in oldScores
                // which implies score changed
                array_push($changedScores, $newScore);
            }
        }

        return $changedScores;
    }
}