# data-converter
something in web application we need to convert data from excel file to array, array to excel file and more.
##How to install

  "require-dev": {
  
     "digitaldream/data-converter": "dev-master"
        
}
##Features
1. Excel to Array and associative array (if first row marked as heading)
2. Excel to json
3. Excel to text
4. Filter data while import from excel,json,txt,csv

##Uses

//Example 01

<?php

$fileManager = new DataConverter\FileExcel();
// A lot of other configuration like you can define from which row you like to read and how many row it will be read. Also you can // //append data to file.  
$config = [
    'file_path' => 'C:\Users\Tuhin\Downloads\apiv1567623ccbcfc1_570a009e10650.xlsx',
    'first_row_as_headline' => true,
];
  $data = $fileManager->config($config)->read()->getData();
   
 //$data = $fileManager->config($config)->read()->toJson();
  
 //$data = $fileManager->config($config)->read()->toText();
  
 //$data = $fileManager->config($config)->read()->toAssoc();
  print_r($data);
  
?>

//Example 02

<?php
//Here it will process the file based on file mimetype. 

$fileFullPath='test.txt';

  $fileManager = DataConverter\FileManager::initByFileType($fileFullPath);

if ($fileManager === FALSE) {
 // exit() File tye does not mathch
 }
 //here we used filter. It will take only these two columns value and other data from the souce will be ignored.
 
 $data = $fileManager->config($config)->read()->makeAssoc()->filter(['column_1','column_2])->getData();

?>

//Example 03

<?php

 $fileManager = new DataConverter\FileExcel();

 $configFile = [
 
     'file_path' => $fullPath,// full file path where file will be saved.
     
       'data' => $data,//data as associative array.
       
       'mode' => 'w+'//only valid if file type is txt
       
    ];

  $fileManager = $fileManager->config($configFile)->filter(['column 1','column 2'])->write(true);

?>



