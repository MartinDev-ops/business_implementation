<?php
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Service Hub Sign Up</title>
  <link rel="stylesheet" href="signup.css" />
  <style>
    .message {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: 500;
    }
    
    .error {
      background-color: #fee;
      color: #c33;
      border: 1px solid #fcc;
    }
    
    .success {
      background-color: #efe;
      color: #363;
      border: 1px solid #cfc;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <h1>Join Us Today!</h1>
      <p>Create an account to get started with Security Hub</p>
    </div>

    <div class="curve">
      <svg viewBox="0 0 100 100" preserveAspectRatio="none">
        <path d="M 0 0 C 25 50, 75 50, 100 100 L 100 0 Z" fill="#f4f4f4" />
      </svg>
    </div>

    <div class="right">
      <div class="form-box">
        <h2>Create Your Account</h2>
        
        <?php if ($error): ?>
          <div class="message error">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
          <div class="message success">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter Email" required />

          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter Password" required />

          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required />

          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" 
                 pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" required />

          <button type="submit" id="submit">Sign Up</button>
        </form>

        <p class="signin-text">
          Already have an account? <a href="index.php">Sign in</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>