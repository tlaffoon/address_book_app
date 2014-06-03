<?php

function readCSV($filename = './data/address-book.csv') {
	$address_book = [];
	$handle = fopen($filename, 'r');
	while(!feof($handle)) {
		$row = fgetcsv($handle);
		if (!empty($row)) {
			$address_book[] = $row;
		}
	}
	return $address_book;
}

function writeCSV($address_book, $filename = './data/address-book.csv') {
	$handle = fopen($filename, 'w');
	foreach ($address_book as $fields) {
		fputcsv($handle, $fields);
	}
	fclose($handle);
}

function checkPOST($post) {
	// check captured post data for all fields completed.
	if (!empty($post)) {
		foreach ($post as $key => $value) {
			if (!empty($post[$key]) && isset($post[$key])) {
				return true;
			} 
		}
	}
	return false;
}

function removeEntry($entryID, $array) {
	unset($array[$entryID]);
	return array_values($array);
}

// Add Sort Function
// Add Dedupe Function

?>

<?php

	$address_book = readCSV();
	
	if (checkPOST($_POST)) {
		$address_book[] = $_POST;
		writeCSV($address_book);
	}

	if (isset($_GET['remove'])) {
	 	$address_book = removeEntry($_GET['remove'], $address_book);
	 	writeCSV($address_book);
	}



?>

<html>
<head>
	<title>Address Book Web App</title>
</head>
<body>
<hr>
	<table border="1">
		<tr>
			<th>First</th>
			<th>Last</th>
			<th>Email</th>
			<th>Telephone</th>
			<th>Address</th>
			<th>Homepage</th>
			<th>Remove?</th>
		</tr>

			<? foreach ($address_book as $key => $entry) : ?>
				<tr>
					<? foreach ($entry as $value) : ?>
						<td><?= htmlspecialchars(strip_tags($value)) ?></td>
					<? endforeach ?>
					<td><?= "<a href='?remove={$key}'> Remove </a>" ?></td>
				</tr>
			<? endforeach ?>

	</table>

	<hr>

	<h3>Add Entry:</h3>
		<form method="POST" action="">
		<p>
			<label for="first_name">First</label>
			<input id="first_name" name="first_name" type="text" placeholder="First Name">
		</p>
		<p>
			<label for="last_name">Last</label>
			<input id="last_name" name="last_name" type="text" placeholder="Surname">
		</p>
		<p>
			<label for="email">Email</label>
			<input id="email" name="email" type="email" placeholder="Email">
		</p>
		<p>
			<label for="tel">Telephone</label>
			<input id="tel" name="tel" type="" placeholder="#">
		</p>
		<p>
			<label for="street_addr">Street Address</label>
			<input id="street_addr" name="street_addr" type="text" placeholder="123 Anywhere Ln">
		</p>
		<p>
			<label for="homepage">Homepage</label>
			<input id="homepage" name="homepage" type="url" placeholder="http://example.com/">
		</p>
			<button value="submit">Add</button>
		</form>

</body>
</html>