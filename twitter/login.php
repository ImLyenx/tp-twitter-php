<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="global.css">
    <title>Document</title>
</head>
<body>
    <form id="loginform" action="redirect.php" method="POST">
        <input type="hidden" name="form" value="login">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" placeholder="Username">
        <label for="pwd">Password:</label>
        <input type="password" name="password" id="password" placeholder="Password">
        <button type="submit">Log In</button>
        <button onclick="alert('Good luck')" type="button" style="margin-top: 10px;">Lost your password ?</button>
    </form>
</body>
</html>