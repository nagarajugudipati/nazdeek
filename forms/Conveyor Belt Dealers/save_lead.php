<?php
// save_lead.php for Conveyor Belt Dealers

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'nazdeek';
$table_name = 'conveyor_belt_dealers_leads';

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
    `belt_type` VARCHAR(255) NOT NULL,
    `belt_width` VARCHAR(255) NOT NULL,
    `belt_length` VARCHAR(255) NOT NULL,
    `industry` VARCHAR(255) NOT NULL,
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
    $belt_type = isset($_POST['belt_type']) ? trim($_POST['belt_type']) : '';
    if (empty($belt_type)) { $errors[] = 'Field Conveyor Belt Type is required.'; }
    $belt_width = isset($_POST['belt_width']) ? trim($_POST['belt_width']) : '';
    if (empty($belt_width)) { $errors[] = 'Field Belt Width Required is required.'; }
    $belt_length = isset($_POST['belt_length']) ? trim($_POST['belt_length']) : '';
    if (empty($belt_length)) { $errors[] = 'Field Belt Length Required is required.'; }
    $industry = isset($_POST['industry']) ? trim($_POST['industry']) : '';
    if (empty($industry)) { $errors[] = 'Field Industry/Application is required.'; }

    if (empty($errors)) {
        // Prepare SQL insert statement
        $stmt = $conn->prepare("INSERT INTO `$table_name` (`name`, `mobile`, `email`, `city`, `pincode`, `locality`, `belt_type`, `belt_width`, `belt_length`, `industry`, `quantity`, `requirements`, `preferred_time`, `budget`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssssissd", $name, $mobile, $email, $city, $pincode, $locality, $belt_type, $belt_width, $belt_length, $industry, $quantity, $requirements, $preferred_time, $budget);
            if ($stmt->execute()) {
                show_response_page(true, "Request sent!", "Your enquiry for " . ucwords(str_replace('_', ' ', preg_replace('/_leads$/', '', $table_name))) . " has been received. A verified provider will reach out to you shortly.");
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

    $status_icon = $success ? '&check;' : '&#10060;';
    $heading_class = $success ? 'success-title' : 'error-title';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Nazdeek</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial,sans-serif;
        }

        body{
            background:#f5f5f5;
            color:#1f2937;
        }

        .header{
            background:linear-gradient(90deg,#7c3aed,#6d28d9);
            color:white;
            padding:18px 20px;
            display:flex;
            align-items:center;
            gap:14px;
        }

        .back-btn{
            color:white;
            text-decoration:none;
            font-size:24px;
            line-height:1;
        }

        .logo{
            font-size:18px;
            font-weight:700;
        }

        .container{
            max-width:900px;
            margin:30px auto;
            padding:0 15px;
        }

        .response-card{
            background:#fff;
            border-radius:20px;
            padding:50px;
            text-align:center;
            box-shadow:0 4px 20px rgba(0,0,0,.08);
        }

        .icon{
            width:90px;
            height:90px;
            margin:0 auto 25px;
            background:#22c55e;
            color:white;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:55px;
            box-shadow:0 4px 15px rgba(34,197,94,.35);
        }

        .error-title{
            color:#db2777;
        }

        .success-title{
            color:#059669;
        }

        h1{
            font-size:42px;
            margin-bottom:20px;
        }

        .success-box{
            color:#374151;
            font-size:20px;
            line-height:1.7;
        }

        .message-list{
            background:#fdf2f8;
            border-left:4px solid #db2777;
            padding:15px 15px 15px 35px;
            border-radius:8px;
            text-align:left;
            color:#9d174d;
            line-height:1.6;
        }

        .button-group{
            margin-top:30px;
            display:flex;
            justify-content:center;
            gap:15px;
            flex-wrap:wrap;
        }

        .btn-primary{
            background:#7c3aed;
            color:white;
            text-decoration:none;
            padding:14px 28px;
            border-radius:12px;
            font-weight:bold;
        }

        .btn-secondary{
            border:2px solid #d8b4fe;
            color:#6d28d9;
            text-decoration:none;
            padding:14px 28px;
            border-radius:12px;
            font-weight:bold;
            background:white;
        }

        .partner-footer{
            margin-top:25px;
            background:linear-gradient(135deg,#7c3aed,#4c1d95);
            border-radius:20px;
            padding:30px;
            color:white;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
        }

        .partner-badge{
            display:inline-block;
            background:rgba(255,255,255,.15);
            padding:8px 14px;
            border-radius:20px;
            margin-bottom:15px;
            font-weight:bold;
        }

        .partner-footer h2{
            margin-bottom:10px;
            font-size:28px;
        }

        .partner-footer p{
            line-height:1.7;
        }

        .partner-btn{
            display:inline-block;
            margin-top:20px;
            background:white;
            color:#5b21b6;
            text-decoration:none;
            padding:12px 24px;
            border-radius:30px;
            font-weight:bold;
        }

        .partner-rocket{
            font-size:80px;
        }

        @media (max-width:768px){
            .container{
                margin:20px auto;
            }

            .response-card{
                padding:35px 20px;
            }

            h1{
                font-size:32px;
            }

            .success-box{
                font-size:17px;
            }

            .partner-footer{
                flex-direction:column;
                text-align:center;
            }

            .partner-footer h2{
                font-size:24px;
            }

            .partner-rocket{
                font-size:55px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <a href="javascript:history.back()" class="back-btn">&#8592;</a>
    <span class="logo">Nazdeek</span>
</div>

<div class="container">

    <div class="response-card">

        <div class="icon">$status_icon</div>

        <h1 class="$heading_class">$title</h1>

        $msg_html

        <div class="button-group">
            <a href="/" class="btn-primary">Browse more services</a>
            <a href="#" class="btn-secondary">My dashboard</a>
        </div>

    </div>

    <div class="partner-footer">

        <div class="partner-content">

            <div class="partner-badge">
                &#10024; BECOME A PARTNER
            </div>

            <h2>
                List your business from &#8377;49 / 7 days
            </h2>

            <p>
                Plans: &#8377;49 / 7d &middot; &#8377;99 / 15d &middot; &#8377;199 / 30d &middot; +&#8377;50 per 15 days &middot; &#8377;1,299 / year (Best Value) &middot; Street Vendors Free
            </p>

            <a href="#" class="partner-btn">
                View Plans &#8594;
            </a>

        </div>

        <div class="partner-rocket">
            &#128640;
        </div>

    </div>

</div>

</body>
</html>
HTML;
}
?>