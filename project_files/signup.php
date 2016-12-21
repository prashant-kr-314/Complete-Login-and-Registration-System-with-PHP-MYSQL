<?php 
include_once "resource/Database.php";		// soule purpose of this is to make the connection to the database and if there is any error(exception) then it will show it.
// well we have done that in the index page also so, well if you have an error it will show at that time BUT NOTE : you have to add this everywhere you try to intereact to the database (any where you wanna use SQL statements)

// process the form
include_once "resource/utilities.php";

if (isset($_POST['signup_sbt'])) { ## does both validation and data processing 

// form validation BEGINS:==================================================================================================

	#initialize an array to store any error message from the form
	$form_errors = array();
	
	#form validation
	$required_fields = array('username','email','password'); // these are the name of the fields in the html form which forms the key in the associative array (here $_POST)

	//call the function to check empty field and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_empty_fields($required_fields));

    //Fields that requires checking for minimum length ,HERE we declared an array from ourself, here we are defining the minimum length of the element with respect to its key, so we can excess through its key and chek its length.
    $fields_to_check_length = array('username' => 4, 'password' => 6, 'email'=>12);

    //call the function to check minimum required length and merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_min_length($fields_to_check_length));

    //email validation / merge the return data into form_error array
    $form_errors = array_merge($form_errors, check_email($_POST));

// form validation ENDS:==================================================================================================

    #############################################   FORM PROCESSING AND ERROR SHOWING   ####################################

	# check if the error array is empty or not , if yes then process the form data, and insert record
	if (empty($form_errors)) {
		$username = $_POST['username'];	//the method post is acutally an associative array of the value we passed 
		$password = $_POST['password'];
		$hashed_password = password_hash($password,PASSWORD_DEFAULT); # immediately hassing the password we got
		$email = $_POST['email'];

		# NOW , BEFORE CREATING THE USER (ie. entering the user data into the database) WE HAVE TO CHECK WHETHER THIS USERNAME IS TAKEN OR NOT IF IT DOES ,THEN SHOW MESSAGE, "sorry this username is already taken"

 		# checkDuplicasy($input, $columnName, $databaseName, $tableName, $db)
		$arrayReturned = checkDuplicasy($username, 'username', 'register', 'users', $db);//returns an array of 'status' and 																				'message' key and their value
		if ($arrayReturned['status'] == false ) {//ie no duplicasy for username found in the database, 
			#checking email duplicasy
			$arrayReturned = checkDuplicasy($email, 'email', 'register', 'users', $db);
			if ($arrayReturned['status'] == false ) {//ie no duplicasy for email found in the database	
				try{
					$sqlInsert = "INSERT INTO register.users (username, password, email, join_date) 
									VALUES  (:username, :password, :email, now() ) ";

					$statement = $db->prepare($sqlInsert);
					$statement->execute( array(':username'=>$username,':password'=>$hashed_password,':email'=>$email ) );

					if($statement->rowcount()==1){ # ie if one row is changed theb ...
						$result = flashMessage("Registration Successfull !", 'green');
					}else{
						$result = flashMessage("Signup unsuccessfull");
					}

				}catch(PDOException $ex){ // thsi will be the error from the conection and not from the user
					$result = flashMessage("An error occured: WHILE INSERTING THE FORM DATA INTO THE DATABASE==>".$ex->getMessage());
				}
			}else{	$result =  flashMessage($arrayReturned['message']);	}

		}else{# here we dont care what is the status of the array( either true OR exception), we have to print the 				message in any case, SO
			$result =  flashMessage($arrayReturned['message']);
		}
	} // so if there will be an error then it will be checked and displayed in the html BODY element
}

?>

<!-- **********************************************   HTML PART   *******************************************************-->
<!-- <body> is already into the header file-->
<?php 	$page_title = 'Signup form';
		include_once 'partials/headers.php'; 	?>

<!----  <body> is already into the header file  -- -->
<h2>Sign-up Form </h2><hr/>
<div class="container">
	<?php  if (isset($result) ) echo $result;  ?>	
	<?php if (!empty($form_errors) )  echo show_errors($form_errors);  ?>
</div>

<!--                                      ---  all have id ending with 2  --                                 -->

<div class="container" >

	<section class="col col-lg-7" style="border:2px solid red";>

		<form action="" method="post" >
			<div class="form-group">
    			<label for="emailField2">E-mail:</label>
    			<input type="text" class="form-control" name="email"  id="emailField2" placeholder="E-mail">
  			</div>
  			<div class="form-group" style="border:2px dotted red";>
    			<label for="usernameField2">Username:</label>
    			<input type="text" class="form-control" name="username"  id="usernameField2" placeholder="Username">
  			</div>
  			<div class="form-group">
    			<label for="password2">Password:</label>
    			<input type="password" class="form-control" name="password" id="password2" placeholder="Password">
  			</div>
  			<button type="submit" class="btn btn-primary pull-right" name="signup_sbt">Sign up</button>
		</form>
	</section>
	<p><a href="index.php">Back</a></p>
</div>

<?php  include_once 'partials/footers.php'; ?>
<!----  </body> is already into the fppter file  -- -->
<!-- **********************************************   HTML PART   *******************************************************-->