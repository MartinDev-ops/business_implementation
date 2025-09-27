<?php
require_once "config.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    
    // Validate phone number length
    if (strlen($phone) != 10 || !is_numeric($phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } else {
        // Handle image upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
            $filename = "user_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            }
        }
      }
    
   
    $updateQuery = "UPDATE users SET username = :username, phone = :phone";
    if (isset($profile_picture)) {
        $updateQuery .= ", profile_picture = :profile_picture";
    }
    $updateQuery .= " WHERE id = :user_id";
    
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(":username", $username);
    $updateStmt->bindParam(":phone", $phone);
    $updateStmt->bindParam(":user_id", $_SESSION['user_id']);
    
    if (isset($profile_picture)) {
        $updateStmt->bindParam(":profile_picture", $profile_picture);
    }
    
    if ($updateStmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Profile</title>
  <link rel="stylesheet" href="profile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
  <div class="container">
    
    <div class="left">
      <h1>Your Profile</h1>
      <p>Manage your personal details and keep your account up-to-date.</p>
    </div>

    
    <div class="curve">
      <svg viewBox="0 0 100 100" preserveAspectRatio="none">
        <path d="M 0 0 C 25 50, 75 50, 100 100 L 100 0 Z" fill="#f4f4f4" />
      </svg>
    </div>

    
    <div class="right">
      <div class="form-box profile-box">
        
        <div class="home-icon">
          <a href="dashboard.php" title="Go to Dashboard">
            <i class="fa-solid fa-house"></i>
          </a>
        </div>

        <?php if (isset($success)): ?>
          <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $success; ?>
          </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
          <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="profile.php" enctype="multipart/form-data">
          <!-- Profile Picture -->
          <div class="profile-pic">
            <img id="userImage" src="<?php echo $user['profile_picture'] ?: 'default-avatar.png'; ?>" alt="Profile Picture">
            <label for="imageUpload" class="camera-icon">
              <i class="fa-solid fa-camera"></i>
            </label>
            <input type="file" id="imageUpload" name="profile_picture" accept="image/*" hidden>
          </div>

          <!-- Username -->
          <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
          </div>

          <!-- Phone -->
          <div class="field">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                   pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits">
          </div>

          <!-- Email (not editable) -->
          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
          </div>

          <!-- Save Changes -->
          <button type="submit" id="saveChanges">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    
    document.getElementById('imageUpload').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('userImage').src = e.target.result;
        }
        reader.readAsDataURL(file);
      }
    });

    
    document.getElementById('phone').addEventListener('input', function(e) {
      
      this.value = this.value.replace(/\D/g, '');
      
      
      if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
      }
    });
  </script>
</body>
</html>