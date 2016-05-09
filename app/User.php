<?php namespace App;

use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimePlayerTrait;
use App\Traits\JsonTrait;
use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimePlayerTrait;

    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_UNREGISTERED = 'unregistered';

    protected $hidden = ['password', 'remember_token'];


    public function getPlayerIdAttribute()
    {
        return ('player_' . $this->id);
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail([
            'status',
            'email',
            'game_id'
        ]);

        array_set($array, 'image', env('KGB_DEFAULT_IMAGE_USER'));
        array_set($array, 'image_thumb', env('KGB_DEFAULT_IMAGE_USER'));

        return $array;
    }


    public function toArray()
    {
        return parent::brief();
    }
}