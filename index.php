<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
    <title>Setup</title>
</head>
<body>
    Setup Page.<br>
    user id : <input type="text" name="namae" size="40" maxlength="20"><br>
    user password : <input type="password" name="namae" size="40" maxlength="20"><br>

    <?php
        require_once "require.php";
        echo sazanami\TwoFactorAuth::generate();
    ?>
</body>
</html>