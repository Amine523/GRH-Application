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

        $this->setEmailContent();
    }

    private function setEmailContent()
    {
        $leaveTypeLabel = match ($this->leave?->type_of_leave) {
            'vacation' => 'congé (vacation)',
            'sick' => 'congé (maladie)',
            'authorisation' => 'autorisation d\'absence',
            'halfday' => 'demi-journée',
            default => 'congé'
        };

        switch ($this->type) {
            case 'user-approved':
                $this->subject = "Votre demande de $leaveTypeLabel a été acceptée";
                $this->content = "<p>Félicitations <strong>{$this->fullName}</strong> ! Votre demande de <strong>$leaveTypeLabel</strong> a été approuvée.</p>";
                break;

            case 'user-rejected':
                $this->subject = "Votre demande de $leaveTypeLabel a été refusée";
                $this->content = "<p>Votre demande de <strong>$leaveTypeLabel</strong> a été refusée pour la raison suivante :
                                  <strong>" . ($this->leave?->leaveReason ?? 'Non spécifiée') . "</strong>.</p>";
                break;

            case 'hr-notification':
                $this->subject = "Nouvelle demande de $leaveTypeLabel de {$this->fullName}";
                $this->content = "<p>Un employé a soumis une demande de <strong>$leaveTypeLabel</strong> :</p>
                                  <p><strong>Employé :</strong> {$this->fullName}</p>";
                break;

            default:
                $this->subject = "Notification de $leaveTypeLabel";
                $this->content = "<p>Informations sur le <strong>$leaveTypeLabel</strong> en cours.</p>";
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
