<?php

define('SITE_URL', 'http://example.net');
define('SITE_ROOT', dirname(__FILE__));
define('DS', '/');

class Uploader
{
    protected $uploads_ENABLED = TRUE;
    protected $uploads_ALLOWED = array("jpg","jpeg","gif","png"); // Allowed File Types, Mainly Images
    protected $uploads_DENIED = array("php","htm","html","xml","css","cgi","xls","rtf","ppt","pdf","swf","flv","avi","wmv","mov","class","bat","sh","java","iso","c","cpp","ini","js"); // Strictly Disallowed File types


    /**
     * Method used to check the folder you are uploading to.
     * Also checks to see if uploading is enabled.
     *
     * @param string optional $folder The directory to check permission for. Use SITE_URL reletivity.
     * @return bool bool
     * 
     */
    public static function CheckUpload($folder=false){
        if($uploads_ENABLED == false){
            return false;
        }
        
        if($folder != false){
            if($folder <= ''){
                return false;
            }
            if(is_writeable($folder)){
                return true;
            }
        }
        
        if($folder == false && $uploads_ENABLED == true){
            return true;
        }
        return false;
    }
    
    
    /**
     * Main method to call when uploading files.
     * 
     * @param array $file The post data of the file to be uploaded ($_FILES['element_name'])
     * @param string $target The directory for the file to be uploaded to. Use SITE_URL reletivity
     * @return string bool Pathway to the file.
     * 
     */
    public function Upload($file, $target){
return true;
        if(self::CheckUpload($target) == false){
            //LogData::addLog(Auth::$userinfo->pilotid, 'A file upload was attempted, but denied due to local settings.');
            return false;
        }
        $check = self::CheckFile($file);
        if($check == false){
            return false;
        }
        
        $pic = self::Rename($file['name']);
        $target = $target.'/'.$pic;
        
        if(is_uploaded_file($file['tmp_name'])){
            if(move_uploaded_file($file['tmp_name'], $target)){
                $target2 = str_replace(SITE_ROOT, SITE_URL.DS, $target);
                self::LogUpload($target, $target2);
                return $target2;
            }else{
                return false;
            }
        }else{
            return false; // File was not uploaded via HTTP POST
        }
    }
    
    
    private static function CheckFile($file){
        if($file['error'] != 0){
            return $file['error'];
        }
        if(in_array(end(explode(".",strtolower($file['name']))), $uploads_DENIED)){
            $file['error'] = 8;
        }
        
        if(!in_array(end(explode(".",strtolower($file['name']))), $uploads_ALLOWED)){
            $file['error'] = 8;
        }
        
        if(in_array(end(explode(".",strtolower($file['name']))), $uploads_ALLOWED)){
            $file['error'] = 0;
        }
        
        if($file['tmp_name'] <= '' || $file['name'] <= '' || $file['error'] == 4){
            $file['error'] = 4;
        }
        
        if($file['error'] != 0){
            return $file['error'];
        }else{
            return true;
        }
        return 9;
    }
    
    
    private static function Rename($filename){
        $ext = self::findexts($filename);
        $ran = time().'-';
        $ran .= rand(111111, 999999).'.';
        $pic = $ran.$ext;
        return $pic;
    }
    
    
    private static function findexts($filename){ // $_FILES[ELEMENT_NAME][NAME] or as reletive to this class $file[name]
        //end(explode(".",strtolower($filename));
        $filename = strtolower($filename);
        $exts = explode(".", $filename);
        $n = count($exts)-1;
        $exts = $exts[$n];
        return $exts;
    }
    
    
    private static function GetError($file){
            $fileErrors = array(
            1 => "UPLOAD_ERR_INI_SIZE: ",
            2 => "UPLOAD_ERR_FORM_SIZE: ",
            3 => "UPLOAD_ERR_PARTIAL: ",
            4 => "UPLOAD_ERR_NO_FILE: ",
            6 => "UPLOAD_ERR_NO_TMP_DIR: ",
            7 => "UPLOAD_ERR_CANT_WRITE: ",
            8 => "UPLOAD_ERR_EXTENSION: ",
            9 => "UPLOAD_ERR_UNKNOWN: "
            );
            $errorDetail = array(
            1, 2 => 'The size of the file uploaded exceeds the maximum upload size allowed by the server.',
            3 => 'The file was not completely uploaded.',
            4 => 'No file was selected to upload.',
            6 => 'This server is not configured for uploads.',
            7 => 'The target upload folder does not have the proper permissions.',
            8 => 'The type of file uploaded is not allowed.',
            9 => 'There was an unknown error while attempting to upload.'
            );
            return $fileErrors[$file['error']].$errorDetail[$file['error']];
    }
}


?>
<br /><br />
<form action="#" method="post" enctype="multipart/form-data" >
File to upload: <input type="file" name="upload" />
<input type="submit" value="Submit File."/>
</form>
