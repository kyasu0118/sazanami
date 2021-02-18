<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
    <title>Auth</title>
</head>
<body>
    Auth Page.<br>

    <form action="auth.php" method="POST">
        Token : <input type="text" name="token" size="40" maxlength="8"><br>
        <input type="submit">
    </form>

    <?php
        require_once "require.php";
        echo sazanami\TwoFactorAuth::isValid($_POST["token"]) ? "true" : "false";
    ?>
</body>
</html>