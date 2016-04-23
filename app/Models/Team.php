<?php namespace App\Models;

use App\Traits\AdminTeamTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;

class Team extends BaseModel
{
    use PlaylyfeTrait;
    use AdminTeamTrait;
    use JsonTrait;

    public static function boot()
    {
        parent::boot();

        Team::creating(function (Team $team) {
            // if remote_id is not there, which means it is local creation
            // not sync creation
            if (!$team->remote_id) {
                $team->createRemoteInstance();
            }
        });

        Team::deleting(function (Team $team) {
            // if remote_id is not there, which means it is sync deletion
            // not local deletion
            if ($team->remote_id) {
                $team->deleteRemoteInstanceIfThere();
            }
        });
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail($additionalAttributes);

        $array = array_merge([
            'image' => env('KGB_DEFAULT_IMAGE_TEAM'),
            'image_thumb' => env('KGB_DEFAULT_IMAGE_TEAM')
        ], $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }


    public function getRolesAttribute()
    {
        $roles = $this->jsonGet('roles');

        $rolesObject = [];

        foreach ($roles as $role) {
            array_push($rolesObject, [
                'id' => $role,
                'name' => $role
            ]);
        }

        return $rolesObject;
    }
}