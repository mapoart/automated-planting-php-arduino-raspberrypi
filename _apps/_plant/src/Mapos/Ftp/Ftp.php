<?php
namespace Mapos\Ftp;

class Ftp
{
    private $connection;

    private $host = '127.0.0.1';

    private $port = '21';

    private $login;

    private $password;

    private $remoteDir = "";

    private $loggedIn;

    public function connect()
    {
        $this->connection = @ftp_connect($this->host);

        ftp_set_option($this->connection, FTP_AUTOSEEK, TRUE);

        return $this->connection ? true : false;
    }

    private function login($username, $password)
    {
        return ftp_login($this->connection, $this->login, $this->password);
    }

    public function upload($source, $destination)
    {
        if ($this->loggedIn = $this->login($this->login, $this->password)) {

            //$file_size = @ftp_size($this->connection, $this->remoteDir . $destination);
            return ftp_put($this->connection, $this->remoteDir . $destination, $source, FTP_BINARY, FTP_AUTORESUME);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getRemoteDir()
    {
        return $this->remoteDir;
    }

    /**
     * @param mixed $remoteDir
     */
    public function setRemoteDir($remoteDir)
    {
        $this->remoteDir = $remoteDir;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }


}