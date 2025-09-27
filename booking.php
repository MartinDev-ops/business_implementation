<?php
require_once "config.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$userQuery = "SELECT * FROM users WHERE id = :user_id";
$userStmt = $db->prepare($userQuery);
$userStmt->bindParam(":user_id", $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);


$guard = null;
if (isset($_GET['guard_id'])) {
    $guardQuery = "SELECT g.*, GROUP_CONCAT(gs.name) as specialties 
                   FROM guards g 
                   LEFT JOIN guard_specialty_map gsm ON g.id = gsm.guard_id 
                   LEFT JOIN guard_specialties gs ON gsm.specialty_id = gs.id 
                   WHERE g.id = :guard_id 
                   GROUP BY g.id";
    $guardStmt = $db->prepare($guardQuery);
    $guardStmt->bindParam(":guard_id", $_GET['guard_id']);
    $guardStmt->execute();
    $guard = $guardStmt->fetch(PDO::FETCH_ASSOC);
}

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guard_id = $_POST['guard_id'];
    $event_name = trim($_POST['event_name']);
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $duration_hours = $_POST['duration_hours'];
    $location = trim($_POST['location']);
    $special_requirements = trim($_POST['special_requirements']);
    
   
    if (strlen($special_requirements) > 1000) {
        $error = "Special requirements must be less than 1000 characters.";
    } else {
        
        $event_name = htmlspecialchars($event_name, ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
        $special_requirements = htmlspecialchars($special_requirements, ENT_QUOTES, 'UTF-8');
        
        // Calculate total amount
        $guardRateQuery = "SELECT hourly_rate FROM guards WHERE id = :guard_id";
        $rateStmt = $db->prepare($guardRateQuery);
        $rateStmt->bindParam(":guard_id", $guard_id);
        $rateStmt->execute();
        $guard_rate = $rateStmt->fetchColumn();
        $total_amount = $guard_rate * $duration_hours;
        
        
        $query = "INSERT INTO bookings (user_id, guard_id, event_name, event_type, event_date, event_time, 
                  duration_hours, location, special_requirements, total_amount, status) 
                  VALUES (:user_id, :guard_id, :event_name, :event_type, :event_date, :event_time, 
                  :duration_hours, :location, :special_requirements, :total_amount, 'pending')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->bindParam(":guard_id", $guard_id);
        $stmt->bindParam(":event_name", $event_name);
        $stmt->bindParam(":event_type", $event_type);
        $stmt->bindParam(":event_date", $event_date);
        $stmt->bindParam(":event_time", $event_time);
        $stmt->bindParam(":duration_hours", $duration_hours);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":special_requirements", $special_requirements);
        $stmt->bindParam(":total_amount", $total_amount);
        
        if ($stmt->execute()) {
            $success = "Booking request submitted successfully! We'll contact you to confirm details.";
            
            $_POST = [];
        } else {
            $error = "Error submitting booking. Please try again.";
        }
    }
}

// Get user's bookings
$bookingsQuery = "SELECT b.*, g.full_name as guard_name 
                  FROM bookings b 
                  LEFT JOIN guards g ON b.guard_id = g.id 
                  WHERE b.user_id = :user_id 
                  ORDER BY b.event_date DESC";
