<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SendMailToNewUser extends Mailable
{
    use Queueable, SerializesModels;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param Request $user
     */
    public function __construct(Request $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user.register', ['user' => $this->user]);
    }
}
