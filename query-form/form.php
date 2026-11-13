<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set your email address where you want to receive the form submissions
    $to = "hk.delhi2009@gmail.com";

    // Get form data
    $name = $_POST["first_name"];
    $dealership = $_POST["dealership"];
    $email = $_POST["email"];
    $title = $_POST["title"];
    $request_type = $_POST["request_type"];
    $comments = $_POST["comments"];

    // Subject of the email
    $subject = "New Ticket Submission - $name";

    // Email body
    $message = "Name: $name\n";
    $message .= "Dealership: $dealership\n";
    $message .= "Email: $email\n";
    $message .= "Title: $title\n";
    $message .= "Request Type: $request_type\n";
    $message .= "Comments:\n$comments";

    // Additional headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "Form submitted successfully. We will get back to you soon.";
    } else {
        echo "Failed to submit the form. Please try again later.";
    }
}
?>
