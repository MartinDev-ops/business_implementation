
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... rest of your code
class Database {
    private $host = "localhost";
    private $db_name = "security_hub";
    private $username = "root";
    private $password = "Spear@20";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error instead of displaying to user
            error_log("Database connection error: " . $exception->getMessage());
            // Display user-friendly message
            die("Database connection failed. Please try again later.");
        }
        return $this->conn;
    }
}

session_start();

// Check if user is logged in (helper function)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}
?>