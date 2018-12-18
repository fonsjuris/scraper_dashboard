<?php
if (isset($_POST['password']) && $_POST['password'] == 'MYPASS') {
    setcookie("password", 'MYPASS', strtotime('+30 days'));
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password protected</title>
</head>
<body>
<div style="text-align:center;margin-top:50px;">
    You must enter the password to view this content.
    <form method="POST">
        <input type="password" name="password">
    </form>
</div>
</body>
</html>