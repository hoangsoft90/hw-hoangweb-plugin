<?php
/**
 * Class to dynamically create a zip file (archive) of file(s) and/or directory
 *
 * @author Rochak Chauhan  www.rochakchauhan.com
 * @package CreateZipFile
 * @see Distributed under "General Public License"
 * 
 * @version 1.0
 */
/**
 * Class CreateZipFile
 */
class CreateZipFile {

	public $compressedData = array();
	public $centralDirectory = array(); // central directory
	public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
	public $oldOffset = 0;

	/**
	 * Function to create the directory where the file(s) will be unzipped
	 *
	 * @param string $directoryName
	 * @access public
	 * @return void
	 */	
	public function addDirectory($directoryName) {
		$directoryName = str_replace("\\", "/", $directoryName);
		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x0a\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("v", strlen($directoryName) );
		$feedArrayRow .= pack("v", 0 );
		$feedArrayRow .= $directoryName;
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$feedArrayRow .= pack("V",0);
		$this->compressedData[] = $feedArrayRow;
		$newOffset = strlen(implode("", $this->compressedData));
		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x0a\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x00\x00\x00\x00";
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("V",0);
		$addCentralRecord .= pack("v", strlen($directoryName) );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("V", 16 );
		$addCentralRecord .= pack("V", $this->oldOffset );
		$this->oldOffset = $newOffset;
		$addCentralRecord .= $directoryName;
		$this->centralDirectory[] = $addCentralRecord;
	}

	/**
	 * Function to add file(s) to the specified directory in the archive 
	 *
	 * @param string $directoryName
	 * @param string $data
	 * @return void
	 * @access public
	 */	
	public function addFile($data, $directoryName)   {
		$directoryName = str_replace("\\", "/", $directoryName);
		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x14\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x08\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";
		$uncompressedLength = strlen($data);
		$compression = crc32($data);
		$gzCompressedData = gzcompress($data);
		$gzCompressedData = substr( substr($gzCompressedData, 0, strlen($gzCompressedData) - 4), 2);
		$compressedLength = strlen($gzCompressedData);
		$feedArrayRow .= pack("V",$compression);
		$feedArrayRow .= pack("V",$compressedLength);
		$feedArrayRow .= pack("V",$uncompressedLength);
		$feedArrayRow .= pack("v", strlen($directoryName) );
		$feedArrayRow .= pack("v", 0 );
		$feedArrayRow .= $directoryName;
		$feedArrayRow .= $gzCompressedData;
		$feedArrayRow .= pack("V",$compression);
		$feedArrayRow .= pack("V",$compressedLength);
		$feedArrayRow .= pack("V",$uncompressedLength);
		$this->compressedData[] = $feedArrayRow;
		$newOffset = strlen(implode("", $this->compressedData));
		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x14\x00";
		$addCentralRecord .="\x00\x00";
		$addCentralRecord .="\x08\x00";
		$addCentralRecord .="\x00\x00\x00\x00";
		$addCentralRecord .= pack("V",$compression);
		$addCentralRecord .= pack("V",$compressedLength);
		$addCentralRecord .= pack("V",$uncompressedLength);
		$addCentralRecord .= pack("v", strlen($directoryName) );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("v", 0 );
		$addCentralRecord .= pack("V", 32 );
		$addCentralRecord .= pack("V", $this->oldOffset );
		$this->oldOffset = $newOffset;
		$addCentralRecord .= $directoryName;
		$this->centralDirectory[] = $addCentralRecord;
	}

	/**
	 * Function to return the zip file
	 *
	 * @return zipfile (archive)
	 * @access public
	 * @return void
	 */
	public function getZippedfile() {
		$data = implode("", $this->compressedData);
		$controlDirectory = implode("", $this->centralDirectory);
		return
		$data.
		$controlDirectory.
		$this->endOfCentralDirectory.
		pack("v", sizeof($this->centralDirectory)).
		pack("v", sizeof($this->centralDirectory)).
		pack("V", strlen($controlDirectory)).
		pack("V", strlen($data)).
		"\x00\x00";
	}

	/**
	 *
	 * Function to force the download of the archive as soon as it is created
	 *
	 * @param archiveName string - name of the created archive file
	 * @access public
	 * @return ZipFile via Header
	 */
	public function forceDownload($archiveName) {
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}

		// Security checks
		if( $archiveName == "" ) {
			echo "<html><title>Public Photo Directory - Download </title><body><BR><B>ERROR:</B> The download file was NOT SPECIFIED.</body></html>";
			exit;
		}
		elseif ( ! file_exists( $archiveName ) ) {
			echo "<html><title>Public Photo Directory - Download </title><body><BR><B>ERROR:</B> File not found.</body></html>";
			exit;
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=".basename($archiveName).";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($archiveName));
		readfile("$archiveName");
	}

	/**
	  * Function to parse a directory to return all its files and sub directories as array
	  *
	  * @param string $dir
	  * @access protected 
	  * @return array
	  */
	protected function parseDirectory($rootPath, $seperator="/"){
		$fileArray=array();
		$handle = opendir($rootPath);
		while( ($file = @readdir($handle))!==false) {
			if($file !='.' && $file !='..'){
				if (is_dir($rootPath.$seperator.$file)){
					$array=$this->parseDirectory($rootPath.$seperator.$file);
					$fileArray=array_merge($array,$fileArray);
				}
				else {
					$fileArray[]=$rootPath.$seperator.$file;
				}
			}
		}		
		return $fileArray;
	}

	/**
	 * Function to Zip entire directory with all its files and subdirectories 
	 *
	 * @param string $dirName
	 * @access public
	 * @return void
	 */
	public function zipDirectory($dirName, $outputDir) {
		if (!is_dir($dirName)){
			trigger_error("CreateZipFile FATAL ERROR: Could not locate the specified directory $dirName", E_USER_ERROR);
		}
		$tmp=$this->parseDirectory($dirName);
		$count=count($tmp);
		$this->addDirectory($outputDir);
		for ($i=0;$i<$count;$i++){
			$fileToZip=trim($tmp[$i]);
			$newOutputDir=substr($fileToZip,0,(strrpos($fileToZip,'/')+1));
			$outputDir=$outputDir.$newOutputDir;
			$fileContents=file_get_contents($fileToZip);
			$this->addFile($fileContents,$fileToZip);
		}
	}
}

