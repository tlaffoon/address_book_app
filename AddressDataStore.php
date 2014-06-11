<?php

class AddressDataStore {

    public $filename = '';

    function __construct($filename = '') {
        $this->filename = $filename;
    }

    function __destruct() 
    {
        // echo "Class Dismissed!";
    }

    function readCSV() {
        $address_book = [];
        $handle = fopen($this->filename, 'r');
        while(!feof($handle)) {
        	$row = fgetcsv($handle);
        	if (!empty($row)) {
        		$address_book[] = $row;
        	}
        }
        return $address_book;    
    }

    function writeCSV($address_book) {
        $handle = fopen($this->filename, 'w');
        foreach ($address_book as $fields) {
        	fputcsv($handle, $fields);
        }
        fclose($handle);
    }

}

?>