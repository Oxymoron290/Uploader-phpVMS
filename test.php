<?php

define('DS', DIRECTORY_SEPARATOR);
if(substr(dirname(__FILE__), -1) != DS){$site_root = dirname(__FILE__).DS;}
define('SITE_ROOT', str_replace('', '', $site_root));
define('SITE_URL', 'http://localhost');

class Uploader
{
    
    const uploads_enabled = true;
    protected static $uploads_ALLOWED = array("jpg","jpeg","gif","png"); // Allowed File Types, Mainly Images
    protected static $uploads_DENIED = array("php","htm","html","xml","css","cgi","xls","rtf","ppt","pdf","dll","swf","flv","avi","wmv","mov","class","bat","sh","java","iso","c","cpp","ini","js"); // Strictly Disallowed File types

    /**
     * Method used to check the folder you are uploading to.
     * Also checks to see if uploading is enabled.
     *
     * @param string optional $folder The directory to check permission for. Use SITE_URL reletivity.
     * @return bool bool
     * 
     */
    public static function CheckUpload($folder=false){
        if(self::uploads_enabled == true){
            if($folder == false){
                return true;
                //LogData::addLog(Auth::$userinfo->pilotid, 'A check for file uploads was conducted with no target folder specified.');
            }else{
                if($folder > '' && is_writeable($folder)){
                    return true;
                }
            }
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
    public static function Upload($file, $target){
        if($file['error'] == 4) return false;
        
        $tmpFile = pathinfo($file['tmp_name']);
        $tmpFile = $tmpFile['dirname'].DS.$tmpFile['basename'];
        
        if(!file_exists($tmpFile)) return false;
        
        $target = str_replace(SITE_URL.DS, SITE_ROOT, $target);
        if(self::CheckUpload($target) == false){
            //LogData::addLog(Auth::$userinfo->pilotid, 'A file upload was attempted, but denied due to local settings.');
            unlink($tmpFile);
            return false;
        }
        
        $check = self::CheckFile($file);
        if($check !== true){
            //LogData::addLog(Auth::$userinfo->pilotid, self::GetError($check));
            unlink($tmpFile);
            return false;
        }
            
        $pic = self::Rename($file);
        $target = $target.DS.$pic;
            
        if(is_uploaded_file($file['tmp_name'])){
                if(move_uploaded_file($file['tmp_name'], $target)){
                    $target2 = str_replace(SITE_ROOT, SITE_URL.DS, $target);
                    //LogData::addLog(Auth::$userinfo->pilotid, 'The file "'.$file['name'].'" was successfully uploaded <a href="'.$target2.'">here</a>');
                    //self::LogUpload($target, $target2);
                    if(file_exists($tmpFile)) unlink($tmpFile); // file should have been moved so this should never happen.
                    return $target2;
                }else{
                    //LogData::addLog(Auth::$userinfo->pilotid, self::GetError(intval($file['error'])));
                    if(file_exists($tmpFile)) unlink($tmpFile); // The only case this would happen is if the file wasn't moveable... this should never happen actually.
                    return false;
                }
            }else{
                //LogData::addLog(Auth::$userinfo->pilotid, 'The file "'.$file['name'].'" was not uploaded due to a possible attack from '.$_SERVER["REMOTE_ADDR"]);
                //unlink($tmpFile); // We will NOT want to delete the file it if !is_uploaded_file()
                return false; // File was not uploaded via HTTP POST
            }
    }
    
    
    /**
     * Method used to delete an uploaded file.
     * 
     * @param string $url the exact URL to the file.
     * @return bool bool
     * 
     */
    public static function DeleteUpload($url){
        if(self::GetUpload($url) != false){
            $target = str_replace(SITE_URL.DS, SITE_ROOT, $url);
            if(file_exists($target) && is_writeable($target)){
                unlink($target);
                self::RemoveLog($url);
                return true;
            }
        }
        return false;
    }
    
    
    private static function CheckFile($file){
        $file['name'] = strtolower($file['name']);
        //$ext = self::findexts($file['name']); // Changed as requested by Nabeel Shahzad
        $ext = pathinfo($file['name']);
        $ext = $ext['extension'];
        
        if($file['error'] != 0){
            return $file['error'];
        }
        
        if(in_array($ext, self::$uploads_DENIED)){
            $file['error'] = 8;
        }
        
        if(!in_array($ext, self::$uploads_ALLOWED)){
            $file['error'] = 8;
        }
        
        if(in_array($ext, self::$uploads_ALLOWED)){
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
    
    
    private static function Rename($file){
        //$ext = self::findexts($file); // Changed as requested by Nabeel Shahzad
        $ext = pathinfo($file['name']);
        $ext = $ext['extension'];
        $ran = time().'-';
        $ran .= rand(111111, 999999).'.';
        $pic = $ran.$ext;
        return $pic;
    }
    
    
    // will be replaced by pathinfo()
    private static function findexts($filename){ // $_FILES[ELEMENT_NAME][NAME] or as reletive to this class $file[name]
        //end(explode(".",strtolower($filename));
        $filename = strtolower($filename);
        $exts = explode(".", $filename);
        $n = count($exts)-1;
        $exts = $exts[$n];
        return $exts;
    }
    
    
    public static function DisplayFilesize($filesize){ // $_FILES[ELEMENT_NAME][SIZE] or as relative to this class $file[size]
        if(is_numeric($filesize)){
            $decr = 1024; $step = 0;
            $prefix = array('Byte','KB','MB','GB','TB','PB');
            
            while(($filesize / $decr) > 0.9){
                $filesize = $filesize / $decr;
                $step++;
            }
            return round($filesize,2).' '.$prefix[$step];
        }else{
            //return 'NaN';
            return false;
        }
    }
    
    
    private static function GetError($error){
            $error = intval($error);
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
            return $fileErrors[$error].$errorDetail[$error];
    }
}


echo '<br />';
$target = SITE_URL.DS.'images';
$target = SITE_ROOT.'images';
if(isset($_POST['action']) && isset($_FILES['upload'])){
    $test = Uploader::Upload($_FILES['upload'], $target);
    if($test == false){
        echo 'that file is not allowed or uploading is disabled.<br />'.$_FILES['upload']['name'].' Denied.';
    }else{
        if(stripos($test, SITE_URL.DS) !== false){
            echo 'Success!<br /><a href="?view='.$test.'">Click here</a> To see the file.';
        }else{
            echo $test;
        }
    }
}
if(isset($_GET['view'])){
    echo '<img src="'.$_GET['view'].'" /><br />';
}
?>
<br /><br />
<form action="#" method="post" enctype="multipart/form-data" >
File to upload: <input type="file" name="upload" /><br />
<input type="hidden" name="action" value="true" />
<input type="submit" value="Submit File."/>
</form>
