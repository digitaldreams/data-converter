<?php namespace  Digitaldream\DataConverter;

/**
 * Description of FileJson
 *
 * @author Tuhin
 */
class FileJson extends FileManager implements FileManagerInterface
{

    /**
     * Under construction 
     * @return \App\Libs\Report\FileJson
     */
    public function append()
    {
        return $this;
    }

    /**
     * Read from Json file
     * @param type $file_path
     * @return \App\Libs\Report\FileJson
     * @throws \Exception if given file is not a valid json file
     */
    public function read($file_path = '')
    {
        try {

            $this->file_path = !empty($file_path) ? $file_path : $this->file_path;

            if (!$this->checkMimeType()) {
                throw new \Exception('Unknown json file');
            }

            $jsonSting = file_get_contents($this->file_path);
            $data = json_decode($jsonSting, true);
            $this->data = $data;
        } catch (Exception $ex) {
            $this->setException($ex);
        }
        return $this;
    }

    /**
     * Write to json file 
     */
    public function write()
    {
        $splFileObject = $this->loadFile();
        $splFileObject->fwrite($this->toJson());
    }
}
