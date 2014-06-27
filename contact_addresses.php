<?php

// Add in-line editing for fields displayed in address book

require('dbc.php');

function getAllExistingAddress($dbc) {
    return $dbc->query("SELECT * FROM addresses;")->fetchAll(PDO::FETCH_ASSOC);
};

function countAddresses($dbc) {
	return $dbc->query('SELECT COUNT(*) FROM addresses;')->fetchColumn();
};

// Needs to have $contact_id sanitized with query prepare, keep getting syntax error.
function getContactName($dbc, $contact_id) {
    $query = "SELECT name FROM contacts WHERE id = $contact_id;";
    //$stmt = $dbc->prepare($query);
    //$stmt->bindValue(':id', $contact_id, PDO::PARAM_STR);
    //return $stmt->execute();
    return $dbc->query($query)->fetch(PDO::FETCH_ASSOC);
};

// Needs to have $contact_id sanitized with query prepare, keep getting syntax error.
function getContactAddresses($dbc, $contact_id, $addrPerPage, $offset) {
	$query = "SELECT a.id, a.street, a.city, a.zip, a.state
				FROM contacts_addresses ca
				JOIN contacts c on c.id = ca.contact_id
				JOIN addresses a on a.id = ca.address_id
				WHERE c.id = $contact_id;";
				// -- LIMIT :limit 
				// -- OFFSET :offset

	//$stmt = $dbc->prepare($query);
	//$stmt->bindValue(':id', $contact_id, PDO::PARAM_STR);
	//$stmt->bindValue(':limit', $addrPerPage, PDO::PARAM_STR);
	//$stmt->bindValue(':offset', $offset, PDO::PARAM_STR);
    return $dbc->query($query)->fetchAll(PDO::FETCH_ASSOC); // ->fetchAll(PDO::FETCH_ASSOC);
};

function insertAddress($dbc, $street, $city, $state, $zip) {
	$query = "INSERT INTO addresses (street, city, state, zip) VALUES (:street, :city, :state, :zip);";
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':street', $street, PDO::PARAM_STR);
	$stmt->bindValue(':city', $city, PDO::PARAM_STR);
	$stmt->bindValue(':state', $state, PDO::PARAM_STR);
	$stmt->bindValue(':zip', $zip, PDO::PARAM_INT);
	$stmt->execute();
	return "<p>Inserted ID: " . $dbc->lastInsertId() . "</p>";
};

function attachAddress($dbc, $contact_id, $address_id) {
	$query = "INSERT INTO contacts_addresses (contact_id, address_id) VALUES (:contact_id, :address_id);";
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
	$stmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
	$stmt->execute();
};


function detachAddress($dbc, $contact_id, $address_id) {
	$query = 'DELETE FROM contacts_addresses WHERE contact_id = :contact_id AND address_id = :address_id;';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
	$stmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
	$stmt->execute();
};

function deleteAddress($dbc, $address_id) {
	$query = 'DELETE FROM addresses WHERE id = :address_id';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
	$stmt->execute();

	// This query fails for some reason.
	// $query2 = 'DELETE FROM contacts_addresses WHERE address_id = :id';
	// $stmt = $dbc->prepare($query2);
	// $stmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
	// $stmt->execute();
};

$pageID = '1';  // Default Value
$addrPerPage = '5';  // Default Value
$maxPages = ceil(countAddresses($dbc) / $addrPerPage);

// Check and process GET
if (!empty($_GET)) {
	// var_dump($_GET);

	if (isset($_GET['page'])) {
		$pageID = $_GET['page'];
	}

	else {
	}
	
	$offset = ($pageID * $addrPerPage) - $addrPerPage;

	if (isset($_GET['contact_id'])) {
		$contact_id = $_GET['contact_id'];
		$contactName = getContactName($dbc, $contact_id);
		$contactAddresses = getContactAddresses($dbc, $contact_id, $addrPerPage, $offset);		
	}
}

else {
	$offset = ($pageID * $addrPerPage) - $addrPerPage;
	$contactName = array('name' => 'Default');
	$contactAddresses = array();
}

// Define Dropdown Choices for Attach/Delete Forms
$dropdownAddresses = getAllExistingAddress($dbc);

