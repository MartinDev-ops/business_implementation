<?php
session_start();

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Service Hub Login</title>
  <link rel="stylesheet" href="index.css">
  <style>
    .success-message {
      background-color: #efe;
      color: #363;
      border: 1px solid #cfc;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container">
    
    <div class="left">
     <h1>Welcome to IronGuard Security Hub</h1>
     <p>Your trusted gateway to reliable security solutions. Sign in to connect with professionals, and safeguard what matters most.</p>
    </div>

    
    <div class="curve">
      <svg viewBox="0 0 100 100" preserveAspectRatio="none">
        <path d="M 0 0 C 25 50, 75 50, 100 100 L 100 0 Z" fill="#f4f4f4" />
      </svg>
    </div>

    
    <div class="right">
      <div class="form-box">
        <h2>Sign In</h2>
        
        <?php if ($success): ?>
          <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="login.php">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter Email" required />

          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your Password" required />

          <button type="submit" id="login">Sign In</button>
        </form>
        <p class="signup-text">
          Don't have an account? <a href="signup.php">Sign up</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>