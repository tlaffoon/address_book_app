<?php 

// TODO: require Filestore class
require('filestore.php');


class AddressDataStore extends Filestore {

    // TODO: Remove this, now using parent!
    function __construct($filename) 
    {
        $filename = strtolower($filename);
        parent::__construct($filename);
    }

    function read_address_book()
    {
        return $this->readCSV();
    }

    function write_address_book($array) 
    {
        return $this->writeCSV($array);
    }

}

?>