<?php require('includes/config.php');

//if logged in redirect to members page
if( $user->is_logged_in() ){ header('Location: memberpage.php'); }

//if form has been submitted process it
if(isset($_POST['submit'])){

	//very basic validation
	if(strlen($_POST['username']) < 3){
		$error[] = 'Username is too short.';
	} else {
		$stmt = $db->prepare('SELECT username FROM members WHERE username = :username');
		$stmt->execute(array(':username' => $_POST['username']));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!empty($row['username'])){
			$error[] = 'Username provided is already in use.';
		}
	}

	if(strlen($_POST['password']) < 3){
		$error[] = 'Password is too short.';
	}

	if(strlen($_POST['passwordConfirm']) < 3){
		$error[] = 'Confirm password is too short.';
	}

	if($_POST['password'] != $_POST['passwordConfirm']){
		$error[] = 'Passwords do not match.';
	}

	if(!is_string($_POST['first']) && !preg_match("/^[a-zA-Z]+$/", $_POST['first'])){
		$error[] = 'Invalid first name.';
	}

	if(!is_string($_POST['last']) && !preg_match("/^[a-zA-Z]+$/", $_POST['last'])){
		$error[] = 'Invalid last name.';
	}

	if(!preg_match('/^[a-z0-9- ]+$/i', $_POST['addr1'])){
		$error[] = 'Please enter in valid address line 1 value.';
	}

	if(!preg_match('/^[a-z0-9- ]+$/i', $_POST['addr2']) && !is_null($_POST['addr2'])){
		$error[] = 'Please enter valid address line 2 value.';
	}

	if(!preg_match('/^[a-zA-Z]+(?:[\s-][a-zA-Z]+)*$/', $_POST['city'])){
		$error[] = 'Please enter valid city name.';
	}
	$us_state_abbrevs = array('AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'GU', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AE', 'AA', 'AP');
	if(!in_array($_POST['state'], $us_state_abbrevs)){
		$error[] = 'Please enter valid state ID.';
	}

	if(!preg_match('/^\d{5}$/', $_POST['zip'])){
		$error[] = 'Please enter valid zip code as #####.';
	}

	$countries_list = array('US', 'United States');

	if(!in_array($_POST['country'], $countries_list)){
		$error[] = 'Please enter either "United States" or "US."';
	}

	//email validation
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
	    $error[] = 'Please enter a valid email address';
	} else {
		$stmt = $db->prepare('SELECT email FROM members WHERE email = :email');
		$stmt->execute(array(':email' => $_POST['email']));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!empty($row['email'])){
			$error[] = 'Email provided is already in use.';
		}

	}


	//if no errors have been created carry on
	if(!isset($error)){

		//hash the password
		$hashedpassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);

		//create the activasion code
		$activasion = md5(uniqid(rand(),true));

		try {

			//insert into database with a prepared statement
			$stmt = $db->prepare('INSERT INTO members (first,last,addr1,addr2,city,state,zip,country,username,password,email,active) VALUES (:first,:last,:addr1,:addr2,:city,:state,:zip,:country,:username,:password,:email,:active)');
			$stmt->execute(array(
				':first' => $_POST['first'],
				':last' => $_POST['last'],
				':addr1' => $_POST['addr1'],
				':addr2' => $_POST['addr2'],
				':city' => $_POST['city'],
				':state' => $_POST['state'],
				':zip' => $_POST['zip'],
				':country' => $_POST['country'],
				':username' => $_POST['username'],
				':password' => $hashedpassword,
				':email' => $_POST['email'],
				':active' => $activasion
			));
			$id = $db->lastInsertId('memberID');

			//send email
			$to = $_POST['email'];
			$subject = "Registration Confirmation";
			$body = "<p>Thank you for registering at demo site.</p>
			<p>To activate your account, please click on this link: <a href='".DIR."activate.php?x=$id&y=$activasion'>".DIR."activate.php?x=$id&y=$activasion</a></p>
			<p>Regards Site Admin</p>";

			$mail = new Mail();
			$mail->setFrom(SITEEMAIL);
			$mail->addAddress($to);
			$mail->subject($subject);
			$mail->body($body);
			$mail->send();

			//redirect to index page
			header('Location: index.php?action=joined');
			exit;

		//else catch the exception and show the error.
		} catch(PDOException $e) {
		    $error[] = $e->getMessage();
		}

	}

}

//define page title
$title = 'HelloWorld Demo';

//include header template
require('layout/header.php');
?>


<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
			<form role="form" method="post" action="" autocomplete="off">
				<h2>Please Sign Up</h2>
				<p>Already a member? <a href='login.php'>Login</a></p>
				<hr>

				<?php
				//check for any errors
				if(isset($error)){
					foreach($error as $error){
						echo '<p class="bg-danger">'.$error.'</p>';
					}
				}

				//if action is joined show sucess
				if(isset($_GET['action']) && $_GET['action'] == 'joined'){
					echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
				}
				?>

				<div class="form-group">
					<input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo $_POST['username']; } ?>" tabindex="1">
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="first" id="first" class="form-control input-lg" placeholder="First Name" value="<?php if(isset($error)){ echo $_POST['first']; } ?>" tabindex="2">
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="last" id="last" class="form-control input-lg" placeholder="Last Name" value="<?php if(isset($error)){ echo $_POST['last']; } ?>" tabindex="3">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="addr1" id="addr1" class="form-control input-lg" placeholder="Address Line 1" value="<?php if(isset($error)){ echo $_POST['addr1']; } ?>" tabindex="4">
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="addr2" id="addr2" class="form-control input-lg" placeholder="Address Line 2" value="<?php if(isset($error)){ echo $_POST['addr2']; } ?>" tabindex="5">
						</div>
					</div>
				</div>
				<div class="form-group">
					<input type="text" name="city" id="city" class="form-control input-lg" placeholder="City e.g. Seattle" value="<?php if(isset($error)){ echo $_POST['city']; } ?>" tabindex="6">
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="state" id="state" class="form-control input-lg" placeholder="State ID e.g. WA" value="<?php if(isset($error)){ echo $_POST['state']; } ?>" tabindex="7">
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="zip" id="zip" class="form-control input-lg" placeholder="5 Digit Zip" value="<?php if(isset($error)){ echo $_POST['zip']; } ?>" tabindex="8">
						</div>
					</div>
				</div>
				<div class="form-group">
					<input type="text" name="country" id="country" class="form-control input-lg" placeholder="Country e.g. United States" value="<?php if(isset($error)){ echo $_POST['country']; } ?>" tabindex="9">
				</div>
				<div class="form-group">
					<input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php if(isset($error)){ echo $_POST['email']; } ?>" tabindex="10">
				</div>
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="11">
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="12">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="13"></div>
				</div>
			</form>
		</div>
	</div>

</div>

<?php
//include header template
require('layout/footer.php');
?>
