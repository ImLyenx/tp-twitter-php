<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="global.css">
    <title>Sign Up | Twitter</title>
</head>
<body>
    <form id="loginform" action="redirect.php" method="POST">

        <input type="hidden" name="form" value="signup">

        <label for="username">Username:</label>
        <span class="tip">Username may only contain letters, numbers, and underscores.</span>
        <input type="text" name="username" id="username" placeholder="Username">

        <label for="pwd">Password:</label>
        <input type="password" name="password" id="password" placeholder="Password">

        <button type="submit">Sign Up</button>
    </form>

</body>
</html>