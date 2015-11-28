<?php namespace  DataConverter;

/**
 * Description of FileText
 *
 * @author Tuhin
 */
class FileText extends FileManager implements FileManagerInterface
{

    /**
     * 
     * @param file $file_path qualified file path
     * @param character $mode file open mode
     */
    public function __construct($file_path = '', $mode = '')
    {
        parent::__construct();

        if (!empty($file_path)) {
            $this->file_path = $file_path;
        }

        if (!empty($mode)) {
            $this->mode = $mode;
        }
    }

    /**
     * 
     * @param string $seperator One or more symbol or string which will be used to seperate one column to another .
     *  e.g ,| . text file line will be separated when either , or | found. 
     * @param integer $from Start line of the file from where start getting data
     * @param integer $to end line of the file where stop reading
     * @return \App\Libs\Report\FileText
     * @throws type
     */
    public function read($seperator = ',|', $from = '', $to = '')
    {

        if (is_int($from)) {
            $this->from = $from;
        }
        if (is_int($to)) {
            $this->to = $to;
        }
        $data = [];
        //create splfileObject if does not exists or return if created
        $splFile = $this->loadFile();
        if ($splFile->getType() != 'file' || $splFile->getExtension() != 'txt') {
            throw \Exception('Invalid txt file');
        }

        while ($splFile->valid()) {

             $currentLineNumber = $splFile->key();
            $currentLine = $splFile->getCurrentLine();

            if (is_int($this->to) && $currentLineNumber > $this->to) {
                break;
            }
            if (is_int($this->from) && $currentLineNumber >= $this->from) {
                //Data can be seperated both comma(,) or Pipe(|) and around these may have space.
                $data[$currentLineNumber] = preg_split('/ ?[' . $seperator . '] ?/', $currentLine);
            }
            $splFile->next();

            //   echo $currentLineNumber;
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Append data to the end of existing text file.
     */
    public function append()
    {
        $this->mode = 'a';
        $this->write();
    }

    /**
     *  Write data to text file.
     */
    public function write()
    {

        $splFileObject = $this->loadFile();
        $splFileObject->fwrite($this->toText());
    }

}
