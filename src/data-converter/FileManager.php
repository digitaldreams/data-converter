<?php

namespace  DataConverter;

/**
 * Description of FileManager
 *
 * @author Tuhin
 */
class FileManager {

    const FILE_TYPE_EXCEL = 'excel';
    const FILE_TYPE_TEXT = 'text';
    const FILE_TYPE_JSON = 'json';
    const FILE_TYPE_XML = 'xml';

    /**
     * Full path of the file.
     * @var path 
     */
    protected $file_path;

    /**
     * Spl File Object. Reading file only
     * @var \SplFileObject 
     */
    protected $file;

    /**
     * File Open mode.
     * 
     * ------------------Some file Mode--------------------
     * 
     * r    => reading only + pointer at the beginning
     * w    => writing only + pointer at the beginning +attempt to create if not exists
     * r+   => Reading and writing + pointer at the beginning
     * w+   => Reading and writing + pointer at the beginning + attempt to create if not exists
     * a    => writing only + pointer at the end . Use to append data at the end of file
     * a+   => Open for reading and writing + 'a'
     * 
     * @var string 
     */
    protected $mode = 'r+';

    /**
     * File Mime Type
     * @var type 
     */
    protected $mime_type;

    /**
     * Data to get or set to file
     */
    protected $data = [];

    /**
     * Headline or first row of the file which will be used as headline column
     *  which is descriptive 
     * @var array
     */
    protected $headline = [];

    /**
     * First row as headline. If this set to true then data first row used as headline.
     * 
     */
    protected $first_row_as_headline = false;

    /**
     * Validation rules. Will be used to filter only valid data before export or import
     * @var array 
     */
    protected $validation = [];

    /**
     * Set Controll over import and exported.
     *  Which key or property will be exported or imported from file.
     * e.g An excel file may contains lots of column among them you can define
     *  which column you like to accept. 
     * Other column data will be ignored.


     * @var array e.g ['email','name','mobile','note'] 
     */
    protected $filter = [];

    /**
     * Error container. In any stage of processing error may occured and store here.
     * @var  array 
     */
    protected $error = [];

    /**
     * What type of file currenly this class are handling. Valid values are 
     *  excel,txt,json,xml,csv
     * @var type 
     */
    protected $file_type;

    /**
     * Index number / Line number to start data read and slice data from source before write to it.
     * For example, In data source we have 500 data now we want to write 100 records per excel file then we can do that simply by using from
     * 
     * Also can be used to get a slice of data along with to
     * 
     * @var integer 
     */
    protected $from = 0;

    /**
     * End of the Read/write data boundary.
     * 
     * for example, we we like to read first 100 records of a  file then make simply do  this by setting 100
     * @var integer 
     */
    protected $to;

    /**
     * If set to true then append data at the end of existing file.
     * So that it does not erase existing data.
     * 
     * @var boolean 
     */
    protected $append = false;

    /**
     * Point each column of this class to an exact table column. 
     * e.g there may have column 'email address' and this column may be indicate table email field. 
     * so the option would be email=>email address
     * @var array 
     */
    protected $table_column_alias = [];

    /**
     * Its always best approach validate file by its mime type not extension.
     * Format : extention=>mime type 
     * @var array 
     */
    protected $mime_types = [
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'txt' => 'text/plain',
        'json' => 'text/plain',
        'xml' => 'application/xml',
        'xlsx' => 'application/octet-stream',
        'csv' => 'text/csv'
    ];

    public function __construct() {
        //do initialization
    }

    /**
     * There are couple of configuration setting e.g validation,headline etc.
     * Its very boring to make a get_* amd set_* to get and set value in this settings.
     * So we used php magic method __call which will handel both get and set operation.
     * 
     * Example. $obj->setFilePath('D:\xampp\htdocs\result'); $obj->getFilePath();
     * This will set and get value from @var file_path
     * 
     * 
     * @param string $name camelCase name of the snake_case property. e.g property name is file_path then it may call setFileName();
     * @param array $arguments arguments of the property
     * @return \App\Libs\Report\FileManager
     */
    public function __call($name, $arguments) {
        $action = substr($name, 0, 3);
        $name = snake_case(substr($name, 3));

        if (property_exists($this, $name)) {

            if ($action == 'get') {
                return $this->$name;
            } elseif ($action == 'set') {
                $this->$name = array_shift($arguments);
            }
        }
        return $this;
    }

    /**
     * Set settings as associative array. Where key will be the name of the property and value would be its value
     * @param array $config
     * @return \App\Libs\Report\FileManager Description
     */
    public function config(array $config) {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        return $this;
    }

    protected function getHeadline() {
        if (!empty($this->headline) && is_array($this->headline)) {
            return $this->headline;
        }
        if ($this->first_row_as_headline == true && !empty($this->data)) {
            return $this->headline = array_shift($this->data);
        }
        return $this->headline;
    }

    /**
     * Filter Input/Output data. This will control what data field to import and export
     * @param array $filter Filter array
     * @return type
     */
    public function filter($filter = []) {
        if (!empty($filter)) {
            $this->filter = $filter;
        }
        $retArr = [];
        foreach ($this->data as $index => $value) {
            $row = (array) $value;
            $filteredArray = array();
            $filteredArray = array_intersect_key($row, array_flip($this->filter));
            $retArr[] = $filteredArray;
        }
        $this->data = $retArr;
        return $this;
    }

