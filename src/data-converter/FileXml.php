<?php namespace  DataConverter;

/**
 * Description of FileText
 *
 * @author Tuhin
 */
class FileXml extends FileManager implements FileManagerInterface
{

    /**
     * 
     * @param file $file_path qualified file path
     * @param character $mode file open mode
     */
    public function __construct($file_path = '', $mode = '')
    {
        parent::__construct();
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
    public function read()
    {
        $simpleXml = simplexml_load_file($this->file_path);
        $contacts = $simpleXml->xpath('//contact');
        //
        foreach ($contacts as $contact) {
            
        }
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
        $this->toXml(true);
    }
}