/**
 * Class HW_Unzipper
 */
class HW_Unzipper {
    public $localdir = '.';
    public $zipfiles = array();
    public static $status = '';

    /**
     * construct method
     */
    public function __construct() {
        //read directory and pick .zip and .gz files
        $this->find_ziped();
        //check if an archive was selected for unzipping
        //check if archive has been selected
        $input = '';
        $input = strip_tags($_POST['zipfile']);
        //allow only local existing archives to extract
        if ($input !== '') {
            if (in_array($input, $this->zipfiles)) {
                self::extract($input, $this->localdir);
            }
        }
    }

    /**
     * look up ziped files in directory
     * @param string $dir
     */
    public function find_ziped($dir='') {
        if(!$dir) $dir = $this->localdir;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== FALSE) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip'
                    || pathinfo($file, PATHINFO_EXTENSION) === 'gz'
                ) {
                    $this->zipfiles[] = $file;
                }
            }
            closedir($dh);
        }
    }

    /**
     * @param $archive
     * @param $destination
     */
    public static function extract($archive, $destination) {
        $ext = pathinfo($archive, PATHINFO_EXTENSION);
        if ($ext === 'zip') {
            self::extractZipArchive($archive, $destination);
        }
        else {
            if ($ext === 'gz') {
                self::extractGzipFile($archive, $destination);
            }
        }
    }
    /**
     * Decompress/extract a zip archive using ZipArchive.
     *
     * @param $archive
     * @param $destination
     */
    public static function extractZipArchive($archive, $destination) {
        // Check if webserver supports unzipping.
        if(!class_exists('ZipArchive')) {
            self::$status = '<span style="color:red; font-weight:bold;font-size:120%;">Error: Your PHP version does not support unzip functionality.</span>';
            return;
        }
        $zip = new ZipArchive;
        // Check if archive is readable.
        if ($zip->open($archive) === TRUE) {
            // Check if destination is writable
            if(is_writeable($destination . '/')) {
                $zip->extractTo($destination);
                $zip->close();
                self::$status = '<span style="color:green; font-weight:bold;font-size:120%;">Files unzipped successfully</span>';
            }
            else {
                self::$status = '<span style="color:red; font-weight:bold;font-size:120%;">Error: Directory not writeable by webserver.</span>';
            }
        }
        else {
            self::$status = '<span style="color:red; font-weight:bold;font-size:120%;">Error: Cannot read .zip archive.</span>';
        }
    }
    /**
     * Decompress a .gz File.
     *
     * @param $archive
     * @param $destination
     */
    public static function extractGzipFile($archive, $destination) {
        // Check if zlib is enabled
        if(!function_exists('gzopen')) {
            self::$status = '<span style="color:red; font-weight:bold;font-size:120%;">Error: Your PHP has no zlib support enabled.</span>';
            return;
        }
        $filename = pathinfo($archive, PATHINFO_FILENAME);
        $gzipped = gzopen($archive, "rb");
        $file = fopen($filename, "w");
        while ($string = gzread($gzipped, 4096)) {
            fwrite($file, $string, strlen($string));
        }
        gzclose($gzipped);
        fclose($file);
        // Check if file was extracted.
        if(file_exists($destination . '/' . $filename)) {
            self::$status = '<span style="color:green; font-weight:bold;font-size:120%;">File unzipped successfully.</span>';
        }
        else {
            self::$status = '<span style="color:red; font-weight:bold;font-size:120%;">Error unzipping file.</span>';
        }
    }

    /**
     * get all files at level 0 from ziped file
     * @param $zip
     */
    public static function get_root_files_fromzip($file) {
        //valid
        if(!file_exists($file) || HW_File_Directory::getExtension($file) !='zip') return ;
        $zip = new ZipArchive;
        $result = array();
        if ($zip->open($file) == TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                //check file in subfolder
                if(count(explode('/', $filename))) continue;    //ignore file in sub folder
                if(HW_File_Directory::has_extension($filename)) $result[] = $filename;
            }
        }
        return $result;
    }

    /**
     * @param $file
     */
    public static function get_root_dirs_fromzip($file) {
        //valid
        if(!file_exists($file) || HW_File_Directory::getExtension($file) !='zip') return ;
        $zip = new ZipArchive;
        $result = array();
        if ($zip->open($file) == TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                //check file in subfolder
                if(count(explode('/', trim($filename,'\/'))) //ignore depth of sub folders
                    || HW_File_Directory::has_extension($filename)) continue;

                $result[] = $filename;
            }
        }
        return $result;
    }
}
?>