<?php
// save_lead.php for Water Pump Manufacturers

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'nazdeek';
$table_name = 'water_pump_manufacturers_leads';

// Load centralized db config if available
if (file_exists(__DIR__ . '/../db_config.php')) {
    include __DIR__ . '/../db_config.php';
}

// 1. Establish connection to MySQL
$conn = new mysqli($db_host, $db_user, $db_pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create database if not exists
$sql_db = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql_db)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// 3. Create table if not exists
$sql_table = "CREATE TABLE IF NOT EXISTS `$table_name` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(10) NOT NULL,
    `email` VARCHAR(100) NULL,
    `city` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(6) NOT NULL,
    `locality` VARCHAR(255) NOT NULL,
    `pump_type` VARCHAR(255) NOT NULL,
    `capacity` VARCHAR(255) NOT NULL,
    `application` VARCHAR(255) NOT NULL,
    `custom_manufacturing` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `requirements` TEXT NOT NULL,
    `preferred_time` VARCHAR(50) NULL,
    `budget` DECIMAL(10, 2) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if (!$conn->query($sql_table)) {
    die("Error creating table: " . $conn->error);
}

// 4. Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize common inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
    $locality = isset($_POST['locality']) ? trim($_POST['locality']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $requirements = isset($_POST['requirements']) ? trim($_POST['requirements']) : '';
    $preferred_time = isset($_POST['preferred_time']) ? trim($_POST['preferred_time']) : 'Any Time';
    $budget = (isset($_POST['budget']) && $_POST['budget'] !== '') ? floatval($_POST['budget']) : null;

    // Server-side validation
    $errors = [];
    if (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
        $errors[] = "Invalid Name. Only alphabets and spaces are allowed (2-50 characters).";
    }
    if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $errors[] = "Invalid Mobile Number. Must be a valid 10-digit Indian number starting with 6-9.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email format.";
    }
    if (!preg_match("/^[A-Za-z ]{2,50}$/", $city)) {
        $errors[] = "Invalid City. Only alphabets and spaces are allowed (2-50 characters).";
    }
    if (!preg_match("/^[0-9]{6}$/", $pincode)) {
        $errors[] = "Invalid PIN Code. Must be exactly 6 digits.";
    }
    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1.";
    }

    // Collect and sanitize service-specific inputs
    $pump_type = isset($_POST['pump_type']) ? trim($_POST['pump_type']) : '';
    if (empty($pump_type)) { $errors[] = 'Field Water Pump Type is required.'; }
    $capacity = isset($_POST['capacity']) ? trim($_POST['capacity']) : '';
    //if (empty($capacity)) { $errors[] = 'Field Capacity Required is required.'; }
    $application = isset($_POST['application']) ? trim($_POST['application']) : '';
    if (empty($application)) { $errors[] = 'Field Application is required.'; }
    $custom_manufacturing = isset($_POST['custom_manufacturing']) ? trim($_POST['custom_manufacturing']) : '';
    if (empty($custom_manufacturing)) { $errors[] = 'Field Custom Manufacturing Required is required.'; }

    if (empty($errors)) {
        // Prepare SQL insert statement
        $stmt = $conn->prepare("INSERT INTO `$table_name` (`name`, `mobile`, `email`, `city`, `pincode`, `locality`, `pump_type`, `capacity`, `application`, `custom_manufacturing`, `quantity`, `requirements`, `preferred_time`, `budget`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssssissd", $name, $mobile, $email, $city, $pincode, $locality, $pump_type, $capacity, $application, $custom_manufacturing, $quantity, $requirements, $preferred_time, $budget);
            if ($stmt->execute()) {
                show_response_page(true, "Lead Saved Successfully", "Thank you! Your quote request has been received. Our partner providers will contact you shortly.");
            } else {
                show_response_page(false, "Database Error", "Unable to save your request. Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            show_response_page(false, "System Error", "Failed to prepare database statement: " . $conn->error);
        }
    } else {
        // Display validation errors
        show_response_page(false, "Validation Failed", $errors);
    }
} else {
    header("Location: index.html");
    exit;
}

$conn->close();

function show_response_page($success, $title, $messages) {
    $msg_html = '';
    if (is_array($messages)) {
        $msg_html .= '<ul class="message-list">';
        foreach ($messages as $msg) {
            $msg_html .= "<li>" . htmlspecialchars($msg) . "</li>";
        }
        $msg_html .= '</ul>';
    } else {
        $msg_html = '<div class="success-box">' . htmlspecialchars($messages) . '</div>';
    }
    
    $status_icon = $success ? '💜' : '❌';
    
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Nazdeek</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .response-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #4c1d95;
            margin-bottom: 15px;
            font-size: 28px;
        }
        .message-list {
            text-align: left;
            background: #fdf2f8;
            border-left: 4px solid #db2777;
            padding: 15px 15px 15px 35px;
            border-radius: 8px;
            margin: 20px 0;
            color: #9d174d;
        }
        .success-box {
            background: #fdf4ff;
            border: 1px solid #f3e8ff;
            color: #6b21a8;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            background: #7c3aed;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.2s;
            margin-top: 15px;
        }
        .btn:hover {
            background: #6d28d9;
        }
    </style>
</head>
<body>
    <div class="response-card">
        <div class="icon">$status_icon</div>
        <h1>$title</h1>
        $msg_html
        <br>
        <a href="index.html" class="btn">← Back to Form</a>
    </div>
</body>
</html>
HTML;
}
?>