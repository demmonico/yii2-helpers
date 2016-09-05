<?php /**
 * @author: dep
 * Date: 25.01.16
 */

namespace demmonico\helpers;


/**
 * Class for work with files
 */
class FileHelper
{
    const PERMISSIONS_FOLDER = 02771;
    const PERMISSIONS_FILE = 0664;

    private static $_mimeTypes;



// File operations

    /**
     * Wrapper for PHP chmod with verifications
     * @param $path
     * @param null $permissions
     * @return bool
     */
    public static function chmod($path, $permissions=null)
    {
        if (is_null($permissions))
            $permissions = is_dir($path) ? self::PERMISSIONS_FOLDER : self::PERMISSIONS_FILE;
        return self::isWindows() || self::isOwner($path) AND chmod($path, $permissions);
    }

    /**
     * Wrapper for PHP chmod with verifications
     * @param $file
     * @return bool
     */
    public static function unlink($file)
    {
        return is_file($file) && unlink($file);
    }

    /**
     * Wrapper for PHP chmod with verifications
     * @param $oldFile
     * @param $newFile
     * @return bool
     */
    public static function move($oldFile, $newFile)
    {
        if (file_exists($oldFile)){
            $dir = dirname($newFile);
            if (is_dir($dir) || self::mkdir($dir))
                return rename($oldFile, $newFile);
        }
        return false;
    }

    /**
     * Wrapper for PHP chmod with verifications
     * @param $dir
     * @return bool|null
     */
    public static function isDirEmpty($dir)
    {
        if (!is_readable($dir)) return NULL;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Removes recursively directory if it isn't empty
     * @param $path
     * @param array $stopFolders
     */
    public static function rmEmptyPath($path, $stopFolders=[])
    {
        while($path && is_dir($path) && self::isDirEmpty($path)){
            $path = trim( $path, DIRECTORY_SEPARATOR);

            // check stopFolder
            $folder = (false !== $pos=strrpos($path, DIRECTORY_SEPARATOR)) ? substr($path, $pos+1) : $path;
            if (in_array($folder, $stopFolders)) break;

            // rmdir
            try{
                rmdir($path);
            } catch(\Exception $e){
                \Yii::error(__METHOD__.': Error - ',$e->getMessage());
                break;
            }

            $path = trim( substr($path, 0, strpos($path, $folder)), DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Verification for owner's permission (for Unix OS only!)
     * @param $path
     * @return bool
     */
    public static function isOwner($path)
    {
        if (file_exists($path) && function_exists('posix_geteuid')){
            $userIdProcess = posix_geteuid();
            $userIdOwner = fileowner($path);
            return $userIdOwner===$userIdProcess;
        }
        return false;
    }

    /**
     * Checks for Win OS
     * @return bool
     */
    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

// Yii wrappers

    /**
     * Wrapper for Yii [createDirectory] with right permissions
     * @param $dir
     * @return bool
     */
    public static function mkdir($dir)
    {
        return \yii\helpers\FileHelper::createDirectory($dir, self::PERMISSIONS_FOLDER);
    }

    /**
     * Wrapper for Yii [getAlias] with replacement right directory separator (DS) and suffixed by DS (optional)
     * @param $alias
     * @param bool $isSuffixed
     * @return bool|string
     */
    public static function alias2path($alias, $isSuffixed=true)
    {
        $path = \Yii::getAlias($alias);

        // replace "/" and "\" with DIRECTORY_SEPARATOR
        if (DIRECTORY_SEPARATOR === '/')
            $path = strtr($path, ['\\' => DIRECTORY_SEPARATOR]);
        elseif (DIRECTORY_SEPARATOR === '\\')
            $path = strtr($path, ['/' => DIRECTORY_SEPARATOR]);

        // in the end add DIRECTORY_SEPARATOR if need it
        if ($isSuffixed && DIRECTORY_SEPARATOR !== substr($path, -1))
            $path .= DIRECTORY_SEPARATOR;

        return $path;
    }



// File info

    /**
     * Returns max filesize which is allowed to upload
     * @param int $adminFileSize
     * @return mixed
     */
    public static function getMaxFileSizeUpload($adminFileSize=0)
    {
        $arr = [];
        if ($adminFileSize)
            $arr[] = self::normalizeBytes($adminFileSize);
        foreach(['upload_max_filesize', 'post_max_size', 'memory_limit'] as $param){
            $arr[] = self::normalizeBytes(ini_get($param));
        }
        return min($arr);
    }

// Yii wrappers

    /**
     * Returns mime type group by full mime type based on Yii mime types data
     * @param $mimeType
     * @return null|string
     */
    public static function getMimeTypeGroup($mimeType)
    {
        if (!empty($mimeType)){
            $part = explode('/', $mimeType);
            if (isset($part[0]))
                return trim($part[0]);
        }
        return null;
    }

    /**
     * Returns mime type group by file using Yii mime types mechanism
     * @param $file
     * @return null|string
     */
    public static function getMimeTypeGroupByFile($file)
    {
        $mimeType = \yii\helpers\FileHelper::getMimeType($file);
        return self::getMimeTypeGroup($mimeType);
    }

    /**
     * Returns mime types based on Yii mime types data
     * @return array
     */
    public static function getMimeTypesFromYii()
    {
        try {
            if (isset(\yii\helpers\FileHelper::$mimeMagicFile)){
                $magicFile = \Yii::getAlias( \yii\helpers\FileHelper::$mimeMagicFile );
                if (!isset(self::$_mimeTypes)) {
                    self::$_mimeTypes = require($magicFile);
                }
            }
            if (!is_array(self::$_mimeTypes) || empty(self::$_mimeTypes))
                throw new \Exception('Invalid mimeTypes file');
            return self::$_mimeTypes;
        } catch(\Exception $e){
            \Yii::error(__METHOD__.' MimeTypes file doesn\'t exists. Error: '.$e->getMessage());
            return [];
        }
    }



// FORMATTERS

    /**
     * Returns formatted string with float file size
     * (get from http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes)
     * @param $bytes
     * @param int $precision
     * @param boolean $useTrueConversion
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2, $useTrueConversion = true)
    {
        $units = array('b', 'Kb', 'Mb', 'Gb', 'Tb');

        $bytes = max($bytes, 0);
        if ($useTrueConversion)
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));       // for divide 1024
        else
            $pow = floor(($bytes ? log($bytes) : 0) / log(1000));       // for divide 1000
        $pow = min($pow, count($units) - 1);

        if ($useTrueConversion)
            $bytes /= pow(1024, $pow);              // for divide 1024
        else
            $bytes /= pow(1000, $pow);              // for divide 1000
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Normalize filesize from formatted bytes/Mb/Gb... to bytes
     * Get 2000000 from 2M used $base=1000
     * @param $input
     * @param $base
     * @return int|string
     */
    public static function normalizeBytes($input, $base=1024)
    {
        $max_int_val = 2147483647;
        $r = $input;
        if (!empty($r)){
            if (!is_numeric($r)) {
                $n = strlen($r)-1;
                $symbol = $r[$n];
                $r = substr($r, 0, $n);
                if (is_numeric($r)) switch ($symbol){
                    case 'M':
                    case 'm':
                        $r = (int)$r * $base * $base;
                        break;
                    case 'K':
                    case 'k':
                        $r = ($r = substr($r, 0, $n) !== '') ? $r * $base : $max_int_val;
                        break;
                    default:
                        $r = self::normalizeBytes($r);
                } else {
                    $r = $max_int_val;
                }
            }

        } elseif ($r != 0 || $r == '') {
            $r = $max_int_val;
        }

        return (int)$r;
    }
}