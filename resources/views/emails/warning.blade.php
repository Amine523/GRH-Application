<!DOCTYPE html>
<html>
<head>
    <title>Mise à jour de votre solde de congé</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            height: 100%;
            background-color: #f4f4f4;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }

        .email-content {
            text-align: center;
        }

        .logo {
            margin-bottom: 20px;
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
                        <!-- Logo section -->
                        <img src="https://gestion-conge.soft2do.de/images/logo-softtodo.png" alt="Company Logo"
                             class="logo" width="150">

                        <!-- Email body -->
                        <h4>Bonjour {{ $firstName }},</h4>
                        <p>Je vous écris pour vous informer que votre comportement récent, notamment vos retards
                            fréquents et vos absences non justifiées au bureau sans fournir d'informations préalables,
                            est inacceptable. Ces manquements perturbent non seulement le bon fonctionnement de
                            l'équipe, mais démontrent également un manque de professionnalisme.

                            Je tiens à vous rappeler que chaque absence ou retard doit être signalé et justifié à
                            l'avance, sauf en cas de circonstances exceptionnelles. Ce non-respect des règles de
                            l'entreprise ne peut pas être toléré, et je vous demande de rectifier cette situation
                            immédiatement.

                            Veuillez noter que si une telle situation se reproduit à l'avenir, des mesures
                            disciplinaires plus sévères seront prises, pouvant aller jusqu'à votre licenciement.

                            Je compte sur vous pour prendre cette remarque avec sérieux et pour vous conformer aux
                            attentes de l'entreprise.</p> <br>
                        <p>Cordialement.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
