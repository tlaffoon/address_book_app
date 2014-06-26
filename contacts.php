<?php

require('dbc.php');

function getContacts($dbc, $contactsPerPage, $offset) {
    return $dbc->query("SELECT * FROM contacts LIMIT $contactsPerPage OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
}

function countContacts($dbc) {
	return $dbc->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
}

function insertContact($dbc, $name, $phone) {
	$query = 'INSERT INTO contacts (contact_name, phone) VALUES (:name, :phone);';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':name', $name, PDO::PARAM_STR);
	$stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
	$stmt->execute();
	return "<p>Inserted ID: " . $dbc->lastInsertId() . "</p>";
}

function removeContact($dbc, $id) {
	$query = 'DELETE FROM contacts WHERE id = :id';
	$stmt = $dbc->prepare($query);
	$stmt->bindValue(':id', $id, PDO::PARAM_STR);
	$stmt->execute();
}

$contactsPerPage = 5;
$pageID = (empty($_GET)) ? 1 : $_GET['page'];
$maxPages = ceil(countContacts($dbc) / $contactsPerPage);
$offset = ($pageID * $contactsPerPage) - $contactsPerPage;
$contacts = getContacts($dbc, $contactsPerPage, $offset);

if (!empty($_POST)) {

	$name = $_POST['name'];
	$phone = $_POST['phone'];

	try {
		if (!isset($name) || !isset($phone)) {
			throw new Exception("Please include all fields.", 1);
		}

	insertContact($dbc, $name, $phone);
	header('Location: http://addr.dev/');

	} 

	catch (Exception $e) {
		echo "Error: " . $e->getMessage(); // alter to helptext beneath add contact form
	}
}

if (!empty($_POST['remove'])) {
	$id = $_POST['remove'];
	removeContact($dbc, $id);
	header('Location: http://addr.dev/');
}

?>
<html>
<head>
	<title>Address Book: Contacts</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<style type="text/css">
	.spaced {
			margin-bottom: 5px;
	}
	</style>
	</head>

<body>

	<div class="container col-md-8">

	<h1>Address Book: Contacts</h1>

		<? // Begin Pagination ?>
		<? if ($pageID > 1) : ?>
			<a class="btn btn-small btn-default pull-left spaced" href="?page=<?= ($pageID - 1) ?>"> Previous </a>
		<? endif ?>

		<? if ($pageID < $maxPages) : ?>
			<a class="btn btn-small btn-default pull-right spaced" href="?page=<?= ($pageID + 1) ?>"> Next </a>
		<? endif ?>
		<? // End Pagination ?>

		<table class="table table-striped">
			<tr>
				<th>Id</th>
				<th>Name</th>
				<th>Phone</th>
				<th># of Address</th>
				<th>Actions</th>
			</tr>
			<?php foreach ($contacts as $contact): ?>
				<tr>
					<td><?= $contact['id'] ?></td>
					<td><?= $contact['contact_name'] ?></td>
					<td><?= $contact['phone'] ?></td>
					<td><? // # of addresses associated with this name?></td>
					<td>
						<a class="btn btn-small btn-default" href="contact_addresses.php?contact_id=<?= $contact['id'] ?>">View</a>
						<button class="btn btn-small btn-danger btn-remove" data-contact="<?= $contact['id'] ?>">Remove</button>
					</td>
				</tr>
			<?php endforeach ?>
		</table>

		<form class="form-inline" role="form" action="contacts.php" method="POST">
			<div class="form-group">
				<label class="sr-only" for="name">Name</label>
				<input type="text" name="name" id="name" class="form-control" placeholder="Name">
			</div>
			<div class="form-group">
				<label class="sr-only" for="phone">Phone #</label>
				<input type="text" name="phone" id="phone" class="form-control" placeholder="#">
			</div>
			<button type="submit" class="btn btn-default btn-success">Add Contact</button> 
		</form>
	</div> <? // end main container ?>


<form id="remove-form" action="contacts.php" method="post">
	<input id="remove-id" type="hidden" name="remove" value="">
</form>

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
 <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script>

$('.btn-remove').click(function () {
	var contactId = $(this).data('contact');
	if (confirm('Are you sure you want to remove contact ' + contactId + '?')) {
		$('#remove-id').val(contactId);
		$('#remove-form').submit();
	}
});

</script>

</body>
</html>