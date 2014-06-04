<?php

function readCSV($filename = '../data/address-book.csv') {
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

function writeCSV($address_book, $filename = '../data/address-book.csv') {
    $handle = fopen($filename, 'w');
    foreach ($address_book as $fields) {
    	fputcsv($handle, $fields);
    }
    fclose($handle);
}

function checkPOST($post) {
	// check captured post data for all fields completed.

	$required = array('first_name', 'last_name', 'email', 'tel', 'address', 'homepage');

	foreach ($required as $field) {
			if (empty($_POST['$field'])) {
				$GLOBALS['post_error'] = 'Please input all fields.';
				return false;
			}
	}

	return true;
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

	$address_book = readCSV();
	
	if (checkPOST($_POST)) {
		$address_book[] = $_POST;
		writeCSV($address_book);
	}

	if (isset($_GET['remove'])) {
	 	$address_book = removeEntry($_GET['remove'], $address_book);
	 	writeCSV($address_book);
	 	header("location:./address_book_sans_class.php");
	}

	if (count($_FILES) == 1) {
		if (checkUploadError() == false && checkMIME() == true) {
			uploadFile();
			//var_dump($_FILES);
			$upload_array = readCSV("../data/{$_FILES['upload_file']['name']}");
			$address_book = array_merge($address_book, $upload_array);
			writeCSV($address_book);
		}
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
			<input id="last_name" name="last_name" type="text" placeholder="Last Name">
		</p>
		<p>
			<label for="email">Email</label>
			<input id="email" name="email" type="email" placeholder="user@domain.com">
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

		<?php  // If file upload error messages exist, output them.
			if (isset($GLOBALS['post_error'])) { 
				echo "<p style=color:red;> {$GLOBALS['post_error']} </p>"; 
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
			?>

</body>
</html>