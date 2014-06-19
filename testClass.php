<?php

require('../class/AddressDataStore.php');

$addrObject = new AddressDataStore('../data/address-book.csv');

var_dump($addrObject->filename);

$address_book = $addrObject->read_address_book($addrObject->filename);

var_dump($address_book);
