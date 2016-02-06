<?php
//HW_WP::load_wp();
ini_set('display_errors', true);
/*
include ('../../../../classes/class-core.php');
if(!HW_WP::load_wp()) exit("Not found wordpress core.");

HW_APF_FieldTypes::load_fieldtype('APF_hw_upload_field');

//valid data
//APF_hw_upload_field::config();
//APF_hw_upload_field::valid_form_data();
*/

HW_HOANGWEB::load_class('HW_Ajax');
//check if this is an ajax request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
    die();
}

$target_dir = !empty($config->uploads_folder)? rtrim($config->uploads_folder,'/').'/' : dirname(__FILE__). "/uploads/";
$ajax = HW_Ajax::create();

//upload multiple files
for($i=0;$i< APF_hw_upload_field::get_files_num();$i++) :
    //validation
    if(!isset($_FILES['file-'.$i])) break;

    $file = $_FILES['file-'.$i];

    $File_Name          = strtolower($file['name']);
    $File_Ext           = substr($File_Name, strrpos($File_Name, '.')); //get file extention

    if($config->random_filename) {
        $Random_Number      = rand(0, 9999999999); //Random number to be added to name.
        $target_file        = $target_dir.$Random_Number.$File_Ext; //new file name
        $filename = $Random_Number.$File_Ext;
    }
    else {
        $target_file = $target_dir . basename($File_Name);
        $filename = basename($File_Name);
    }

    $uploadOk = 1;

    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if($config->image_type) {
        $check = getimagesize($file["tmp_name"]);
        if($check !== false) {
            $ajax->message( "File is an image - " . $check["mime"] . ".");
            $uploadOk = 1;
        } else {
            $ajax->message( "File is not an image.");
            $uploadOk = 0;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $ajax->message( "Sorry, file already exists.");
        $ajax->add_data('code', 'exists');
        $ajax->add_data('path', $target_file);
        $uploadOk = 0;
    }
    // Check file size
    if ($file["size"] > 500000) {
        $ajax->message("Sorry, your file is too large.");
        $uploadOk = 0;
    }
    // Allow certain file formats
    /*if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }*/
    //allowed file type Server side check
    if(is_array($config->allow_types) && !in_array(strtolower($file['type']), $config->allow_types)) {
        die('Unsupported File!'); //output error
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $ajax->message( "Sorry, your file was not uploaded.");
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $ajax->message("The file ". $filename. " has been uploaded.");
            $ajax->status(1);
            $ajax->add_data('code', 'success');
            $ajax->add_data('url', $config->uploads_url. '/'. $filename);
            $ajax->add_data('path', $target_file);
            do_action ('hw_upload_file_success', $file);
        } else {
            $ajax->message("Sorry, there was an error uploading your file.");
            $ajax->status(0);
            $ajax->add_data('code', 'error');
            do_action ('hw_upload_file_error', $file);
        }
    }
endfor;
//after all files uploaded
do_action('hw_upload_files_success');
//json output
echo ($ajax);