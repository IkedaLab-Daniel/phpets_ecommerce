<?php
    echo "<div id='toast-data' data-message=' ❌ GET OUT: Account Banned!' data-type='banned' data-img='/phpets/assets/images/getout.png'></div>";
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/index.css" >
        <title>Document</title>
    </head>
    <body>
        <h1>U R banned</h1>
        <h3>💥SESSION DESTROYED💥</h3>
        <a href="../login.php">Log In</a><br/>
        <a href="../signup.php">Sign Up</a>

        <div id="toast-container"></div>
        <script src="../assets/js/toast.js"></script>
    </body>

    
</html>