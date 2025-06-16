<?php
session_start();
date_default_timezone_set("America/Toronto");
require_once('database.php');

$user_name = $_POST['user_name'];
$password = $_POST['password'];

$query = "SELECT * FROM registrations WHERE userName = :userName";
$statement = $db->prepare($query);
$statement->bindValue(':userName', $user_name);
$statement->execute();
$row = $statement->fetch();
$statement->closeCursor();

if ($row) {   

    $now = new DateTime();
    $last_failed = new DateTime($row['last_failed_login']);
    
    $interval = $now->getTimeStamp() - $last_failed->getTimeStamp();  

    if ($row['failed_attempts'] >= 3 && $interval < 300) {
        $remaining = 300 - $interval;
        $_SESSION['login_error'] = "Account locked. Try again in " . ceil($remaining) . " seconds.";
        header("Location: login_form.php");
        exit;
    }

    if (password_verify($password, $row['password'])) {
        // Reset failed attempts on successful login
        $_SESSION["isLoggedIn"] = TRUE;
        $query = "UPDATE registrations SET failed_attempts = 0, last_failed_login = NULL WHERE userName = :userName";
        $statement = $db->prepare($query);
        $statement->bindValue(':userName', $user_name);
        $statement->execute();
        $statement->closeCursor();

        $_SESSION['user_id'] = $row['id'];
        header("Location: index.php");
        exit;
    } else {
        // Increment failed attempts
        $query = "UPDATE registrations 
                  SET failed_attempts = failed_attempts + 1, 
                      last_failed_login = NOW() 
                  WHERE userName = :userName";
        $statement = $db->prepare($query);
        $statement->bindValue(':userName', $user_name);
        $statement->execute();
        $statement->closeCursor();    

        $_SESSION['login_error'] = "Incorrect password.";
        header("Location: login_form.php");
        exit;
    }
} else {
    $_SESSION['login_error'] = "User not found.";
    header("Location: login_form.php");
    exit;
}
