<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define a pool of questions
$questions = array(
    1 => array('question' => 'What is 12 + 15?', 'answer' => '27'),
    2 => array('question' => 'What is 7 * 8?', 'answer' => '56'),
    3 => array('question' => 'What is 25 / 5?', 'answer' => '5'),
    4 => array('question' => 'What is 15 - 6?', 'answer' => '9'),
    5 => array('question' => 'What is 5 squared?', 'answer' => '25'),
    6 => array('question' => 'What is 100 minus 45?', 'answer' => '55'),
    7 => array('question' => 'What is 4 squared?', 'answer' => '16')
);

// Select a random question
$question_id = array_rand($questions);
$question_text = $questions[$question_id]['question'];

// Store the question ID and text in a session
$_SESSION['question_id'] = $question_id;
$_SESSION['question_text'] = $question_text;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "hkumar@blackrockdigital.agency, dsingh@blackrockdigital.agency, asharma@blackrockdigital.agency, support@blackrockdigital.agency";

    // Directly assign form data
    $name = $_POST["first_name"];
    $dealership = $_POST["dealership"];
    $email = $_POST["email"];
    $title = $_POST["title"];
    $request_type = $_POST["request_type"];
    $comments = $_POST["comments"];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Validate the email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='alert alert-danger'>Invalid email format. Please enter a valid email address.</div>";
        exit;
    }

    // Verify the reCAPTCHA response
    $secret = '6LfDgDMmAAAAAGx2jy7Im5SuiY8WeKuAdmPmy1R6';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = array(
        'secret' => $secret,
        'response' => $recaptcha_response
    );

    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($recaptcha_data)
        )
    );

    $context  = stream_context_create($options);
    $recaptcha_response = file_get_contents($recaptcha_url, false, $context);
    $recaptcha_result = json_decode($recaptcha_response);

    if ($recaptcha_result->success) {
    $subject = "Rafih Portal Request : $name - $dealership";

    // Email body with proper line endings
    $message = "Name: $name\r\n";
    $message .= "Dealership: $dealership\r\n";
    $message .= "Email: $email\r\n";
    $message .= "Title: $title\r\n";
    $message .= "Request Type: $request_type\r\n";
    $message .= "Comments:\r\n $comments";

    // Headers
    $headers = "From: request@rafihportal.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo "<div class='alert alert-success'>Form submitted successfully. We will get back to you soon.</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to submit the form. Please try again later.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>reCAPTCHA validation failed. Please try again.</div>";
}


    // Clear the question ID from the session
    unset($_SESSION['question_id']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Ticket</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container mt-2">
        <center><br>
        <img src="https://rafihqma.com/public/uploads/main/images/25-04-2023/6446f073158cf.jpeg" width="180px"><br>
        <h2>New Ticket Submission</h2></center>
        <hr><br>
        <form method="post">
            <input type="hidden" name="hp_field" id="hp_field">
            <input type="hidden" name="form_time" value="<?php echo time(); ?>">
            <input type="hidden" name="question_id" value="<?php echo $_SESSION['question_id']; ?>">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="first_name">Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                       
                            <label for="dealership">Dealership:</label>
                        <select class="form-control" id="dealership" name="dealership" required>
                            <option value="Midtown Honda">Midtown Honda</option>
                            <option value="Performance Ford & Lincoln of Windsor">Performance Ford & Lincoln of Windsor</option>
                            <option value="BMW Rochester hills">BMW Rochester hills</option>
                            <option value="Audi Rochestorhills">Audi Rochestorhills</option>
                            <option value="Whiteoak Lincoln">Whiteoak Lincoln</option>
                            <option value="Whiteoak Ford & Lincoln">Whiteoak Ford & Lincoln</option>
                            <option value="Overseas Mercedes benz">Overseas Mercedes benz</option>
                            <option value="Overseas BMW & Mini">Overseas BMW & Mini</option>
                            <option value="Mini Motor City">Mini Motor City</option>
                            <option value="Windsor Mazda">Windsor Mazda</option>
                            <option value="Leamington GM">Leamington GM</option>
                            <option value="JLR London">JLR London</option>
                            <option value="JLR Windsor">JLR Windsor</option>
                            <option value="EASTWAY Toyota & Lexus Of Windsor">EASTWAY Toyota & Lexus Of Windsor</option>
                            <option value="Countryside Chrysler">Countryside Chrysler</option>
                            <option value="Oakridge Ford">Oakridge Ford</option>
                            <option value="Mercedes Benz north Olmsted">Mercedes Benz north Olmsted</option>
                            <option value="Porsche Cleveland">Porsche Cleveland</option>
                            <option value="Porsche Motor City">Porsche Motor City</option>
                            <option value="Cleveland Motorsports">Cleveland Motorsports</option>
							 <option value="Toyota of Warren">Toyota of Warren</option>
                        </select>
                      
                    </div>

                    <div class="form-group">
                        <label for="email">Email Id:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="request_type">Request type:</label>
                        <select class="form-control" id="request_type" name="request_type" required>
                            <option value="">Select Request Type</option>
                            <option value="I need my password.">I need my password.</option>
                            <option value="I need to register.">I need to register.</option>
                            <option value="Technical issue">Technical issue</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="comments">Comments:</label>
                        <textarea class="form-control" id="comments" name="comments" rows="6" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="math_question"><?php echo $_SESSION['question_text']; ?></label>
                        <input type="text" class="form-control" id="math_question" name="math_question" placeholder="Type in lower case" required>
                    </div>

                    <div class="g-recaptcha" data-sitekey="6LfDgDMmAAAAANd7RHeTcRzspUIow9WwvrxeQRT1"></div><br>

                    <button type="submit" class="btn btn-success">Submit</button><br>
                </div>
            </div>
        </form>
    </div>

    <center><br><br>
    <hr><br>
    <a href="https://rafihqma.com/login" class="backtohome">Back To Home</a>
    </center><br>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
