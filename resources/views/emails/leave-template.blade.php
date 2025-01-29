<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            border: 1px solid #ddd;
        }

        .email-content {
            text-align: left;
            color: #333;
            padding: 20px;
        }

        .logo {
            display: block;
            margin: 0 auto 20px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .details-table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
            padding: 15px;
            border-top: 1px solid #ddd;
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

                        {{-- HR Email: More concise --}}
                        @if($type !== 'hr-notification')
                            {{-- User Email: Personalized greeting --}}
                            <p>Bonjour <strong>{{ $fullName }}</strong>,</p>
                        @endif

                        {{-- Subject as header (only for user emails) --}}
                        @if($type !== 'hr-notification')
                            <h3>{{ $subject }}</h3>
                        @endif

                        {{-- Dynamic Content --}}
                        {!! $content !!}

                        {{-- Leave Details Table --}}
                        @if(isset($leave))
                            <h4>Détails du congé :</h4>
                            <table class="details-table">
                                <tr>
                                    <th>Début</th>
                                    <td>{{ \Carbon\Carbon::parse($leave?->start_date)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Fin</th>
                                    <td>{{ \Carbon\Carbon::parse($leave?->end_date)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Motif</th>
                                    <td>{{ ucfirst($leave?->type_of_leave) ?? 'Non spécifié' }}</td>
                                </tr>
                            </table>
                        @endif

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
