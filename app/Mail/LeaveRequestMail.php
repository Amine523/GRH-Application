<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $messageType;

    public function __construct($firstName, $messageType, $leaveReason = null)
    {
        $this->firstName = $firstName;
        $this->messageType = $messageType;
        $this->leaveReason = $leaveReason;
    }

    public function build()
    {

        return match ($this->messageType) {
            'approved-extra' => $this->view('emails.leave-mail')
                ->subject('Information sur le solde de congé et sa clôture')
                ->with([
                    'firstName' => $this->firstName,
                ]),
            'approved' => $this->view('emails.leave-accepted')
                ->subject('Demande de congé Apprové')
                ->with([
                    'firstName' => $this->firstName,
                ]),

            'rejected' => $this->view('emails.leave-rejected')
                ->subject('Demande de congé refusée')
                ->with([
                    'firstName' => $this->firstName,
                    'reason' => $this->leaveReason,
                ]),
            'revoke' => $this->view('emails.leave-rejected')
                ->subject('Demande de congé révoqué')
                ->with([
                    'firstName' => $this->firstName,
                    'reason' => $this->leaveReason,
                ]),
            'approved-authorisation' => $this->view('emails.leave-authorisation-approved')
                ->subject('Demande de authorisation accepteé')
                ->with([
                    'firstName' => $this->firstName,
                ]),
            'rejected-authorisation' => $this->view('emails.leave-authorisation-rejected')
                ->subject('Demande de authorisation refusée')
                ->with([
                    'firstName' => $this->firstName,
                ]),

            default => $this->view('emails.leave-default')
                ->subject('Information sur le solde de congé et sa clôture')
                ->with([
                    'firstName' => $this->firstName,
                ]),
        };
    }
}
