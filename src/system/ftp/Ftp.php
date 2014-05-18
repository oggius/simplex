<?php
/**
 * Created by PhpStorm.
 * User: hammond
 * Date: 3/31/14
 * Time: 12:44 AM
 */

namespace system\ftp;

/**
 * Class Ftp
 * @package system\ftp
 */
class Ftp {

    /**
     * @var resource
     */
    protected $_ftpConnection;

    /**
     * @param $host
     * @param int $port
     * @param string $login
     * @param string $password
     * @param bool $passiveMode
     */
    public function __construct($host, $port = 21, $login = '', $password = '', $passiveMode = null)
    {
        $ftp = ftp_connect($host, $port);
        if ($ftp) {
            $this->_ftpConnection = $ftp;
            if (!empty($login)) {
                $this->login($login, $password);
            }

            // set passive mode if needed
            if (is_bool($passiveMode)) {
                ftp_pasv($this->_ftpConnection, $passiveMode);
            }
        }
    }
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @param $login
     * @param $password
     * @return bool
     */
    public function login($login, $password)
    {
        if (!$this->_ftpConnection) {
            return false;
        }
        return ftp_login($this->_ftpConnection, $login, $password);
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        if (!$this->_ftpConnection) {
            return false;
        }

        return ftp_close($this->_ftpConnection);
    }

    /**
     * @param $passiveModeFlag
     * @return bool
     */
    public function setPassiveMode($passiveModeFlag)
    {
        if (!$this->_ftpConnection) {
            return false;
        }

        return ftp_pasv($this->_ftpConnection, $passiveModeFlag);
    }

    /**
     * @param $dirName
     * @return bool
     */
    public function setDirectory($dirName)
    {
        if (!$this->_ftpConnection) {
            return false;
        }

        return ftp_chdir($this->_ftpConnection, $dirName);
    }

    /**
     * @param $filePath
     * @param null $newFileName
     * @param null $fileDir
     * @return bool
     */
    public function putFile($filePath, $newFileName = null, $fileDir = null)
    {
        if ($this->_ftpConnection && is_file($filePath)) {
            $localFile = fopen($filePath, 'r');
            if ($newFileName) {
                $fileName = $newFileName;
            } else {
                $fileName = basename($filePath);
            }
            return ftp_fput($this->_ftpConnection, $fileName, $localFile, FTP_BINARY);
        }
    }

    /**
     * @param $fileHandle
     * @param $fileName
     * @param null $fileDir
     * @return bool
     */
    public function getFile($fileHandle, $fileName, $fileDir = null)
    {
        if (is_resource($fileHandle) && !empty($fileName)) {
            if (!empty($fileDir)) {
                $this->setDirectory($fileDir);
            }

            return ftp_fget($this->_ftpConnection, $fileHandle, $fileName, FTP_ASCII);
        }
    }

    /**
     * @param $fileName
     * @param null $fileDir
     */
    public function getFileContent($fileName, $fileDir = null)
    {}

    /**
     * @param null $directory
     * @return array|bool
     */
    public function getFilesList($directory = null)
    {
        if (!$this->_ftpConnection) {
            return false;
        }
        if ($directory === null) {
            $directory = '.';
        }
        return ftp_nlist($this->_ftpConnection, $directory);
    }
} 