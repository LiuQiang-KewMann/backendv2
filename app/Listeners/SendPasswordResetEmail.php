<?php namespace App\Listeners;

use Mail;
use App\Events\PasswordResetTokenCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPasswordResetEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PasswordResetTokenCreated $event
     * @return void
     */
    public function handle(PasswordResetTokenCreated $event)
    {
        $passwordReset = $event->passwordReset;

        $email = $passwordReset->email;
        $token = $passwordReset->token;
        $url = env('KGB_PASSWORD_RESET_URL', 'http://localhost/appv2-client/#/password/reset/') . $token;

        // send email
        Mail::send('mail.reset-password', [
            'email' => $email,
            'url' => $url
        ], function ($message) use ($email) {
            $message
                ->to($email, 'KGB User')
                ->subject('Reset Password | 密码重置');
        });
    }
}