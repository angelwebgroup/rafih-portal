<?php

/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/email_functions.inc.php');


// Start session
hesk_session_start();

// Connect to database and check for brute force attempts
	hesk_load_database_functions();
	hesk_dbConnect();
	hesk_limitBfAttempts();

if (!isset($_SESSION['a_iserror']))
{
	$_SESSION['a_iserror'] = array();
}

$hesk_error_buffer = array();

$otpsent=0;
$emailverified=0;

// If this is a POST method, check input
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// Verify security image
	
	if ($hesk_settings['secimg_use'])
	{
		// Using reCAPTCHA?
		if ($hesk_settings['recaptcha_use'])
		{
			require(HESK_PATH . 'inc/recaptcha/recaptchalib_v2.php');

			$resp = null;
			$reCaptcha = new ReCaptcha($hesk_settings['recaptcha_private_key']);

			// Was there a reCAPTCHA response?
			if ( isset($_POST["g-recaptcha-response"]) )
			{
				$resp = $reCaptcha->verifyResponse(hesk_getClientIP(), hesk_POST("g-recaptcha-response") );
			}

			if ($resp != null && $resp->success)
			{
				//$_SESSION['img_a_verified']=true;
			}
			else
			{
				$hesk_error_buffer['mysecnum']=$hesklang['recaptcha_error'];
			}
		}
		// Using PHP generated image
		else
		{
			$mysecnum = intval( hesk_POST('mysecnum', 0) );

			if ( empty($mysecnum) )
			{
				$hesk_error_buffer['mysecnum'] = $hesklang['sec_miss'];
			}
			else
			{
				require(HESK_PATH . 'inc/secimg.inc.php');
				$sc = new PJ_SecurityImage($hesk_settings['secimg_sum']);
				if ( isset($_SESSION['checksum']) && $sc->checkCode($mysecnum, $_SESSION['checksum']) )
				{
					//$_SESSION['img_a_verified'] = true;
				}
				else
				{
					$hesk_error_buffer['mysecnum'] = $hesklang['sec_wrng'];
				}
			}
		}
	}

	

	// Get email
	//$email = hesk_validateEmail( hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer['email']=$hesklang['enter_valid_email'];
	//$dealership = hesk_input( hesk_POST('dealership') );
	hesk_input( hesk_POST('dealership') ) or $hesk_error_buffer['dealership']="Enter dealership";
	hesk_input( hesk_POST('department') ) or $hesk_error_buffer['department']="Enter department";
	hesk_input( hesk_POST('name') ) or $hesk_error_buffer['name']="Enter name";
	hesk_input( hesk_POST('email') ) or $hesk_error_buffer['email']="Enter email";
	hesk_input( hesk_POST('user') ) or $hesk_error_buffer['user']="Enter username";
	hesk_input( hesk_POST('newpass') ) or $hesk_error_buffer['newpass']="Enter password";

	// Any errors?
	if (count($hesk_error_buffer)!=0)
	{
		$_SESSION['a_iserror'] = array_keys($hesk_error_buffer);

		$tmp = '';
		foreach ($hesk_error_buffer as $error)
		{
			$tmp .= "<li>$error</li>\n";
		}
		$hesk_error_buffer = $tmp;

		$hesk_error_buffer = $hesklang['pcer'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
		hesk_process_messages($hesk_error_buffer,'NOREDIRECT');
	}
	elseif( defined('HESK_DEMO') )
	{
		hesk_process_messages($hesklang['ddemo'],'NOREDIRECT');
	}
	else
	{
		
		
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $otp = '';
    for ($i = 0; $i < 6; $i++) {
        $otp .= $chars[random_int(0, strlen($chars) - 1)];
    }
		
		//hesk_sendOTP('amitsharma0007in@yopmail.com', $otp);
		
	//	die;
		
		/* start Mail send code */

// Email body with approval buttons
$message = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f6f6f6;
      margin: 0;
      padding: 0;
    }
    .email-container {
      max-width: 600px;
      margin: 30px auto;
      background-color: #ffffff;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .header {
      background-color: #0c783c;
      color: #ffffff;
      padding: 20px;
      text-align: center;
      font-size: 22px;
    }
    .body {
      padding: 30px;
      color: #333333;
      font-size: 16px;
    }
    .otp-box {
      background-color: #f1f1f1;
      padding: 15px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      letter-spacing: 4px;
      margin: 20px 0;
      border-radius: 5px;
    }
    .footer {
      background-color: #f6f6f6;
      padding: 15px;
      text-align: center;
      font-size: 13px;
      color: #999999;
    }
  </style>
</head>
<body>

<div class="email-container">
  <div class="header">
    Email Verification
  </div>
  <div class="body">
    <p>Dear User,</p>
    <p>We received a request to verify your email address. Please use the following OTP to complete the verification process:</p>

    <div class="otp-box">
     '.$otp.'
    </div>

    <p>This OTP is valid for the next 10 minutes. Please do not share it with anyone.</p>
    <p>If you didn’t request this, you can safely ignore this email.</p>

    <p>Thank you,<br>The Support Team</p>
  </div>
  <div class="footer">
    &copy; <?php echo date("Y"); ?> Rafih Portal. All rights reserved.
  </div>
</div>

</body>
</html>
';

    $subject = "Rafih Portal Request - Email Verification Code";
    // Headers
    $headers = "From: request@rafihportal.com\r\n";
    $headers .= "Reply-To: support@rafihportal.com \r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

	 $to=$_POST['email'];
$otpsent=1;
		
		hesk_sendOTP($to, $otp);
		
if (mail($to, $subject, $message, $headers)) {
	
   // echo "<div class='alert alert-success'>Form submitted successfully. We will get back to you soon.</div>";
} 
	
		/*end Mail send Code 
		
		
		
		
		
		hesk_dbQuery("INSERT INTO `".hesk_dbEscape($hesk_settings['db_pfix'])."users` (
		`user`,
		`pass`,
		`name`,
		`email`,
		`dealership`,
		`department`,
		`activated`
		) VALUES (
		'".hesk_dbEscape($_POST['user'])."',
		'".hesk_dbEscape(password_hash($_POST['newpass'],PASSWORD_DEFAULT))."',
		'".hesk_dbEscape($_POST['name'])."',
		'".hesk_dbEscape($_POST['email'])."',
		'".hesk_dbEscape($_POST['dealership'])."',
		'".hesk_dbEscape($_POST['department'])."',
		'1'
		)" );
		
		hesk_notifyUserRegistered($_POST['user']);
		
		hesk_process_messages("Your are registered with Rafih Portal. You will get an email when your account is activated!",'NOREDIRECT','SUCCESS'); */
		

	}
}

