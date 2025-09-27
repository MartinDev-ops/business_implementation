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

// Get booking history
$bookingsQuery = "SELECT * FROM bookings WHERE user_id = :user_id ORDER BY event_date DESC LIMIT 5";
$bookingsStmt = $db->prepare($bookingsQuery);
$bookingsStmt->bindParam(":user_id", $_SESSION['user_id']);
$bookingsStmt->execute();
$bookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming booking
$upcomingQuery = "SELECT * FROM bookings WHERE user_id = :user_id AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1";
$upcomingStmt = $db->prepare($upcomingQuery);
$upcomingStmt->bindParam(":user_id", $_SESSION['user_id']);
$upcomingStmt->execute();
$upcomingBooking = $upcomingStmt->fetch(PDO::FETCH_ASSOC);


$guardsQuery = "SELECT g.*, GROUP_CONCAT(gs.name) as specialties 
                FROM guards g 
                LEFT JOIN guard_specialty_map gsm ON g.id = gsm.guard_id 
                LEFT JOIN guard_specialties gs ON gsm.specialty_id = gs.id 
                WHERE g.availability = 'available'
                GROUP BY g.id 
                ORDER BY g.rating DESC";
$guardsStmt = $db->prepare($guardsQuery);
$guardsStmt->execute();
$guards = $guardsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Security Services</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
   
    .guards-section {
      margin-bottom: 2.5rem;
    }
    
    .guards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    
    .guard-card {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      padding: 1.5rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .guard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .guard-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #111827;
    }
    
    .guard-specialties {
      color: #6b7280;
      margin-bottom: 1rem;
      line-height: 1.5;
      font-size: 0.9rem;
    }
    
    .guard-price {
      font-weight: 600;
      color: #059669;
      margin-bottom: 1rem;
    }
    
    .guard-details {
      margin-bottom: 1.5rem;
    }
    
    .guard-detail {
      display: flex;
      align-items: center;
      margin-bottom: 0.5rem;
      color: #4b5563;
      font-size: 0.9rem;
    }
    
    .guard-detail:before {
      content: "✓";
      color: #059669;
      margin-right: 0.5rem;
      font-weight: bold;
    }
    
    .book-btn {
      width: 100%;
      padding: 0.75rem;
      background-color: #3b82f6;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    
    .book-btn:hover {
      background-color: #2563eb;
    }
    
    .rating {
      display: flex;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .guards-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  
  <aside class="sidebar">
    <div class="profile">
      <img src="<?php echo $user['profile_picture'] ?: 'default-avatar.png'; ?>" alt="Profile Picture" id="userImage">
      <h3 id="userName"><?php echo htmlspecialchars($user['username']); ?></h3>
    </div>
    <nav class="menu">
      <a href="#" class="active">Dashboard</a>
      <a href="profile.php">Profile</a>
      <a href="booking.php">My Bookings</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <a href="logout.php" class="logout">Logout</a>
  </aside>

  
  <main class="dashboard">
    <h1>Security Services Dashboard</h1>

    <!-- Available Security Guards -->
    <section class="guards-section" id="services-section">
      <h2>Available Security Guards</h2>
      <p>Browse our professional security personnel and book the protection you need.</p>
      
      <div class="guards-grid">
        <?php if (empty($guards)): ?>
          <div class="guard-card">
            <p>No security guards available at the moment. Please check back later.</p>
            <p><small>To test the system, you need to add guards to the database.</small></p>
          </div>
        <?php else: ?>
          <?php foreach ($guards as $guard): ?>
            <div class="guard-card">
              <h3 class="guard-title"><?php echo htmlspecialchars($guard['full_name']); ?></h3>
              
              <?php if ($guard['is_verified']): ?>
                <div class="guard-detail" style="color: #059669;">
                  <span style="background: #059669; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 5px;">Verified</span>
                  Verified Professional
                </div>
              <?php endif; ?>
              
              <div class="rating">
                <span>Rating: <?php echo $guard['rating'] ?? 0; ?>/5 (<?php echo $guard['total_ratings'] ?? 0; ?> reviews)</span>
              </div>
              
              <div class="guard-price">
                R<?php echo htmlspecialchars($guard['hourly_rate']); ?>/hour
              </div>
              
              <div class="guard-specialties">
                <strong>Specialties:</strong> <?php echo htmlspecialchars($guard['specialties'] ?? 'Various security services'); ?>
              </div>
              
              <?php if (!empty($guard['bio'])): ?>
                <div class="guard-details">
                  <p><?php echo htmlspecialchars($guard['bio']); ?></p>
                </div>
              <?php endif; ?>
              
              <div class="guard-details">
                <div class="guard-detail"><?php echo $guard['experience_years'] ?? 0; ?>+ years experience</div>
                <div class="guard-detail">Available for booking</div>
              </div>
              
              <button class="book-btn" onclick="location.href='booking.php?guard_id=<?php echo $guard['id']; ?>'">
                Book This Guard
              </button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Upcoming Booking -->
    <section class="upcoming">
      <h2>Upcoming Booking</h2>
      <div class="booking-card <?php echo empty($upcomingBooking) ? 'empty' : ''; ?>" id="upcomingBooking">
        <?php if (empty($upcomingBooking)): ?>
          <p>No upcoming bookings yet.</p>
          <p><small><a href="booking.php" style="color: #3b82f6; text-decoration: none;">Book your first security guard now</a></small></p>
        <?php else: ?>
          <p><strong>Event:</strong> <?php echo htmlspecialchars($upcomingBooking['event_name']); ?></p>
          <p><strong>Date:</strong> <?php echo htmlspecialchars($upcomingBooking['event_date']); ?></p>
          <p><strong>Status:</strong> <span style="padding: 2px 8px; border-radius: 4px; background: #fef3c7; color: #d97706;"><?php echo htmlspecialchars($upcomingBooking['status']); ?></span></p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Booking History -->
    <section class="history">
      <h2>Recent Bookings</h2>
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Event</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="historyTable">
          <?php if (empty($bookings)): ?>
            <tr>
              <td colspan="4" style="text-align:center; color:#9ca3af;">
                No bookings yet. <a href="booking.php" style="color: #3b82f6;">Make your first booking</a>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
              <tr>
                <td>#<?php echo htmlspecialchars($booking['id']); ?></td>
                <td><?php echo htmlspecialchars($booking['event_name']); ?></td>
                <td><?php echo htmlspecialchars($booking['event_date']); ?></td>
                <td>
                  <span style="padding: 2px 8px; border-radius: 4px; 
                    <?php 
                    $status = $booking['status'];
                    if ($status == 'confirmed') echo 'background: #d1fae5; color: #065f46;';
                    elseif ($status == 'pending') echo 'background: #fef3c7; color: #d97706;';
                    elseif ($status == 'completed') echo 'background: #f3f4f6; color: #374151;';
                    elseif ($status == 'cancelled') echo 'background: #fee2e2; color: #dc2626;';
                    else echo 'background: #eff6ff; color: #1e40af;';
                    ?>">
                    <?php echo htmlspecialchars($status); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

    <!-- Quick Actions -->
    <section class="actions">
      <h2>Quick Actions</h2>
      <div class="action-buttons">
        <button class="btn" onclick="location.href='#services-section'">Browse Guards</button>
        <button class="btn" onclick="location.href='booking.php'">New Booking</button>
        <button class="btn secondary" onclick="location.href='profile.php'">Manage Profile</button>
      </div>
    </section>
  </main>
</body>
</html>