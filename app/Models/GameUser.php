<?php namespace App\Models;

use App\Traits\AdminGameUserTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;

class GameUser extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use AdminGameUserTrait;

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


    public function scores()
    {
        $remoteJson = $this->jsonArray('remote');
        $scores = array_get($remoteJson, 'profile.scores', []);

        // add metric image, description
        foreach ($scores as &$score) {
            $metric = Metric::firstOrNew([
                'game_code' => $this->game->code,
                'code' => array_get($score, 'metric.id')
            ]);

            if ($metric->exists) {
                $metricDetail = $metric->detail();

                array_set($score, 'metric.name', array_get($metricDetail, 'mixed.name'));
                array_set($score, 'metric.subheader', array_get($metricDetail, 'mixed.subheader'));
                array_set($score, 'metric.image', array_get($metricDetail, 'mixed.image'));
                array_set($score, 'metric.description', array_get($metricDetail, 'mixed.description'));

                if (array_get($metricDetail, 'mixed.type') == 'state') {
                    // if current metric is of type - state
                    // ...
                    $remoteName = array_get($score, 'value.name');
                    $metricItem = $metric->getItemByRemoteName($remoteName);
                    $metricItemDetail = $metricItem->detail();

                    array_set($score, 'value.name', array_get($metricItemDetail, 'mixed.name'));
                    array_set($score, 'value.image', array_get($metricItemDetail, 'mixed.image'));
                    array_set($score, 'value.description', array_get($metricItemDetail, 'mixed.description'));

                } else if (array_get($metricDetail, 'mixed.type') == 'set') {
                    // if current metric is of type - set
                    // ...
                    $nbrOfItems = sizeof(array_get($score, 'value'));
                    for ($i = 0; $i < $nbrOfItems; $i++) {
                        $remoteName = array_get($score, "value.$i.name");
                        $metricItem = $metric->getItemByRemoteName($remoteName);
                        $metricItemDetail = $metricItem->detail();

                        array_set($score, "value.$i.name", array_get($metricItemDetail, 'mixed.name'));
                        array_set($score, "value.$i.image", array_get($metricItemDetail, 'mixed.image'));
                        array_set($score, "value.$i.description", array_get($metricItemDetail, 'mixed.description'));
                    }
                }
            }
        }

        // END
        return $scores;
    }


    public function teams()
    {
        // team
        $gameUserTeamRoles = GameUserTeamRole::where([
            'game_user_id' => $this->id
        ])->get();

        $teams = [];
        foreach ($gameUserTeamRoles as $gameUserTeamRole) {
            $team = $gameUserTeamRole->team;
            $role = $gameUserTeamRole->role;

            array_push($teams, ['team' => $team, 'role' => $role]);
        }

        // END
        return $teams;
    }

    public function detail($additionalAttributes = [])
    {
        $array = parent::detail(['game_id']);
        $userArray = $this->user->toArray();

        // merge with userArray
        // while gameUser will overwrite userArray if same key exists
        $array = array_merge(
            $userArray,
            $array
        );

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }
}