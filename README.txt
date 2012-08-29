Uploader-phpVMS - File Upload addon for phpVMS
 Copyright &copy; 2012 Timothy Sturm

phpVMS - Virtual Airline Administration Software
 Copyright (c) 2008 Nabeel Shahzad

 phpVMS and Uploader-phpVMS is licenced under the following license:
   Creative Commons Attribution Non-commercial Share Alike (by-nc-sa)
   View license.txt in the root, or visit:
	http://creativecommons.org/licenses/by-nc-sa/3.0/
---

INSTALLATION

Make a backup of your site, and it's database.

Open the SQL document uploader.sql.txt located in the core directory of this package.


edit everywhere you see "phpvms_" to match your table prefix.


Note: If you do not know your table prefix it will be located in your local.config.php
Normally located in the core directory, the table prefix is normally defined on line 20


Run the SQL queries and then delete the file uploader.sql.txt


Delete test.php and ~test.php.htm if they exist


Upload the remaining contents to your site.


---

USAGE

During usage of this script keep in mind that, as a security measure, all files uploaded will be renamed to a random sequence to numbers in the format of xxxxxxxxxx-xxxxxx But it will keep it's original extension.


To edit any options, they will be located in core/common/Uploader.class.php on lines 23, 24, 25


To use this addon create an HTML forum and make sure it has the enctype attribute set to multipart/form-data like so &lt;form enctype="multipart/form-data"&gt; and you have a file input element, for example: &lt;input type="file" name=HTML_FILE_UPLOAD_ELEMENT /&gt;

When a user submits the form with a file process with php it like so:
Uploader::Upload($_FILES[HTML_FILE_UPLOAD_ELEMENT], $target);
Where $target is the directory to upload to, use of SITE_ROOT constant is recommended. However in theory you could use SITE_URL although this has not been tested, for example: $target = SITE_ROOT.'lib'.DS.'images';

To delete an uploaded file, process with php like so:
Uploader::DeleteUpload($target);
Where $target is the uploaded file to be deleted, use of SITE_ROOT constant is recommended. However in theory you could use SITE_URL although this has not been tested, for example: $target = SITE_ROOT.'lib'.DS.'images'.DS.'xxxxxxxxx - xxxxxxxxx';


Keep in mind this addon will not delete a file if it was not uploaded by this script.


---

 For more information on phpVMS, visit www.phpvms.net
	Forums: http://forum.phpvms.net
	Documentation: http://docs.phpvms.net