    public function makeAssoc() {
        $this->toAssoc();
        return $this;
    }

    /**
     * Make enum index data row to associative array using headline
     * @return type
     */
    public function toAssoc() {
        $retArr = [];
        $headline = $this->getHeadline();
        //If headline is empty then return from here.
        if (empty($headline)) {
            return $this->data;
        }
        //array_combine will throw error if headline and value are not equal number.
        $headlines = array_values($headline);
        $totalHeadline = count($headlines);
        foreach ($this->data as $index => $value) {
            //If value array if greater than headline then we trim value array
            if (count($value) > $totalHeadline) {
                $value = array_slice($value, 0, $totalHeadline);
            }
            if (count($headlines) != count($value)) {
                continue;
            }
            $retArr[] = array_combine($headlines, $value);
        }
        return $this->data = $retArr;
        // return $this;
    }

    public function toJson() {
        $assocData = $this->toAssoc();
        return json_encode($assocData);
    }

    /**
     * Return data as XML format or save it to file_path
     * @param boolean $save whether or not xml is written to the specified file
     * @return boolean
     */
    public function toXml($save = false) {
        $assocData = $this->toAssoc();
        $xml = new \DOMDocument();
        $simpleXml = new \SimpleXMLElement('<xml/>');
        $records = $simpleXml->addChild('contacts');

        foreach ($assocData as $index => $data) {
            $record = $records->addChild('contact');

            foreach ($data as $elem => $value) {
                $record->addChild($elem, $value);
            }
        }
        if ($save) {
            return $simpleXml->asXML($this->file_path);
        } else {
            return $simpleXml->asXML();
        }
    }

    /**
     * Output Data as String
     * @param character $seperator Column seperator used as glue for implode() function
     * @return string
     */
    public function toText($seperator = ',') {
        $retString = '';

        foreach ($this->data as $record) {
            $retString.=implode($seperator, $record) . "\n";
        }
        return $retString;
    }

    /**
     * Mime Type checking. If it matches accepted mime type list then return true otherwise return false
     * @return boolean
     */
    public function checkMimeType() {
        //  fileinfo
        $finfo = new \finfo();
        $this->mime_type = static::getMimeType($this->file_path);
        if (!in_array($this->mime_type, $this->mime_types)) {
            $this->error[] = 'Unknown file type';
            return false;
        }
        return true;
    }

    public static function getMimeType($filePath) {
        //  fileinfo
        $finfo = new \finfo();
        return $finfo->file($filePath, FILEINFO_MIME_TYPE);
    }

    /**
     * Get SPL file Object. If not created then create an object and return it.
     * @return \SplFileObject 
     */
    public function loadFile($file_path = '', $mode = '', $force_create = false) {
        try {

            if ($force_create == false && $this->file instanceof \SplFileObject) {
                return $this->file;
            }
            $filePath = !empty($file_path) ? $file_path : $this->file_path;
            $openMode = !empty($mode) ? $mode : $this->mode;
            $this->file = new \SplFileObject($filePath, $openMode);
            return $this->file;
        } catch (\Exception $ex) {
            $this->error[] = $ex->getMessage();
        }
    }

    /**
     * Set error message from an exception
     * @param \Exception $ex
     * @return type
     */
    public function setException(\Exception $ex) {
        $this->error[] = $ex->getMessage() . 'on ' . $ex->getLine() . 'in ' . $ex->getFile();
        return $this;
    }

    /**
     * Throw a new exception
     * @param \Exception $ex
     * @return \App\Libs\Report\FileManager
     * @throws \Exception
     */
    public function throwException(\Exception $ex) {
        throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        return $this;
    }

    /**
     * Check if any error occured
     * @return booleab
     * 
     */
    public function hasError() {
        return count($this->error) > 0 ? TRUE : FALSE;
    }

    /**
     * There are couple of child class of FileManager class. 
     * And each child class handle with specific file type. 
     * Here we will initialize proper child based on file mime type.
     *  e.g If a user upload an excel file then we this method automatically return new FileExcel.
     * 
     * @param path $filePath Full file path. File must be exist
     * @return FileManager
     */
    public static function initByFileType($filePath) {
        $obj = new static;

        $mimeType = static::getMimeType($filePath);
        $key = array_search($mimeType, $obj->getMimeTypes());
        $obj = static::initByExt($key);
        return $obj;
    }

    /**
     * Initialize proper child class by extension
     * @param type $ext File Extension. 
     * Supported extensions are xlsx,xlx,json,txt,xml
     * @return FileManager
     */
    public static function initByExt($ext) {
        $obj = '';
        switch ($ext) {
            case 'xlsx':
            case 'xlx':
                $obj = new FileExcel();
                break;
            case 'json':
                $obj = new FileJson();

                break;
            case 'txt':
                $obj = new FileText();

                break;
            case 'xml':

                $obj = new FileXml();
                break;
            case 'csv':
                $obj = new FileCsv();
            default :
                $obj = FALSE;
                break;
        }
        return $obj;
    }

}
