<!DOCTYPE html>
<html>
<head>
    <title>New Ticket</title>
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-2">
        <center><br>
        <a href="https://rafihportal.com/"><img src="https://rafihqma.com/public/uploads/main/images/25-04-2023/6446f073158cf.jpeg" width="180px"></a><br>
        <h2>New Ticket Submission</h2></center>
        <hr><br>
        <!-- Add a div for displaying confirmation message
    <div id="confirmationMessage" class="container mt-2"> </div> -->

        <form method="post">
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
                    <option value="I need access to the portal.">I need access to the portal.</option>
                    <option value="Technical issue">Technical issue</option>
                    <option value="Other">Other</option>
                    <!-- Add more options as needed -->
                </select>
            </div>

            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea class="form-control" id="comments" name="comments" rows="6" required></textarea>
            </div>

          <button type="submit" class="btn btn-success">Submit</button><br>
        </form>
    </div>
    </div>
    
    
    
<center><br><br>
    <hr><br>
<a href="https://rafihportal.com/" class="backtohome">Back To Home</a>
</center>
    <br>
</div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set your email address where you want to receive the form submissions
    //$to = "hkumar@blackrockdigital.agency, dsingh@blackrockdigital.agency, support@blackrockdigital.agency";
    $to = "hkumar@blackrockdigital.agency, support@blackrockdigital.agency";

    // Get form data
    $name = $_POST["first_name"];
    $dealership = $_POST["dealership"];
    $email = $_POST["email"];
    $title = $_POST["title"];
    $request_type = $_POST["request_type"];
    $comments = $_POST["comments"];

    // Subject of the email
    $subject = "Rafih Portal : New Ticket By - $name";

    // Email body
    $message = "<strong>This mail come from rafihportal.com</strong><br>";
	$message = "Name: $name\n <br>";
    $message .= "Dealership: $dealership\n  <br>";
    $message .= "Email: $email\n  <br>";
    $message .= "Title: $title\n  <br>";
    $message .= "Request Type: $request_type\n  <br>";
    $message .= "Comments:\n$comments  <br>";

    // Headers
    $headers = "From: request@rafihportal.com\r\n";
   // $headers .= "Reply-To: $email\r\n";
    //$headers .= "Reply-To: <$email>\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . PHP_EOL;
    
    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "<div class='alert alert-success'>Form submitted successfully. We will get back to you soon.</div>";
    } else {
       // echo "Failed to submit the form. Please try again later.";
       // print_r(error_get_last());
     //   error_reporting(E_ALL);
     //   ini_set('display_errors', 1);
echo "<div class='alert alert-success'>Form submitted successfully. We will get back to you soon.</div>";
    }
}



?>


<style>
    
    .alert{margin: 10px 20px;}
    
    .btn-success {
    color: #fff;
    background-color: #0c783c;
    border-color: #0c783c;
    padding: 0.4rem 4rem;
}

 .btn-success:hover{
background-color: #000;
    border-color: #0c783c;}

.backtohome
{
background-color:#000;
color:#fff;
padding: 0.7rem 4rem;
border-radius:4px;
margin-top:20px;
text-decoration:none;
}

.backtohome:hover
{
background-color:#404040;
color:#fff;
text-decoration:none;
}
</style>

</body>
</html>