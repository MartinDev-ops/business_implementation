<?php
require_once "config.php";

// Check if user is logged in
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

// Handle contact form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $category = $_POST['category'];
    
    // Validate message length
    if (strlen($message) < 50) {
        $error = "Message must be at least 50 characters long to provide sufficient details.";
    } else {
        // Additional security: sanitize inputs
        $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        
        // Here you would typically send an email or save to database
        // For now, we'll just show a success message
        $success = "Your message has been sent successfully! We'll get back to you within 24 hours.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us - Security Hub</title>
  <link rel="stylesheet" href="contact.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
  <div class="container">
    <!-- Left Intro Panel -->
    <div class="left">
      <h1>Get In Touch</h1>
      <p>Have questions or need assistance? Our support team is here to help you with any inquiries about our security services.</p>
      <div class="contact-info">
        <div class="contact-item">
          <i class="fa-solid fa-phone"></i>
          <span>+27 (761) 01-1718</span>
        </div>
        <div class="contact-item">
          <i class="fa-solid fa-envelope"></i>
          <span>support@securityhub.com</span>
        </div>
        <div class="contact-item">
          <i class="fa-solid fa-clock"></i>
          <span>24/7 Support Available</span>
        </div>
      </div>
    </div>

    <!-- Curve Divider -->
    <div class="curve">
      <svg viewBox="0 0 100 100" preserveAspectRatio="none">
        <path d="M 0 0 C 25 50, 75 50, 100 100 L 100 0 Z" fill="#f4f4f4" />
      </svg>
    </div>

    <!-- Right Contact Panel -->
    <div class="right">
      <div class="form-box contact-box">
        <!-- Home Icon -->
        <div class="home-icon">
          <a href="dashboard.php" title="Go to Dashboard">
            <i class="fa-solid fa-house"></i>
          </a>
        </div>

        <?php if (isset($success)): ?>
          <div class="success-message">
            <?php echo $success; ?>
          </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
          <div class="error-message">
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <h2>Contact Us</h2>
        <form method="POST" action="contact.php">
          <!-- Prefilled User Info -->
          <div class="user-info">
            <p><strong>From:</strong> <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</p>
          </div>

          <!-- Category -->
          <div class="field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <option value="">Select a category</option>
              <option value="general">General Inquiry</option>
              <option value="booking">Booking Issue</option>
              <option value="technical">Technical Support</option>
              <option value="billing">Billing Question</option>
              <option value="feedback">Feedback</option>
            </select>
          </div>

          <!-- Subject -->
          <div class="field">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Enter subject" 
                   minlength="5" maxlength="100" 
                   title="Subject must be between 5 and 100 characters" required>
          </div>

          <!-- Message -->
          <div class="field">
            <label for="message">Message <small>(Minimum 50 characters required)</small></label>
            <textarea id="message" name="message" rows="5" placeholder="Please provide detailed information about your inquiry (minimum 50 characters)" 
                      minlength="50" maxlength="1000" 
                      title="Message must be between 50 and 1000 characters" required></textarea>
          </div>

          <!-- Submit Button -->
          <button type="submit" id="sendMessage">Send Message</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Character counter for message with minimum requirement
    document.getElementById('message').addEventListener('input', function(e) {
      const minLength = 50;
      const maxLength = 1000;
      const currentLength = this.value.length;
      const counter = document.getElementById('charCount') || createCounter();
      
      counter.textContent = `${currentLength}/${maxLength} characters (minimum: ${minLength})`;
      
      if (currentLength > maxLength) {
        this.value = this.value.substring(0, maxLength);
        counter.textContent = `${maxLength}/${maxLength} characters (minimum: ${minLength})`;
        counter.style.color = '#e74c3c';
      } else if (currentLength < minLength) {
        counter.style.color = '#e74c3c';
      } else if (currentLength > maxLength * 0.8) {
        counter.style.color = '#f39c12';
      } else {
        counter.style.color = '#27ae60';
      }
      
      // Enable/disable submit button based on length
      const submitButton = document.getElementById('sendMessage');
      if (currentLength < minLength) {
        submitButton.disabled = true;
        submitButton.style.opacity = '0.6';
        submitButton.style.cursor = 'not-allowed';
      } else {
        submitButton.disabled = false;
        submitButton.style.opacity = '1';
        submitButton.style.cursor = 'pointer';
      }
    });

    function createCounter() {
      const counter = document.createElement('div');
      counter.id = 'charCount';
      counter.style.fontSize = '0.8rem';
      counter.style.marginTop = '5px';
      counter.style.textAlign = 'right';
      counter.style.fontWeight = '500';
      document.querySelector('textarea').parentNode.appendChild(counter);
      return counter;
    }

    // Initialize counter on page load
    document.addEventListener('DOMContentLoaded', function() {
      const messageField = document.getElementById('message');
      if (messageField) {
        const event = new Event('input');
        messageField.dispatchEvent(event);
      }
    });

    // Subject length validation
    document.getElementById('subject').addEventListener('input', function(e) {
      const minLength = 5;
      const maxLength = 100;
      const currentLength = this.value.length;
      
      if (currentLength < minLength) {
        this.setCustomValidity(`Subject must be at least ${minLength} characters long`);
      } else if (currentLength > maxLength) {
        this.setCustomValidity(`Subject must be no more than ${maxLength} characters`);
      } else {
        this.setCustomValidity('');
      }
    });
  </script>
</body>
</html>