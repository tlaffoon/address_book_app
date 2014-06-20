<?php

class Filestore {

    public $filename = '';
    private $is_csv = '';

    function __construct($filename = '') {
        // Sets $this->filename
        $this->filename = $filename;

        // Determines if csv or not
        if (substr($this->filename, -3) == 'csv') {
            $this->is_csv = true;
        }
    }

    /**
    *  Reads file
    */

    public function read() {

        if ($this->is_csv == true) {
            return $this->readCSV($this->filename);
        }

        else {
            return $this->readTXT($this->filename);
        }
    }

    /**
    *  Writes file
    */

    public function write($array) {
        if ($this->is_csv == true) {
            return $this->writeCSV($array);
        }

        else {
            return $this->writeTXT($array);
        }
    }

    /**
     * Returns array of lines in $this->filename
     */
    private function readTXT($filename) {
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
    private function writeTXT($array) {
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
    private function readCSV()
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
    private function writeCSV($array) {
        $handle = fopen($this->filename, 'w');
        foreach ($array as $fields) {
            fputcsv($handle, $fields);
        }
        fclose($handle);
    }

}