// Check and process POST
if (!empty($_POST)) {

	if (isset($_POST['delete_id'])) {
		$address_id = $_POST['delete_id'];
		deleteAddress($dbc, $address_id);
		header('Location: http://addr.dev/contact_addresses.php?contact_id=' . $contact_id);
	}

	if (isset($_POST['attach_id'])) {
		$address_id = $_POST['attach_id'];
		attachAddress($dbc, $contact_id, $address_id);
		header('Location: http://addr.dev/contact_addresses.php?contact_id=' . $contact_id);
	}

	if (isset($_POST['detach_id'])) {
		$address_id = $_POST['detach_id'];
		echo $address_id;
		detachAddress($dbc, $contact_id, $address_id);
		header('Location: http://addr.dev/contact_addresses.php?contact_id=' . $contact_id);
	}

	// need try/catch for input validation.
	if (isset($_POST['street']) && isset($_POST['city']) && isset($_POST['state']) && isset($_POST['zip'])) {
		$street = ucwords($_POST['street']);
		$city = ucwords($_POST['city']);
		$state = ucfirst($_POST['state']);  // need to uppercase all and change to two letter abbrev.
		$zip = $_POST['zip'];

		insertAddress($dbc, $street, $city, $state, $zip);
		header('Location: http://addr.dev/contact_addresses.php?contact_id=' . $contact_id);
	}
}

?>

<html>
<head>
	<title>Address Book: Contact Addresses</title>
	<link rel="stylesheet" href="./bootstrap/css/bootstrap.css">
	<style type="text/css">
	.zero-pad {
		padding-left: 0px;
		padding-right: 0px;
	}
	</style>
</head>
<body>

<div class="container">

	<h1>
		Address for Contact: <?= $contactName['name'] ?>
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
					<button class="btn btn-small btn-warning btn-detach" data-detach="<?= $contactAddress['id'] ?>">Detach</button>
				</td>
			</tr>
		<?php endforeach ?>
	</table>

	<div class="col-md-6 zero-pad">
		<h4>Attach Existing Address</h4>
		<form class="form-inline" role="form" action="" method="POST">
			<div class="form-group">
				<select class="form-control" name="attach_id">
					<?php foreach ($dropdownAddresses as $address): ?>
						<option value="<?= $address['id'] ?>"> <?= "{$address['street']} - {$address['city']}, {$address['state']} {$address['zip']}"  ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<button type="submit" class="btn btn-info btn-attach" data-attach="<?= $address['id']?>">Attach</button>
		</form>
	</div>

	<div class="col-md-6 zero-pad">
		<h4>Delete Existing Address</h4>
		<form class="form-inline" role="form" action="" method="POST">
			<div class="form-group">
				<select class="form-control" name="delete_id">
					<?php foreach ($dropdownAddresses as $address): ?>
						<option value="<?= $address['id'] ?>"> <?= "{$address['street']} - {$address['city']}, {$address['state']} {$address['zip']}"  ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<button type="submit" class="btn btn-danger btn-delete" data-delete="<?= $address['id'] ?>">Delete</button>
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

	<!-- Attach Address Form -->
	<form id="attach-form" action="" method="post">
		<input id="attach-id" name="attach" type="hidden" value="">
	</form>

	<!-- Detach Address Form -->
	<form id="detach-form" action="" method="post">
		<input id="detach-id" name="detach" type="hidden" value="">
	</form>
	
	<!-- Delete Address Form -->
	<form id="delete-form" action="" method="post">
		<input id="delete-id" name="delete" type="hidden" value="">
	</form>

</div>


<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script>
	console.log('Document Loaded.');

	$('.btn-attach').click(function () {
		var addressId = $(this).data('attach');
			$('#attach-id').val(addressId);
			$('#attach-form').submit();
	});

	$('.btn-detach').click(function () {
		var addressId = $(this).data('detach');
			$('#detach-id').val(addressId);
			$('#detach-form').submit();
	});

	$('.btn-delete').click(function () {
		var addressId = $(this).data('delete');
		if (confirm('Are you sure you want to remove address ' + addressId + '?')) {
			$('#delete-id').val(addressId);
			$('#delete-form').submit();
		}

		else {
			// redirect to current contact_id page without executing delete query
		}
	});
</script>

</body>
</html>