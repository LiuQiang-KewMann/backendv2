<?php namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetTokenCreated;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Validator;
use Auth;
use JWTAuth;
use Response;
use Request;
use Redirect;
use Hash;
use Log;
use App\User;
use App\Models\OneTimeKey;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    public function postLogin()
    {
        // get login credentials
        $credentials = Request::only('email', 'password');

        // try to authenticate
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return Response::json([
                    'msg' => 'invalid_credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return Response::json([
                'msg' => 'token_error'
            ], 500);
        }

        return compact('token');
    }


    public function postSsoLogin()
    {
        // params from client
        $ticket = Request::get('ticket');
        $accessTokenUrl = Request::get('access_token_url');
        $profileUrl = Request::get('profile_url');
        $attributes = Request::get('attributes');

        // ******************* START *****************************
        // init http http call
        $http = curl_init();
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        // get access token
        $url = $accessTokenUrl . $ticket;
        curl_setopt($http, CURLOPT_URL, $url);
        $accessToken = curl_exec($http);

        // get profile
        $url = $profileUrl . $accessToken;
        curl_setopt($http, CURLOPT_URL, $url);
        $userProfile = curl_exec($http);
        // ******************** END ******************************


        // close http call
        curl_close($http);

        // convert to array
        $userProfile = json_decode($userProfile, true);

        // get attributes
        $kgbAttributes = [];
        foreach ($attributes as $attr => $path) {
            array_set($kgbAttributes, $attr, array_get($userProfile, $path));
        }

        // if no email is obtained from userProfile
        if (!array_has($kgbAttributes, 'email')) {
            return Response::json(['msg' => 'Invalid user profile from SsoProvider'], 406);
        }

        $user = User::updateOrCreate([
            'email' => array_get($kgbAttributes, 'email')
        ], [
            'sso' => 1,
            'json_local' => json_encode($kgbAttributes),
            'json_remote' => json_encode($userProfile)
        ]);

        return ['token' => JWTAuth::fromUser($user)];
    }


    public function postSignup()
    {
        $email = Request::get('email');
        $password = Request::get('password', '1234');
        $firstName = Request::get('first_name');
        $lastName = Request::get('last_name');

        $user = User::firstOrNew(['email' => $email]);

        if ($user->exists) {
            // user found
            if ($user->status == User::STATUS_UNREGISTERED) {
                // user found with status = unregistered
                $user->update([
                    'password' => Hash::make($password),
                    'sso' => 0,
                    'status' => User::STATUS_ACTIVE,
                    'json' => json_encode([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]),
                ]);
            } else {
                // user found with status != unregistered
                return Response::json(['msg' => 'user_exist_already'], 406);
            }
        } else {
            // user not found
            // then create new
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($password),
                'sso' => 0,
                'status' => User::STATUS_ACTIVE,
                'json' => json_encode([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]),
            ]);
        }

        return ['token' => JWTAuth::fromUser($user)];
    }


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
        $user = JWTAuth::parseToken()->toUser();

        $oldPassword = Request::get('old_password');
        $newPassword = Request::get('new_password');

        if (!$oldPassword || !$newPassword) {
            return Response::json([
                'msg' => 'invalid_input'
            ], 406);
        }

        $userCurrentHashedPassword = $user->password;
        $userNewHashedPassword = Hash::make($newPassword);


        if (!Hash::check($oldPassword, $userCurrentHashedPassword)) {
            return Response::json([
                'msg' => 'wrong_old_passsword'
            ], 406);
        }

        $user->update(['password' => $userNewHashedPassword]);

        return [
            'item' => $user->fresh()->detail(false),
            'msg' => 'password_updated'
        ];
    }


    public function getPassword($email)
    {
        // looking for this user
        $user = User::firstOrNew(['email' => $email]);

        if ($user->exists) {
            // 1. delete previous token first
            PasswordReset::where('email', $email)->delete();

            // 2. create token - 10-digit-random-string
            $passwordReset = PasswordReset::create([
                'email' => $email,
                'token' => str_random(25),
                'created_at' => Carbon::now()
            ]);

            // 3. fire PasswordResetToken created event
            // Planned: use queue for this event
            Event::fire(new PasswordResetTokenCreated($passwordReset));

            return ['msg' => 'reset_email_sent'];

        } else {

            // uer does not not exist
            return Response::json(['msg' => 'account_not_exists'], 406);
        }
    }

    public function postPassword()
    {
        $token = Request::get('token');
        $password = Request::get('password');

        // find passwordReset by token
        $passwordReset = PasswordReset::firstOrNew(['token' => $token]);

        if ($passwordReset->exists) {
            // 1. find user
            $user = User::firstOrNew(['email' => $passwordReset->email]);

            // 2. update password
            $user->update([
                'password' => Hash::make($password)
            ]);

            // 3. delete token
            PasswordReset::where('token', $token)->delete();


            return [
                'token' => JWTAuth::fromUser($user),
                'msg' => 'password_reset'
            ];

        } else {
            // passwordReset does not not exist
            return Response::json(['msg' => 'invalid_token'], 406);
        }
    }
}