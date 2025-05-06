<?php 
    $view_mode = $_COOKIE['view'];
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <link rel="stylesheet" href="/phpets/assets/css/view_modal.css" />
    </head>

    <body>
        <form action="/phpets/includes/view-modal-logic.php" method="GET"  class="view-modal cool-btn <?php echo $view_mode; ?>" href="/phpets/buyer/buyer.php">            
            <button type="submit" name="skibdi" class="<?php echo $view_mode; ?>">
                <?php if ($view_mode == 'light'): ?>
                    <img class="<?php echo $view_mode; ?>" src="/phpets/assets/images/light.svg" > 
                <?php else: ?>
                    <img class="<?php echo $view_mode; ?>" src="/phpets/assets/images/moon.svg" > 
                <?php endif ?>
                
            </button>
        </form>          
    </body>
</html>