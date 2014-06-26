<?php

require('dbc.php');

function getContactName($dbc, $contact_id) {
    $query = 'SELECT contact_name FROM contacts WHERE id = :id';
    $stmt = $dbc->prepare($query);
    $stmt->bindValue(':id', $contact_id, PDO::PARAM_STR);
    return $stmt->execute();
}

function getContactAddresses($dbc, $contact_id, $addrPerPage, $offset) {
    return $dbc->query("SELECT * FROM addresses")->fetchAll(PDO::FETCH_ASSOC);
}

function getAllExistingAddress($dbc) {
    return $dbc->query("SELECT * FROM addresses")->fetchAll(PDO::FETCH_ASSOC);
}

function countAddresses($dbc) {
	return $dbc->query('SELECT COUNT(*) FROM addresses')->fetchColumn();
}

function insertAddress($dbc, $street, $city, $state, $zip) {
	$query = 'INSERT INTO addresses (street, city, state, zip) VALUES (:street, :city, :state, :zip);';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':street', $street, PDO::PARAM_STR);
	$stmt->bindValue(':city', $city, PDO::PARAM_STR);
	$stmt->bindValue(':state', $state, PDO::PARAM_STR);
	$stmt->bindValue(':zip', $zip, PDO::PARAM_INT);
	$stmt->execute();
	// return "<p>Inserted ID: " . $dbc->lastInsertId() . "</p>";
}

function detachAddress($dbc, $address_id, $contact_id) {
	$query = 'DELETE FROM map WHERE ... ';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':address_id', $address_id, PDO::PARAM_STR);
	$stmt->bindValue(':contact_id', $contact_id, PDO::PARAM_STR);
	$stmt->execute();
}

function deleteAddress($dbc, $address_id) {
	$query = 'DELETE FROM addresses WHERE id = :id';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':id', $address_id, PDO::PARAM_STR);
	$stmt->execute();
}

$addrPerPage = 5;
// 
$maxPages = ceil(countAddresses($dbc) / $addrPerPage);

// Check and process GET 

if (!empty($_GET)) {

	// var_dump($_GET);

	if (isset($_GET['page'])) {
		$pageID = $_GET['page'];
	}

	else {
		$pageID = 1;
	}
	
	$offset = ($pageID * $addrPerPage) - $addrPerPage;

	if (isset($_GET['contact_id'])) {
		$contact_id = $_GET['contact_id'];
		$contactName = getContactName($dbc, $contact_id);
		$contactAddresses = getContactAddresses($dbc, $contact_id, $addrPerPage, $offset);		
	}
}

else {
	$pageID = 1;
	$offset = ($pageID * $addrPerPage) - $addrPerPage;
	$contactName = 'Default';
	$contactAddresses = array();
}

// Define Dropdown Choices for Attach/Delete Forms
$dropdownAddresses = getAllExistingAddress($dbc);

// Check and process POST

if (!empty($_POST)) {
	// var_dump($_POST);

	if (isset($_POST['delete_id'])) {
		$delete_id = $_POST['delete_id'];
		deleteAddress($dbc, $delete_id);
		header('Location: http://addr.dev/contact_addresses.php');
	}

	if (isset($_POST['detach_id'])) {
		$detach_id = $_POST['detach_id'];
		//$contact_id = 
		detachAddress($dbc, $detach_id, $contact_id);
		header('Location: http://addr.dev/contact_addresses.php');
	}

	if (isset($street) && isset($city) && isset($state) && isset($zip)) {
		$street = $_POST['street'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];

		// need try/catch for input validation.

		insertAddress($dbc, $street, $city, $state, $zip);
		header('Location: http://addr.dev/contact_addresses.php');
	}
}

?>

<html>
<head>
	<title>Address Book: Contact Addresses</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<style type="text/css">
	.zero-pad {
		padding-left: 0px;
	}
	</style>
</head>
<body>

