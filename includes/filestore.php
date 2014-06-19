<?php

class Filestore {

    public $filename = '';

    function __construct($filename = '') 
    {
        // Sets $this->filename
        $this->filename = $filename;
    }

    /**
     * Returns array of lines in $this->filename
     */
    function readTXT($filename) {
        $handle = fopen($filename, 'r');
            if (filesize($filename) > 0) {
                $contents = trim(fread($handle, filesize($filename)));
                    $lines = explode("\n", $contents);
                    fclose($handle);
                    return $lines;
                }

            else 
                $lines = [];
                    return $lines;

        }

    /**
     * Writes each element in $array to a new line in $this->filename
     */
    function writeTXT($array) {
        $handle = fopen($this->filename, 'w');
        $string = '';

        foreach ($array as $key => $value) {
            $string .= "$value\n";
        }

        fwrite($handle, $string);
        fclose($handle);
    }

    /**
     * Reads contents of csv $this->filename, returns an array
     */
    function readCSV()
    {
        $array = [];
        $handle = fopen($this->filename, 'r');
        while(!feof($handle)) {
            $row = fgetcsv($handle);
            if (!empty($row)) {
                $array[] = $row;
            }
        }
        return $array;    
    }

    /**
     * Writes contents of $array to csv $this->filename
     */
    function writeCSV($array) {
        $handle = fopen($this->filename, 'w');
        foreach ($array as $fields) {
            fputcsv($handle, $fields);
        }
        fclose($handle);
    }

}