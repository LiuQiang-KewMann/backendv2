<?php namespace App\Http\Controllers\Client;

use Hash;
use Request;
use Response;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getDetail()
    {
        return ['item' => $this->user->detail()];
    }


    public function postUpdate()
    {
        $inputArray = Request::only(['first_name', 'last_name']);

        $this->user->jsonUpdate($inputArray);

        return ['item' => $this->user->detail()];
    }


    public function postUpdatePassword()
    {
        $oldPassword = Request::get('old_password');
        $newPassword = Request::get('new_password');

        if (!$oldPassword || !$newPassword) {
            return Response::json(['msg' => 'invalid_input'], 406);
        }

        $userCurrentHashedPassword = $this->user->password;
        $userNewHashedPassword = Hash::make($newPassword);


        if (!Hash::check($oldPassword, $userCurrentHashedPassword)) {
            return Response::json(['msg' => 'wrong_old_passsword'], 406);
        }

        $this->user->update(['password' => $userNewHashedPassword]);

        return [
            'item' => $this->user->detail(),
            'msg' => 'password_updated'
        ];
    }
}