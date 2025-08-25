<!DOCTYPE html>
<html>
<head>
    <title>Nouvelle demande de congé</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        table {
            width: 100%;
            height: 100%;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .email-content {
            text-align: left;
            color: #333;
        }

        .logo {
            display: block;
            margin: 0 auto 20px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table class="email-container" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="email-content">
                        <img src="https://gestion-conge.soft2do.de/images/logo-softtodo.png" alt="Company Logo"
                             class="logo" width="150">
                        <h3>Nouvelle demande de congé</h3>
                        <p>Bonjour,</p>
                        <p>Une nouvelle demande de congé a été soumise par <strong>{{ $firstName }}</strong>.</p>

                        @if(!empty($leaveDuration))
                            <p><strong>Durée :</strong> {{ $leaveDuration }} jour(s)</p>
                        @endif

                        @if(isset($startDate) && $startDate)
                            <p><strong>Date de début :</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</p>
                        @endif

                        @if(isset($endDate) && $endDate)
                            <p><strong>Date de fin :</strong> {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                        @endif

                        @if(isset($reason) && $reason)
                            <p><strong>Raison :</strong> {{ $reason }}</p>
                        @endif

                        <p>Merci de prendre en compte cette demande.</p>

                        <p>Cordialement,</p>
                        <p><strong>L'équipe GRH</strong></p>

                        <p class="footer">Cet e-mail est généré automatiquement. Veuillez ne pas y répondre.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
