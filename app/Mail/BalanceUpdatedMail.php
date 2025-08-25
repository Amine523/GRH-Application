<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BalanceUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $newBalance;

    public function __construct($user, $newBalance)
    {
        $this->user = $user;
        $this->newBalance = $newBalance;
    }

    public function build()
    {
        return $this->subject('Your Balance Has Been Updated')
            ->view('emails.balance-updated')
            ->with([
                'userName' => $this->user->profile ? $this->user->profile->first_name : $this->user->name,
                'newBalance' => $this->newBalance,
            ]);
    }
}
