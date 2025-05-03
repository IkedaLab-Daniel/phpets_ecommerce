<?php 
    include ('./includes/header.php');
    include ('./includes/db_connect.php');
    session_start();
    
    $category = $_GET['q'];
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Category - <?php echo ucfirst($category) ?></title>
    </head>

    <body style="margin-top: 7rem;">
         <h1><?php echo "$category" ?></h1>    
    </body>
</html>

<?php 
    include ('./includes/error_catch.php');
?>