<div class="container">

	<h1>
		Address for Contact: <?= $contactName ?>
		<a href="http://addr.dev/" class="btn btn-default pull-right">Back to Contacts</a>
	</h1>

	<table class="table table-striped">
		<tr>
			<th>Street</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Actions</th>
		</tr>

		<?php foreach ($contactAddresses as $contactAddress): ?>
			<tr>
				<td><?= $contactAddress['street'] ?></td>
				<td><?= $contactAddress['city'] ?></td>
				<td><?= $contactAddress['state'] ?></td>
				<td><?= $contactAddress['zip'] ?></td>
				<td>
					<button class="btn btn-small btn-info btn-detach" data-detach="<? // $contactAddress['id'] ?>">Detach</button>
				</td>
			</tr>
		<?php endforeach ?>
	</table>

	<hr>

	<div class="col-md-6 zero-pad">
		<h4>Attach Existing Address</h4>
		<form class="form-inline" role="form" action="" method="POST">
			<div class="form-group">
				<select class="form-control" name="attach_id">
					<?php foreach ($dropdownAddresses as $address): ?>
						<option value="<?= $address['id'] ?>"> <?= "{$address['street']} {$address['city']}, {$address['state']} {$address['zip']}"  ?> </option>
					<?php endforeach ?>
				</select>
			</div>
			<button type="submit" class="btn btn-default btn-info" data-attach="<?= $address['id']?>">Attach</button>
		</form>
	</div>

	<div class="col-md-6 zero-pad">
		<h4>Delete Existing Address</h4>
		<form class="form-inline" role="form" action="" method="POST">
			<div class="form-group">
				<select class="form-control" name="delete_id">
					<?php foreach ($dropdownAddresses as $address): ?>
						<option value="<?= $address['id'] ?>"> <?= "{$address['street']} {$address['city']}, {$address['state']} {$address['zip']}"  ?> </option>
					<?php endforeach ?>
				</select>
			</div>
			<button type="submit" class="btn btn-danger btn-success btn-delete" data-delete="<?= $address['id'] ?>">Delete</button>
		</form>
	</div>

	<div class="clearfix"></div>

	<h4>Add New Address</h4>
	<form class="form-inline" role="form" action="" method="POST">
		<div class="form-group">
			<label class="sr-only" for="street">Street</label>
			<input type="text" name="street" id="street" class="form-control" placeholder="Street">
		</div>
		<div class="form-group">
			<label class="sr-only" for="city">City</label>
			<input type="text" name="city" id="city" class="form-control" placeholder="City">
		</div>
		<div class="form-group">
			<label class="sr-only" for="state">State</label>
			<input type="text" name="state" id="state" class="form-control" placeholder="State">
		</div>
		<div class="form-group">
			<label class="sr-only" for="zip">Zip</label>
			<input type="text" name="zip" id="zip" class="form-control" placeholder="Zip">
		</div>
		<button type="submit" class="btn btn-default btn-success">Add</button>
	</form>

	<!-- Detach Address Form -->
	<form id="detach-form" action="contact_addresses.php" method="post">
		<input id="detach-id" name="detach" type="hidden" value="">
	</form>
	
	<!-- Delete Address Form -->
	<form id="delete-form" action="contact_addresses.php" method="post">
		<input id="delete-id" name="delete" type="hidden" value="">
	</form>

</div>


<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
 <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script>

$('document').ready({

	$('.btn-detach').click(function () {
		var addressId = $(this).data('address');
		//if (confirm('Are you sure you want to detach address ' + addressId + '?')) {
			//$('#detach-id').val(addressId); // alter to dissociate address from current contact, rather than delete
			//$('#detach-form').submit();
		//}
	});

	$('.btn-delete').click(function () {
		var addressId = $(this).data('address');
		if (confirm('Are you sure you want to remove address ' + addressId + '?')) {
			$('#delete-id').val(addressId); // alter to dissociate address from current contact, rather than delete
			$('#delete-form').submit();
		}
	});



});



</script>

</body>
</html>