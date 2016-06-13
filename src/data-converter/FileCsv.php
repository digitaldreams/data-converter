<?php

namespace DataConverter;

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
        $this->mode = 'w+';
        $this->loadFile($this->file_path);
        $this->file->fwrite($this->toText());
        return $this;
    }

    public function append() {
        $this->mode = 'a';
        $this->write();
        return $this;
    }

}
