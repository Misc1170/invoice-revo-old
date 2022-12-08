<?php

class StorageDatabase{

    private $connection;

    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $database,
        $cert_path
    )
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = mysqli_init();
        $this->connection->ssl_set(NULL, NULL, $cert_path, NULL, NULL);
        
        $this->connection->real_connect(
            $host,
            $user, 
            $password,
            $database, 
            $port, 
            NULL,
            MYSQLI_CLIENT_SSL
        );
    }

    public function getConnection()
    {
        return $this->connection;
    }

}