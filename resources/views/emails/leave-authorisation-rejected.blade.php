<!DOCTYPE html>
<html>
<head>
    <title>Information sur le solde de congé et sa clôture</title>
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
                        <img src="https://gestion-conge.soft2do.de/images/logo-softtodo.png" alt="Company Logo" class="logo" width="150">

                        <!-- Email body -->
                        <h4>Bonjour {{ $firstName }},</h4>
                        <p>Suite à votre demande de autorisation, nous vous informons que celle-ci a été refusée.</p>
                        <p>Si vous avez des questions ou besoin de plus d'informations, n'hésitez pas à nous contacter.</p>
                        <p>Cordialement.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
