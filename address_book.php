<?php

// require_once('./includes/filestore.php');
require_once('./includes/AddressDataStore.php');

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

// Add Sort
// Add Dedupe
// Add Search

?>

<?php

	// Create a new instance of AddressDataStore
	$addrObject = new AddressDataStore('../data/address-book.csv');
	$address_book = $addrObject->read($addrObject->filename);
	
	// Checks for POST data and adds entry to address book
	if (!empty($_POST)) {		
		foreach ($_POST as $key => $value) {
			// echo "<p>$key $value</p>";
			if ($value == '') {
				throw new Exception("Please enter " . ucfirst($key) . ".", 1);
			}
			if (strlen($value) > 125) {
				throw new Exception(ucfirst($key) . "cannot exceed 125 characters.", 1);
			}
		}
	// Add POST data to array
	$address_book[] = $_POST;
	// 	// Write changes to file
	$addrObject->write_address_book($address_book);
	}

	// Checks for GET request and removes corresponding entry
	if (isset($_GET['remove'])) {
		// Remove entry from address book array
	 	$address_book = removeEntry($_GET['remove'], $address_book);
	 	// Write changes to file
	 	$addrObject->write_address_book($address_book);
	 	// Redirect to main index.php
	 	header('Location: http://addr.dev/');
	}

	// Checks for uploaded files and processes accordingly
	if (count($_FILES) == 1) {
		// Runs sanity check functions located above
		if (checkUploadError() == false && checkMIME() == true) {
			// Runs local upload file function
			uploadFile();
			// Create additional object using Filestore class
			$addrObject2 = new AddressDataStore("../data/{$_FILES['upload_file']['name']}");
			// Create new array to merge with existing address book 
			$upload_array = $addrObject2->read_address_book($addrObject2->filename);
			// Merge
			$address_book = array_merge($address_book, $upload_array);
			// Write changes to file
			$addrObject->write_address_book($address_book);
		}
	}
?>

<html>
<head>
	<title>Address Book Web App</title>
	<link href="./style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<p class="navbar-brand">Address Book</p>
		</div>

		<form class="navbar-form navbar-right" role="search">
		  <div class="form-group">
		    <input type="text" class="form-control" placeholder="Search">
		  </div>
		  <button type="submit" class="btn btn-default">Submit</button>
		</form>

	</div>
</nav>

	<table class="table table-hover">
		<tr>
			<th>Name</th>
			<th>Phone</th>
			<th>Email</th>
			<th>Address</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
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
	<div class="container">
		<!-- Add Entry Form														-->
		<h3>Add Entry:</h3>
		<form role="form" method="POST" action="">
			<div class="form-group">			
			<label for="name">Name</label>
			<input class="form-control" id="name" name="name" type="text" placeholder="Name">
			</div>

			<div class="form-group">			
			<label for="telephone">Phone</label>
			<input class="form-control" id="telephone" name="telephone" type="" placeholder="#">
			</div>

			<div class="form-group">			
			<label for="email">Email</label>
			<input class="form-control" id="email" name="email" class="form-control"type="email" placeholder="user@domain.com">
			</div>

			<div class="form-group">			
			<label for="address">Address</label>
			<input class="form-control" id="address" name="address" type="text" placeholder="123 Anywhere Ln">
			</div>

			<div class="form-group">			
			<label for="city">City</label>
			<input class="form-control" id="city" name="city" type="text" placeholder="San Antonio">
			</div>

			<div class="form-group">			
			<label for="state">State</label>
			<input class="form-control" id="state" name="state" type="text" placeholder="">
			</div>

			<div class="form-group">			
			<label for="zip">Zip</label>
			<input class="form-control" id="zip" name="zip" type="text" placeholder="78015">
			</div>

			<button class="btn btn-default"value="submit">Add</button>
		</form>

		<?php  // If user feedback messages exist, output them.
			if (isset($GLOBALS['item_added'])) {
				echo "<p style=color:green;> {$GLOBALS['item_added']} </p>";
			}

			elseif (isset($GLOBALS['item_removed'])) {
				echo "<p style=color:orange;> {$GLOBALS['item_removed']} </p>";
			} 
		?>
	</div>
<hr>
	<div class="container">
		<!-- Upload File Form														-->
		<h3>Upload File:</h3>
			<form role="form" method="POST" enctype="multipart/form-data" action="">
				<div class="form-group">			
				<label for="upload_file">Upload File:</label>
				<input class="form-control" id="upload_file" name="upload_file" type="file" placeholder="Choose file">
				</div>
				

				<button class="btn btn-default" type="submit" value="Upload">UPLOAD</button>
			</form>

			
			<?php  // If file upload error messages exist, output them.
				if (isset($GLOBALS['error_message'])) { 
					echo "<p style=color:red;> {$GLOBALS['error_message']} </p>"; 
				} 
				elseif (isset($GLOBALS['file_uploaded'])) {
					echo "<p style=color:blue;> {$GLOBALS['file_uploaded']} </p>";
				} 
			?>
	</div>

</body>
</html>