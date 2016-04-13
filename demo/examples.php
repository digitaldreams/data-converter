<?php

//Path to vendor file
// require_once './vendor/autoload.php';
$fileManager = new DataConverter\FileExcel();

/*
 * Read All data
 *  

  $config = [
  'file_path' => 'file/contacts.xlsx',
  'first_row_as_headline' => true,
  ];
  $data = $fileManager->config($config)->read()->toXml();

  $data2 = $fileManager->config($config)->read()->toText();
  print_r($data2);

  $data3 = $fileManager->config($config)->read()->makeAssoc()->toJson();
  print_r($data3);
 */

/*
 * Read data by Range
 * 
  $config = [
  'file_path' => 'file/contacts.xlsx',
  'first_row_as_headline' => true,
  'range' => 'A2:F4'
  ];
  $data = $fileManager->config($config)->read()->getData();
 * 
 */

/*
 * Filter Data
 * 
 *

  $config = [
  'file_path' => 'file/contacts.xlsx',
  'first_row_as_headline' => true,
  ];
  $data = $fileManager->config($config)
  ->read()
  ->makeAssoc()
  ->filter(['full_name', 'email_address'])
  ->getData();

 *
 */

/*
 * Append Data to the end of file
  $config = [
  'file_path' => 'file/contacts.xlsx',
  'first_row_as_headline' => true,
  'data' => ['Tuhin Bepari', '01925000036', '1537019884', 'digitaldreams40@gmail.com', 'Mirpur 11, Dhaka', 'Test']
  ];
  $data = $fileManager->config($config)->append()->write();

 *
 */

/*
  $dataToSave = [

  ['Tuhin Bepari', '01925000036', '1537019884', 'digitaldreams40@gmail.com', 'Mirpur 11, Dhaka', 'Test']
  ];

  $config = [
  'headline' => ['full_name', 'primary_phone', 'secondary_phone', 'email_address', 'address', 'group'],
  'file_path' => 'C:\Users\Tuhin\Desktop\contacts-test.xlsx',
  'first_row_as_headline' => true,
  'data' => $dataToSave
  ];
  $data = $fileManager->config($config)->write();
 */

/**
 * Initialize by File Mime Type
 * 
 * Consider first line of the text file as headline. default seperator of this text line is ,|. You can pass other seperator on read method first arguments
 * 

  $fileManager = DataConverter\FileManager::initByFileType('C:\Users\Tuhin\Desktop\contacts.txt');
  $data = $fileManager->config([
  'first_row_as_headline' => true,
  ])
  ->read()
  ->makeAssoc()
  ->getData();
  print_r($data);
 * 
 */
//In this example only line from 1 to 2 will be read others will be ignored
$fileManager = DataConverter\FileManager::initByFileType('C:\Users\Tuhin\Desktop\contacts.txt');
$data = $fileManager->config([
            'from' => 1,
            'to' => 2
        ])
        ->read()
        ->makeAssoc()
        ->getData();
print_r($data);
/**
 * Read XML file 
 * 

$fileManager = DataConverter\FileManager::initByFileType('file/contacts.xml');
$data = $fileManager->read()->getData();
print_r($data);
 */

