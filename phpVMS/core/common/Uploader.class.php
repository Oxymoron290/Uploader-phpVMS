<?php
/**
 * phpVMS - Virtual Airline Administration Software
 * Copyright (c) 2008 Nabeel Shahzad
 * For more information, visit www.phpvms.net
 *	Forums: http://www.phpvms.net/forum
 *	Documentation: http://www.phpvms.net/docs
 *
 * phpVMS is licenced under the following license:
 *   Creative Commons Attribution Non-commercial Share Alike (by-nc-sa)
 *   View license.txt in the root, or visit http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * @author Timothy Sturm
 * @project Uploader-phpVMS
 * @copyright Copyright (c) 2012, Timothy Sturm
 * @frameworkLink http://www.phpvms.net
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/
 */


class Uploader extends CodonData {

    /**
     * Method used to check the folder you are uploading to.
     * Also checks to see if uploading is enabled.
     *
     * @param string optional $folder The directory to check permission for. Use SITE_URL reletivity.
     * @return bool bool
     * 
     */
    public static function CheckUpload($folder=false){
        if(Config::Get('UPLOADS_ENABLED') == false){
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
        
        if($folder == false && Config::Get('UPLOADS_ENABLED') == true){
            LogData::addLog(Auth::$userinfo->pilotid, 'A check for file uploads was conducted with no target folder specified.');
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
        if(self::CheckUpload($target) == false){
            LogData::addLog(Auth::$userinfo->pilotid, 'A file upload was attempted, but denied due to local settings.');
            return false;
        }

        $check = self::CheckFile($file);
        if($check != true){
            LogData::addLog(Auth::$userinfo->pilotid, self::GetError($check));
            return false;
        }
        
        $pic = self::Rename($file['name']);
        $target = $target.'/'.$pic;
        
        if(is_uploaded_file($file['tmp_name'])){
            if(move_uploaded_file($file['tmp_name'], $target)){
                $target2 = str_replace(SITE_ROOT, SITE_URL.DS, $target);
                LogData::addLog(Auth::$userinfo->pilotid, 'The file "'.$file['name'].'" was successfully uploaded <a href="'.$target2.'">here</a>');
                self::LogUpload($target, $target2);
                return $target2;
            }else{
                LogData::addLog(Auth::$userinfo->pilotid, self::GetError($file));
                return false;
            }
        }else{
            LogData::addLog(Auth::$userinfo->pilotid, 'The file "'.$file['name'].'" was not uploaded due to a possible attack from '.$_SERVER["REMOTE_ADDR"]);
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
    
    
    /**
     * Method used to check if file was uploaded
     * 
     * @param string $url the exact URL to the file.
     * @return int bool Returns the id of the pilot who uploaded the file on success.
     * 
     */
    public static function GetUpload($url){
        $sql = 'SELECT * FROM `' . TABLE_PREFIX . "uploads` WHERE `site_url`='".$url."'";
        $myresult = DB::get_row($sql);
        if(isset($myresult->pilotID)){
            return $myresult->pilotID;
        }else{
            return 'there was an error';
        }
        return false;
    }
    
    
    private static function LogUpload($site_root, $site_url){
        $user = Auth::$userinfo->pilotid;
        $sql = 'INSERT INTO `' . TABLE_PREFIX . "uploads`
						   (`site_root`, `site_url`, `pilotID`)
					VALUES ('$site_root', '$site_url', '$user')";;
        DB::query($sql);
        return;
    }
    
    
    private static function RemoveLog($url){
        $file = end(explode(DS, $url));
        LogData::addLog(Auth::$userinfo->pilotid, 'The file "'.$file.'" was successfully Deleted.');
        $sql = "DELETE FROM " . TABLE_PREFIX . "uploads 
					WHERE `site_url`='$url'";
        DB::query($sql);
        return;
    }
    
    
    private static function CheckFile($file){
        if($file['error'] != 0){
            return $file['error'];
        }
        if(in_array(end(explode(".",strtolower($file['name']))), Config::Get('UPLOADS_DENIED'))){
            $file['error'] = 8;
        }
        
        if(!in_array(end(explode(".",strtolower($file['name']))), Config::Get('UPLOADS_ALLOWED'))){
            $file['error'] = 8;
        }
        
        if(in_array(end(explode(".",strtolower($file['name']))), Config::Get('UPLOADS_ALLOWED'))){
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