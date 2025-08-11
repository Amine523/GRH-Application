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
    public $recipientName;

    public function __construct(string $fullName, string $type, $leave = null, string $recipientName = null)
    {
        $this->fullName = $fullName;
        $this->type = $type;
        $this->leave = $leave;
        $this->recipientName = $recipientName;

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
                
            case 'pm-notification':
                $this->subject = "Nouvelle demande de $leaveTypeLabel - {$this->fullName}";
                $this->content = "<p>Une nouvelle demande de <strong>$leaveTypeLabel</strong> a été soumise par <strong>{$this->fullName}</strong>.</p>
                                <p>Détails de la demande :</p>
                                <ul>
                                    <li>Type : $leaveTypeLabel</li>
                                    <li>Date de début : {$this->leave?->start_day}</li>
                                    <li>Date de fin : {$this->leave?->end_day}</li>
                                    " . ($this->leave?->type_of_leave === 'authorisation' ? "<li>Heures : {$this->leave?->authorization_hour}h</li>" : "") . "
                                </ul>";
                break;

            case 'admin-pending-approval':
                $recipientName = $this->recipientName ?? 'Administrateur';
                $this->subject = "Nouvelle demande de $leaveTypeLabel nécessitant une approbation";
                $this->content = "<p>Bonjour <strong>$recipientName</strong>,</p>";
                $this->content .= "<p>Une nouvelle demande de <strong>$leaveTypeLabel</strong> a été soumise par <strong>{$this->fullName}</strong> et nécessite votre attention.</p>";
                $this->content .= "<p><strong>Détails de la demande :</strong></p>";
                $this->content .= "<ul>";
                $this->content .= "<li><strong>Type :</strong> " . ucfirst($this->leave->type_of_leave) . "</li>";
                $this->content .= "<li><strong>Date de début :</strong> " . $this->leave->start_day->format('d/m/Y') . "</li>";
                if ($this->leave->end_day) {
                    $this->content .= "<li><strong>Date de fin :</strong> " . $this->leave->end_day->format('d/m/Y') . "</li>";
                }
                if ($this->leave->start_time) {
                    $this->content .= "<li><strong>Heure de début :</strong> " . $this->leave->start_time . "</li>";
                }
                $this->content .= "<li><strong>Statut :</strong> En attente d'approbation</li>";
                $this->content .= "</ul>";
                $this->content .= "<p>Veuillez vous connecter à l'application pour traiter cette demande.</p>";
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
