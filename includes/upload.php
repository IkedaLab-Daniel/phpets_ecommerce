<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if (isset($_POST['upload'])) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/phpets/uploads/";
        $target_file = $target_dir . basename($_FILES["uploaded_file"]["name"]);

        if (move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $target_file)) {
            echo "✅ The file " . htmlspecialchars(basename($_FILES["uploaded_file"]["name"])) . " has been uploaded.";
        } else {
            echo "❌ Sorry, there was an error uploading your file.";
        }
    }
?>
