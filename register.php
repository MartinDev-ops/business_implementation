<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $username = $_POST['username'];
    $phone = $_POST['phone'];

    
    if (strlen($phone) != 10 || !is_numeric($phone)) {
        $_SESSION['error'] = "Phone number must be exactly 10 digits.";
        header("Location: signup.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

   
    $checkQuery = "SELECT id FROM users WHERE email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":email", $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = "An account with this email address already exists. Please use a different email or try logging in.";
        header("Location: signup.php");
        exit();
    }

    
    $query = "INSERT INTO users (email, password, username, phone) VALUES (:email, :password, :username, :phone)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":phone", $phone);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Welcome to Security Hub. You can now sign in to your account.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "We encountered an issue creating your account. Please try again or contact support if the problem persists.";
        header("Location: signup.php");
        exit();
    }
} else {
    
    header("Location: signup.php");
    exit();
}
?>