$bookingsStmt = $db->prepare($bookingsQuery);
$bookingsStmt->bindParam(":user_id", $_SESSION['user_id']);
$bookingsStmt->execute();
$bookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Security Hub</title>
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        
        <aside class="sidebar">
            <div class="profile">
                <img src="<?php echo $user['profile_picture'] ?: 'default-avatar.png'; ?>" alt="Profile Picture">
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            </div>
            <nav class="menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="#" class="active">My Bookings</a>
                <a href="contact.php">Contact Us</a>
            </nav>
            <a href="logout.php" class="logout">Logout</a>
        </aside>

        
        <main class="main-content">
            <div class="header">
                <h1>Security Bookings</h1>
                <p>Manage your security service bookings</p>
            </div>

            
            <div class="booking-section">
                <h2><?php echo $guard ? 'Book ' . htmlspecialchars($guard['full_name']) : 'New Booking'; ?></h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="booking.php" class="booking-form">
                    <?php if ($guard): ?>
                        <input type="hidden" name="guard_id" value="<?php echo $guard['id']; ?>">
                        <div class="guard-summary">
                            <div class="guard-avatar">
                                <i class="fas fa-user-shield" style="font-size: 3rem; color: #3b82f6;"></i>
                            </div>
                            <div>
                                <h4><?php echo htmlspecialchars($guard['full_name']); ?></h4>
                                <p>Rate: $<?php echo $guard['hourly_rate']; ?>/hour</p>
                                <p>Specialties: <?php echo htmlspecialchars($guard['specialties'] ?? 'Various'); ?></p>
                                <?php if ($guard['experience_years']): ?>
                                    <p>Experience: <?php echo $guard['experience_years']; ?> years</p>
                                <?php endif; ?>
                                <?php if ($guard['rating']): ?>
                                    <p>Rating: <?php echo $guard['rating']; ?> ★</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="guard_id">Select Guard</label>
                            <select name="guard_id" id="guard_id" required>
                                <option value="">Choose a security guard...</option>
                                <?php
                                $guardsQuery = "SELECT * FROM guards WHERE availability = 'available'";
                                $guardsStmt = $db->prepare($guardsQuery);
                                $guardsStmt->execute();
                                $allGuards = $guardsStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($allGuards as $g): ?>
                                    <option value="<?php echo $g['id']; ?>" 
                                        <?php echo ($_POST['guard_id'] ?? '') == $g['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($g['full_name']); ?> - $<?php echo $g['hourly_rate']; ?>/hour
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="event_name">Event Name</label>
                        <input type="text" id="event_name" name="event_name" 
                               value="<?php echo htmlspecialchars($_POST['event_name'] ?? ''); ?>" 
                               placeholder="e.g., Corporate Conference, Wedding Reception" required
                               maxlength="100">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_type">Event Type</label>
                            <select id="event_type" name="event_type" required>
                                <option value="">Select event type</option>
                                <option value="wedding" <?php echo ($_POST['event_type'] ?? '') == 'wedding' ? 'selected' : ''; ?>>Wedding</option>
                                <option value="corporate" <?php echo ($_POST['event_type'] ?? '') == 'corporate' ? 'selected' : ''; ?>>Corporate</option>
                                <option value="personal" <?php echo ($_POST['event_type'] ?? '') == 'personal' ? 'selected' : ''; ?>>Personal</option>
                                <option value="concert" <?php echo ($_POST['event_type'] ?? '') == 'concert' ? 'selected' : ''; ?>>Concert</option>
                                <option value="other" <?php echo ($_POST['event_type'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="duration_hours">Duration (hours)</label>
                            <input type="number" id="duration_hours" name="duration_hours" 
                                   value="<?php echo htmlspecialchars($_POST['duration_hours'] ?? '4'); ?>" min="1" max="24" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Event Date</label>
                            <input type="date" id="event_date" name="event_date" 
                                   value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="event_time">Event Time</label>
                            <input type="time" id="event_time" name="event_time" 
                                   value="<?php echo htmlspecialchars($_POST['event_time'] ?? '09:00'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Event Location</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" 
                               placeholder="Full address of the event" required
                               maxlength="200">
                    </div>

                    <div class="form-group">
                        <label for="special_requirements">Special Requirements <small>(Maximum 1000 characters)</small></label>
                        <textarea id="special_requirements" name="special_requirements" 
                                  placeholder="Any special instructions or requirements..." 
                                  rows="4" maxlength="1000"><?php echo htmlspecialchars($_POST['special_requirements'] ?? ''); ?></textarea>
                        <div id="special_requirements_counter" style="font-size: 0.8rem; margin-top: 5px; text-align: right; color: #6b7280;"></div>
                    </div>

                    <button type="submit" class="btn primary">
                        <i class="fas fa-calendar-check"></i> Submit Booking Request
                    </button>
                </form>
            </div>

            
            <div class="bookings-section">
                <h2>Booking History</h2>
                
                <?php if (empty($bookings)): ?>
                    <div class="no-bookings">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No bookings yet</h3>
                        <p>Start by booking your first security guard</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card <?php echo $booking['status']; ?>">
                                <div class="booking-header">
                                    <h4><?php echo htmlspecialchars($booking['event_name']); ?></h4>
                                    <span class="status-badge"><?php echo ucfirst($booking['status']); ?></span>
                                </div>
                                
                                <div class="booking-details">
                                    <p><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($booking['guard_name'] ?? 'Not assigned'); ?></p>
                                    <p><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($booking['event_date'])); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($booking['event_time'])); ?></p>
                                    <p><i class="fas fa-hourglass-half"></i> <?php echo $booking['duration_hours']; ?> hours</p>
                                    <p><i class="fas fa-dollar-sign"></i> $<?php echo $booking['total_amount']; ?></p>
                                </div>
                                
                                <?php if (!empty($booking['location'])): ?>
                                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['location']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        
        document.getElementById('event_date').min = new Date().toISOString().split('T')[0];
        
        
        const now = new Date();
        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                           now.getMinutes().toString().padStart(2, '0');
        document.getElementById('event_time').value = currentTime;

        
        document.getElementById('special_requirements').addEventListener('input', function(e) {
            const maxLength = 1000;
            const currentLength = this.value.length;
            const counter = document.getElementById('special_requirements_counter');
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
                counter.textContent = `${maxLength}/${maxLength} characters`;
                counter.style.color = '#e74c3c';
            } else if (currentLength > maxLength * 0.8) {
                counter.style.color = '#f39c12';
            } else {
                counter.style.color = '#6b7280';
            }
        });

        
        document.addEventListener('DOMContentLoaded', function() {
            const specialRequirementsField = document.getElementById('special_requirements');
            if (specialRequirementsField) {
                const event = new Event('input');
                specialRequirementsField.dispatchEvent(event);
            }
        });

        
        document.getElementById('event_name').addEventListener('input', function(e) {
            const maxLength = 100;
            const currentLength = this.value.length;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });

        document.getElementById('location').addEventListener('input', function(e) {
            const maxLength = 200;
            const currentLength = this.value.length;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });
    </script>
</body>
</html>