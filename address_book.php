<?php

include('AddressDataStore.php');

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

function uploadFile() {
	$upload_dir = '/vagrant/sites/addr.dev/data/';
	$filename = basename($_FILES['upload_file']['name']);
	$saved_filename = $upload_dir . $filename;
	move_uploaded_file($_FILES['upload_file']['tmp_name'], $saved_filename);

	// if (isset($saved_filename)) {
	//     $GLOBALS['file_uploaded']  = "<p>You can download your file <a href='/uploads/{$filename}'>here</a>.</p>";
	// }
}

function checkUploadError() {
	if ($_FILES['upload_file']['error'] == 0) {
		return false;
	}
	else
		$GLOBALS['error_message'] = "Error on file upload: Unknown error.";
		return true;
}

function checkMIME() {
	if ($_FILES['upload_file']['type'] != 'text/csv') {
		$GLOBALS['error_message'] = "File must be a valid CSV.";
		return false;
	}
	else 
		return true;
}

// Add Sort
// Add Dedupe
// Add Search
// Add Upload

?>

<?php

	// Create a new instance of AddressDataStore
	$storeData = new AddressDataStore('../data/address-book.csv');
	$address_book = $storeData->readCSV();
	
	if (checkPOST($_POST)) {
		$address_book[] = $_POST;
		$storeData->writeCSV($address_book);
	}

	if (isset($_GET['remove'])) {
	 	$address_book = removeEntry($_GET['remove'], $address_book);
	 	$storeData->writeCSV($address_book);
	}

	if (count($_FILES) == 1) {
		if (checkUploadError() == false && checkMIME() == true) {
			uploadFile();
			// var_dump($_FILES);
			$storeData2 = new AddressDataStore("../data/{$_FILES['name']}");
			$upload_array = $storeData2->readCSV(filename);
			var_dump($upload_array);
			$address_book = array_merge($address_book, $upload_array);
			var_dump($address_book);
			$storeData->writeCSV($address_book);
		}
	}
?>

<html>
<head>
	<title>Address Book Web App</title>
	<link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<hr>
	<table class="table table-striped">
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
					<td><?= "<a href=\"?remove={$key}\">" ?>Remove</a></td>
				</tr>
			<? endforeach ?>

	</table>

<hr>
	<!-- Add Entry Form														-->
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

		<?php  // If user feedback messages exist, output them.
			if (isset($GLOBALS['item_added'])) {
				echo "<p style=color:green;> {$GLOBALS['item_added']} </p>";
			}

			elseif (isset($GLOBALS['item_removed'])) {
				echo "<p style=color:orange;> {$GLOBALS['item_removed']} </p>";
			} 
		?>
<hr>
		<!-- Upload File Form														-->
		<h3>Upload File:</h3>
			<form method="POST" enctype="multipart/form-data" action="">
				<label for="upload_file">Upload File:</label>
				<input id="upload_file" name="upload_file" type="file" placeholder="Choose file">
				<button type="submit" value="Upload">UPLOAD</button>
			</form>

			
			<?php  // If file upload error messages exist, output them.
				if (isset($GLOBALS['error_message'])) { 
					echo "<p style=color:red;> {$GLOBALS['error_message']} </p>"; 
				} 
				elseif (isset($GLOBALS['file_uploaded'])) {
					echo "<p style=color:blue;> {$GLOBALS['file_uploaded']} </p>";
				} 
			?>

</body>
</html>