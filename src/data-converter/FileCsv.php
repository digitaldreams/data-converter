<?php 

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace  DataConverter;

/**
 * Description of FileCsv
 *
 * @author Tuhin
 */
class FileCsv extends FileManager implements FileManagerInterface {

    public function __construct() {
        parent::__construct();
    }

    public function read() {
        $this->loadFile($this->file_path, 'r+');
        $data = [];
        while (!$this->file->eof()) {
            $data[] = $this->file->fgetcsv();
        }
        $this->data = $data;
        return $this;
    }

    public function write() {
        $this->loadFile($this->file_path, 'w+');
    }

    public function append() {
        ;
    }

}
