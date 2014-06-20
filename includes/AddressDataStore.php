<?php 

require('filestore.php');

class AddressDataStore extends Filestore {

    // TODO: Remove this, now using parent!
    function __construct($filename) 
    {
        $filename = strtolower($filename);
        parent::__construct($filename);
    }

    public function read_address_book()
    {
        return $this->read();
    }

    public function write_address_book($array) 
    {
        return $this->write($array);
    }
    
}

?>