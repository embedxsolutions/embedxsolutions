<?php
// Contact form submission handler
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Response array
$response = array();

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and validate input
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    
    // Validation
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Please select a subject";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        $response['success'] = false;
        $response['message'] = implode(", ", $errors);
        echo json_encode($response);
        exit;
    }
    
    // Prepare email content
    $to = "contact@embedxsolutions.com"; // Change this to your email
    $email_subject = "New Contact Form Submission: " . $subject;
    
    // Create email body
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Phone: " . $phone . "\n";
    $email_body .= "Subject: " . $subject . "\n";
    $email_body .= "Message:\n" . $message . "\n";
    
    // Email headers
    $headers = "From: noreply@embedxsolutions.com\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Try to send email
    if (mail($to, $email_subject, $email_body, $headers)) {
        // Also save to file as backup
        $log_entry = date('Y-m-d H:i:s') . " | Name: $name | Email: $email | Phone: $phone | Subject: $subject | Message: $message\n";
        file_put_contents('contact-submissions.txt', $log_entry, FILE_APPEND);
        
        $response['success'] = true;
        $response['message'] = "Thank you for contacting us! We'll get back to you soon.";
    } else {
        // If email fails, still save to file
        $log_entry = date('Y-m-d H:i:s') . " | Name: $name | Email: $email | Phone: $phone | Subject: $subject | Message: $message\n";
        file_put_contents('contact-submissions.txt', $log_entry, FILE_APPEND);
        
        $response['success'] = true;
        $response['message'] = "Thank you for contacting us! Your message has been recorded.";
    }
    
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method";
}

echo json_encode($response);
?>
