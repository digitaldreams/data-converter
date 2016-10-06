<?php

namespace DataConverter\Laravel;

use DataConverter\FileManager;

class Exim
{
    protected $data = [];

    /**
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $collection;
    protected $errors    = [];
    protected $extension = 'xlsx';
    protected $uploadFolder;
    protected $downloadFolder;
    protected $fullPath;
    protected $filter    = [];

    /**
     *
     * @var \DataConverter\FileManager;
     */
    protected $fileManager;

    public function __construct()
    {
        $this->fileManager    = new FileManager();
        $this->downloadFolder = $this->uploadFolder   = storage_path('app');
    }

    public function asText()
    {
        $this->extension   = 'txt';
        $this->fileManager = new \DataConverter\FileText;
    }

    public function only(array $filter)
    {
        $this->filter = $filter;
    }

    public function asExcel()
    {
        $this->extension   = 'xlsx';
        $this->fileManager = new \DataConverter\FileExcel;
    }

    public function asPdf()
    {
        $this->extension = 'pdf';
    }

    public function asXml()
    {
        $this->extension   = 'xml';
        $this->fileManager = new \DataConverter\FileXml;
    }

    public function asJson()
    {
        $this->extension   = "json";
        $this->fileManager = new \DataConverter\FileJson();
    }

    public function asArray()
    {
        
    }

    public function validate(callable $callback)
    {
        
    }

    public function dataFile($pathToFile)
    {
        $ext             = pathinfo($pathToFile, PATHINFO_EXTENSION);
        $this->extension = str_replace(".", "", $ext);

        $this->fileManager = FileManager::initByExt($this->extension);
        $this->fullPath    = $pathToFile;
        return $this;
    }

    /**
     *
     * @param type $request
     * @param type $name
     */
    public function upload(\Illuminate\Http\Request $request, $name)
    {
        if ($request->hasFile($name)) {
            $file     = $request->file($name);
            $fileName = uniqid('f_').'.'.$file->getClientOriginalExtension();

            if ($file->isValid()) {
                $file = $file->move($this->uploadFolder, $fileName);
            } else {
                $this->errors[] = 'Invalid file';
            }
            $this->fileManager = FileManager::initByFileType($file->getRealPath());
            $this->fullPath    = $file->getRealPath();

            if (!empty($this->fileManager)) {
                $this->data = $this->fileManager->config(['first_row_as_headline' => true])->read()->toAssoc();
                $this->clean();
            } else {
                $this->errors[] = 'Unsupported file type';
            }
        } else {
            $this->errors[] = 'File does not exists';
        }
        return $this;
    }

    public function write($filePath = '', $filter = [])
    {
        if (!empty($filePath)) {
            $ext               = pathinfo($filePath, PATHINFO_EXTENSION);
            $this->extension   = str_replace(".", "", $ext);
            $this->fileManager = FileManager::initByExt($this->extension);
        } else {
            $this->fullPath = $filePath       = $this->downloadFolder.uniqid().'.'.$this->extension;
        }

        if (!empty($filter) && is_array($filter)) {
            $this->only($filter);
        }

        $this->fullPath = $filePath;

        $this->fileManager->config([
            'first_row_as_headline' => true,
            'data' => $this->data,
            'mode' => 'w+',
            'file_path' => $this->fullPath,
            'filter' => $this->filter
        ])->write();

        return $this;
    }

    public function read($filePath = '', $filter = [])
    {
        if (!empty($filePath) && file_exists($filePath)) {
            $this->dataFile($filePath);
        }

        if (!empty($filter) && is_array($filter)) {
            $this->only($filter);
        }

        $config = [
            'first_row_as_headline' => true,
            'file_path' => $this->fullPath,
            'filter' => $this->filter
        ];
        $this->fileManager->config($config);
    }

    protected function clean()
    {
        if (file_exists($this->fullPath)) {
            unlink($this->fullPath);
            return true;
        }
        return false;
    }

    public function append($filePath = '')
    {
        if (!empty($filePath) && file_exists($filePath)) {
            $this->dataFile($filePath);
        }
        $config = [
            'first_row_as_headline' => true,
            'file_path' => $this->fullPath,
            'filter' => $this->filter,
            'data' => $this->data
        ];
        $this->fileManager->config($config)->append()->write();
        return $this;
    }

    public function getData()
    {
        
    }

    public function setData($data, $model = '')
    {
        if (is_object($var)) {
            if ($data instanceof \Illuminate\Database\Eloquent\Model) {
                $this->model = $data;
                $this->data  = $this->model->toArray();
            } elseif ($data instanceof \Illuminate\Database\Eloquent\Collection) {
                $this->model = $data->first();
                $this->data  = $data->toArray();
            }
        } elseif (is_array($data)) {
            $this->data  = $data;
            $this->model = $model;
        }

        return $this;
    }

    public function fileManager($fileManager='')
    {
        if($fileManager instanceof \DataConverter\FileManager){
            $this->fileManager=$fileManager;
            return $this;
        }
        return $this->fileManager;
    }
}