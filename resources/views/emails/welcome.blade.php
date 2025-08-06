<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #007BFF;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .header {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        Bienvenue à Softtodo
    </div>
    <div class="content">
        <img src="https://gestion-conge.soft2do.de/images/logo-softtodo.png" alt="Company Logo" class="logo" width="120">
        <h4>Bonjour {{ $user->profile->first_name }} {{ $user->profile->last_name }},</h4>
        <p>
            Nous sommes ravis de vous accueillir au sein de l’équipe de Softtodo. Toute l’équipe se réjouit de collaborer avec vous et est convaincue que vos compétences et votre enthousiasme apporteront une grande valeur à nos projets.
        </p>
        <p>
            Vos identifiants de connexion : <br>
            <strong>Email :</strong> {{ $user->email }} <br>
            <strong>Mot de passe :</strong> password
        </p>
        <a href="https://gestion-conge.soft2do.de/login" class="button">Accéder à votre compte</a>
        <p>Cordialement,<br>L'équipe Softtodo</p>
    </div>
    <div class="footer">
        © 2025 Softtodo. Tous droits réservés.
    </div>
</div>
</body>
</html>
