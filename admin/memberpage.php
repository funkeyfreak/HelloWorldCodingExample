<?php require('includes/config.php');

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); }

//define page title
$title = 'Admin Page';

//include header template
require('layout/header.php');
?>

<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

				<h2>Admin only page - Welcome <?php echo $_SESSION['username']; ?></h2>
				<table class="table">
					<thead>
						<tr>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Address 1</th>
							<th>Address 2</th>
							<th>City</th>
							<th>State</th>
							<th>Zip</th>
							<th>Country</th>
							<th>User Name</th>
							<th>Email</th>
							<th>Activated</th>
						</tr>
					</thead>
					<?php
						$stmt = $db->prepare('SELECT first, last, addr1, addr2, city, state, zip, country, username, email, active, dateAdded FROM members');
						$stmt->execute();
						while($record = $stmt->fetch()){
							echo "<tr>";
							echo "<td>".$record["first"]."</td>";
							echo "<td>".$record["last"]."</td>";
							echo "<td>".$record["addr1"]."</td>";
							echo "<td>".$record["addr2"]."</td>";
							echo "<td>".$record["city"]."</td>";
							echo "<td>".$record["state"]."</td>";
							echo "<td>".$record["zip"]."</td>";
							echo "<td>".$record["country"]."</td>";
							echo "<td>".$record["username"]."</td>";
							echo "<td>".$record["email"]."</td>";
							echo "<td>".$record["active"]."</td>";
							echo "</tr>";
						}
					?>
				</table>
				<p><a href='logout.php'>Logout</a></p>

		</div>
	</div>


</div>

<?php
//include header template
require('layout/footer.php');
?>
