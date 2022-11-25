<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

class StorageFileService{

    private $bucketName;
    private $s3_connection;

    public function __construct(
        $bucketName = 'test'
    )
    {
        $this->bucketName = $bucketName;

        // AWS_ACCESS_KEY_ID и AWS_SECRET_ACCESS_KEY 
        // должны быть утсановлены в переменных окружения
        $this->s3_connection = new S3Client([
            'version' => 'latest',
            'endpoint' => 'https://storage.yandexcloud.net',
            'region' => 'ru-central1',
        ]);
    }

    /**
     * @throws AwsException
     * @throws S3Exception
     */
    public function upload($filepath, $file_content)
    {
        return $this->s3_connection->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $filepath,
            'Body' => $file_content
        ]);
    }

    /**
     * @throws AwsException
     * @throws S3Exception
     */
    public function downloadAs($filepath, $saveAsPath){
        return $this->s3_connection->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $filepath,
            'SaveAs' => $saveAsPath
        ]);
    }

    /**
     * @throws AwsException
     * @throws S3Exception
     */
    public function doesObjectExists($filepath){
        return $this->s3_connection->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $filepath
        ]);
    }

    /**
     * @throws AwsException
     * @throws S3Exception
     */
    public function headObject($filepath)
    {
        return $this->s3_connection->headObject([
            'Bucket' => $this->bucketName,
            'Key' => $filepath
        ]);
    }

}