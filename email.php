<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$to = "asharma@blackrockdigital.agency";
$subject = 'Office supplies - Reminder';

// Message

$message = '
<html>
<head>
  <title>Review Request Reminder</title>
</head>
<body>
  <p>Here are the cases requiring your review in December:</p>
  <table>
    <tr>
      <th>Case title</th><th>Category</th><th>Status</th><th>Due date</th>
    </tr>
    <tr>
      <td>Case 1</td><td>Development</td><td>pending</td><td>Dec-20</td>
    </tr>
    <tr>
      <td>Case 1</td><td>DevOps</td><td>pending</td><td>Dec-21</td>
    </tr>
  </table>
</body>
</html>
';
// To send HTML emails, remember to set the Content-type header

$headers = "MIME-Version: 1.0" . "\r\n";

$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";



$send=mail($to,$subject,$message,$headers);
print($send);

?>