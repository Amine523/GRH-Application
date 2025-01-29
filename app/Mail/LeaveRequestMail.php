<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;
    public $type;
    public $leave;
    public $subject;
    public $content;

    public function __construct(string $fullName, string $type, $leave = null)
    {
        $this->fullName = $fullName;
        $this->type = $type;
        $this->leave = $leave;

        // Set email subject and content dynamically
        $this->setEmailContent();
    }

    private function setEmailContent()
    {
        switch ($this->type) {
            case 'user-approved':
                $this->subject = 'Votre demande de congé a été acceptée';
                $this->content = '<p>Félicitations ! Votre demande de congé a été approuvée.</p>';
                break;

            case 'user-rejected':
                $this->subject = 'Votre demande de congé a été refusée';
                $this->content = '<p>Votre demande de congé a été refusée pour la raison suivante :
                                  <strong>' . ($this->leave?->leaveReason ?? 'Non spécifiée') . '</strong>.</p>';
                break;

            case 'hr-notification':
                $this->subject = 'Nouvelle demande de congé soumise';
                $this->content = '<p>Un employé a soumis une demande de congé :</p>
                                  <p><strong>Employé :</strong> ' . $this->fullName . '</p>';
                break;

            default:
                $this->subject = 'Notification de congé';
                $this->content = '<p>Informations sur le congé en cours.</p>';
        }
    }

    public function build()
    {
        return $this->view('emails.leave-template')
            ->subject($this->subject)
            ->with([
                'fullName' => $this->fullName,
                'content' => $this->content
            ]);
    }
}
