<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClubAdminCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your Club Admin Account')
            ->view('emails.club_admin_credentials')
            ->with([
                'email' => $this->email,
                'password' => $this->password,
            ]);
    }
}