// Tell header to load reCaptcha API if needed
if ($hesk_settings['recaptcha_use'])
{
	define('RECAPTCHA',1);
}

$hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' .$hesklang['passr'];
require_once(HESK_PATH . 'inc/header.inc.php');
$login_wrapper = true;

$dealerships = array();
$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."dealerships` ");
while ($row=hesk_dbFetchAssoc($res))
{
	$dealerships[] = $row['dealership'];
}
//echo "<pre>";
//print_r($_SESSION);exit;
?>
<div class="wrapper login">
<main class="main">
    <div class="reg__wrap">
        <div class="reg__image">
            <div class="bg-absolute"><img src="<?php echo HESK_PATH; ?>img/hero-bg.png" alt="Hesk" /></div>
        </div>
        <div class="reg__section reset-password">
            <div class="reg__box">
                <?php if ($show_sent_email_message): ?>
                    <h2 class="reg__heading delete">
                        <?php echo $hesklang['password_reset_check_your_email']; ?>
                    </h2>
                    <div class="reset-password__text">
                        <?php echo $hesklang['password_reset_link_sent']; ?>
                    </div>
                <?php else: 
				
				
				?>
                    <h2 class="reg__heading delete">
                        <?php echo "Register to Rafih Portal!";//echo $hesklang['reset_your_password']; ?>
                    </h2>
                    <div class="reset-password__text otpblock"><?php 
						if($otpsent==1){
							
							echo "OTP sent to your email successfully. Please check and verify your email ID.";//$hesklang['reset_password_instructions']; 
						}
						else{
						echo "Register yourself by filling the form below.";//$hesklang['reset_password_instructions']; 
						}
						
						?></div>
                    <div style="margin-right: -24px; margin-left: -16px">
                        <?php
                        /* This will handle error, success and notice messages */
                        hesk_handle_messages();
                        ?>
                    </div>
				
				
				<?php 
				if($otpsent == 0 ){ ?> 
				
				
				
				
                    <!-- To activate the error add the class "invalid" to the form -->
                    <form action="register.php" class="form <?php echo isset($_SESSION['a_iserror']) && count($_SESSION['a_iserror']) ? 'invalid' : ''; ?>" method="post" name="form1" id="form1" novalidate onsubmit="return validationForm()" class="otpblock">
				<!--<form name="form1" method="post" action="manage_users.php" class="form <?php //echo hesk_SESSION(array('userdata','errors')) ? 'invalid' : ''; ?>">-->
					<input type="hidden" name="coming_from" value="indexpage">
						
						<div class="form-group">
							<label for="prof_dealership"><?php echo "Dealership";//$hesklang['real_name']; ?></label>
							<!--<input type="text" class="form-control <?php //echo in_array('dealership', $errors) ? 'isError' : ''; ?>" id="prof_dealership" name="dealership" maxlength="50"
								   value="<?php //echo $_SESSION[$session_array]['dealership']; ?>">-->
							 <select name="dealership" id="prof_dealership" class="form-control">
								 <option value="">Select</option>
								 <?php foreach ($dealerships as $dealership): ?>
                            <option value="<?php echo $dealership; ?>" <?php if ($_SESSION[$session_array]['dealership'] == $dealership) {echo 'selected';} ?>>
                                <?php echo $dealership; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
						</div>
						<div class="form-group">
							<label for="prof_department"><?php echo "Department";//$hesklang['email']; ?></label>
							<input type="text" class="form-control <?php echo in_array('department', $errors) ? 'isError' : ''; ?>" name="department" maxlength="50" id="prof_department"
								   value="<?php echo $_SESSION[$session_array]['department']; ?>">
						</div>
						
						<div class="form-group">
							<label for="prof_name"><?php echo "Name";//$hesklang['real_name']; ?></label>
							<input type="text" class="form-control <?php echo in_array('name', $errors) ? 'isError' : ''; ?>" id="prof_name" name="name" maxlength="50"
								   value="<?php echo $_SESSION[$session_array]['name']; ?>">
						</div>
						
						<div class="form-group">
							<label for="prof_email"><?php echo $hesklang['email']; ?></label>
							<input type="email" class="form-control <?php echo in_array('email', $errors) ? 'isError' : ''; ?>" name="email" maxlength="255" id="prof_email"
								   value="<?php echo $_SESSION[$session_array]['email']; ?>" required  >
						</div>
						
						<div class="form-group">
							<label for="prof_user"><?php echo $hesklang['username']; ?></label>
							<input type="text" class="form-control <?php echo in_array('user', $errors) ? 'isError' : ''; ?>" name="user" autocomplete="off" id="prof_user" maxlength="20"
								   value="<?php echo $_SESSION[$session_array]['user']; ?>">
						</div>
						
						<div class="form-group">
							<label for="prof_newpass"><?php echo $hesklang['pass']; ?></label>
							<input type="password" id="prof_newpass" name="newpass" autocomplete="off" class="form-control <?php echo in_array('passwords', $errors) ? 'isError' : ''; ?>"
								   value="<?php echo isset($_SESSION[$session_array]['cleanpass']) ? $_SESSION[$session_array]['cleanpass'] : ''; ?>"
								   onkeyup="hesk_checkPassword(this.value)">
						</div>
						<!--<div class="form-group">
							<label for="prof_newpass2"><?php echo $hesklang['confirm_pass']; ?></label>
							<input type="password" class="form-control <?php echo in_array('passwords', $errors) ? 'isError' : ''; ?>" id="prof_newpass2" name="newpass2" autocomplete="off"
								   value="<?php echo isset($_SESSION[$session_array]['cleanpass']) ? $_SESSION[$session_array]['cleanpass'] : ''; ?>">
						</div>-->
						<!--<div class="form-group">
							<label><?php //echo $hesklang['pwdst']; ?></label>
							<div style="border: 1px solid #d4d6e3; width: 100%; height: 14px">
								<div id="progressBar" style="font-size: 1px; height: 12px; width: 0px; border: none;">
								</div>
							</div>
						</div>-->

						
                        <?php 
						
						if ($hesk_settings['secimg_use'] && $hesk_settings['recaptcha_use'] != 1) {
                            if ($hesk_settings['recaptcha_use'] == 2) { ?>
                                <div class="g-recaptcha" data-sitekey="<?php echo $hesk_settings['recaptcha_public_key']; ?>"></div>
                            <?php } else {
                                $cls = in_array('mysecnum',$_SESSION['a_iserror']) ? ' class="form-control isError" ' : ' class="form-control" ';

                                echo '<div class="form-group"><label>'.$hesklang['sec_enter'].'</label><img src="'.HESK_PATH.'print_sec_img.php?'.rand(10000,99999).'" width="150" height="40" alt="'.$hesklang['sec_img'].'" title="'.$hesklang['sec_img'].'" border="1" name="secimg" style="vertical-align:middle" /> '.
                                    '<a style="vertical-align: middle; display: inline" class="btn btn-refresh" href="javascript:" onclick="document.form1.secimg.src=\''.HESK_PATH.'print_sec_img.php?\'+ ( Math.floor((90000)*Math.random()) + 10000);">
                                            <svg class="icon icon-refresh">
                                                <use xlink:href="' . HESK_PATH . 'img/sprite.svg#icon-refresh"></use>
                                            </svg>
                                         </a>'.
                                    '<br><br><input type="text" name="mysecnum" size="20" maxlength="5" autocomplete="off" '.$cls.'></div>';
                            }
                        } 
						
						?>
                        <div class="form__submit">
                            <button class="btn btn-full" type="submit" id="recaptcha-submitkkk"><?php echo "Register";//$hesklang['passs']; ?></button>
                        </div>
                        <?php
                        // Use Invisible reCAPTCHA?
                        if ($hesk_settings['secimg_use'] && $hesk_settings['recaptcha_use'] == 1)
                        {
                            ?>
                            <div class="g-recaptcha" data-sitekey="<?php echo $hesk_settings['recaptcha_public_key']; ?>" data-bind="recaptcha-submit" data-callback="recaptcha_submitForm"></div>
                            <?php
                        }
                        ?>
                    </form>
				
				<?php  }
				
				if($otpsent == 1 ){ 
					
				?>
				 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    .container {
      max-width: 400px;
      margin: 50px auto;
    }
    
  </style>

<div class="container">
  <h4 class="mb-4 text-center otpblock">Email OTP Verification</h4>
  <form id="otpForm" class="otpblock" >
    
   

    <div id="otpSection" class="form-group otp-box">
      <label for="otp">Enter OTP <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="otp" maxlength="6" required>
      <div class="invalid-feedback">Please enter the OTP sent to your email.</div>
		 <button type="button" onClick="validateForm();" class="btn btn-success btn-block mt-3">Verify OTP</button>
    </div>
 
  
  <input type="hidden" id="dealership" name="dealership" value="<?php echo $_POST['dealership']; ?>">
  <input type="hidden" id="department" name="department" value="<?php echo $_POST['department']; ?>">
  <input type="hidden" id="name" name="name" value="<?php echo $_POST['name']; ?>">
  <input type="hidden" id="email" name="email" value="<?php echo $_POST['email']; ?>">
  <input type="hidden" id="user" name="user" value="<?php echo $_POST['user']; ?>">
  <input type="hidden" id="newpass" name="newpass" value="<?php password_hash($_POST['newpass'],PASSWORD_DEFAULT); ?>">
  </form>

<div id="result"></div>
</div>
				
				
				<?php 
				}
				
				 endif; ?>
                <div class="reg__footer">
                    <a href="index.php" class="link"><?php echo $hesklang['back_to_login']; ?></a>
					<br>
					<!--<a href="https://old.rafihportal.com/" class="link" target="_blank"><?php echo "Switch to old portal"; ?></a>-->
                </div>
            </div>
        </div>
    </div>
	<span id="genratedotp" style="display:none;"><?php echo $otp ?></span>
    <script>
	function validateForm() {
  const enteredOtp = document.getElementById('otp').value;
  const generatedOTP = document.getElementById('genratedotp').textContent.trim();

  if (enteredOtp === '') {
    document.getElementById('otp').classList.add('is-invalid');
    return false;
  } else {
    document.getElementById('otp').classList.remove('is-invalid');
  }

  if (enteredOtp === generatedOTP) {
    document.getElementById('result').innerHTML = `<div class="alert alert-success">OTP verified successfully!</div>`;
	   const formData = {
    dealership: $('#dealership').val(),
    department: $('#department').val(),
    name: $('#name').val(),
    email: $('#email').val(),
    user: $('#user').val(),
    newpass: $('#newpass').val(),
    otp: $('#otp').val()
  };
	  
	  
	  $.ajax({
    url: '../ajaxapi/index.php',
    method: 'POST',
    data: formData,
    success: function (res) {
      document.getElementById('result').innerHTML = `<div class="alert alert-success">${res}</div>`;
	$('.otpblock').hide();	
    },
    error: function () {
      document.getElementById('result').innerHTML = `<div class="alert alert-danger">Registration failed. Try again.</div>`;
    }
  });
  } else {
	  
	   	  
    document.getElementById('result').innerHTML = `<div class="alert alert-danger">Invalid OTP. Please try again.</div>`;
  }

  return false; // Prevent actual form submission
}
		
    $(() => {
        $('form :visible[class*=isError]:first').focus();
    })
    </script>
<?php
// Clean session errors
hesk_cleanSessionVars('a_iserror');
hesk_cleanSessionVars('img_a_verified');
	
function generateAlphanumericOTP($length = 6) {
    
    return $otp;
}

// Print footer
require_once(HESK_PATH . 'inc/footer.inc.php');
?>
