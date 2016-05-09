<?php namespace App\Models;

use App\Traits\AdminGameUserTeamRoleTrait;
use App\Traits\JsonTrait;
use App\Traits\PlaylyfeTrait;

class GameUserTeamRole extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use AdminGameUserTeamRoleTrait;


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail([
            'role'
        ]);

        $array = array_merge($this->gameUser->detail(), $array);

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'email',
            'nickname',
            'db_id',
            'image',
            'image_thumb'
        ]);
    }


    public static function boot()
    {
        parent::boot();

        GameUserTeamRole::creating(function (self $gameUserTeamRole) {
            GameUserTeamRole::applyMemberRolesInPlaylyfe($gameUserTeamRole->gameUser, $gameUserTeamRole->team, [
                $gameUserTeamRole->role => true
            ]);
        });

        GameUserTeamRole::deleting(function (self $gameUserTeamRole) {
            // if current role is in

            GameUserTeamRole::applyMemberRolesInPlaylyfe($gameUserTeamRole->gameUser, $gameUserTeamRole->team, [
                $gameUserTeamRole->role => false
            ]);
        });
    }


    public function gameUser()
    {
        return $this->belongsTo('App\Models\GameUser');
    }


    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }
}