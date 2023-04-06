<?

use Aws\CommandPool;
use Aws\S3\S3Client;
use Guzzle\Service\Exception\CommandTransferException;

class Minio implements ImageStorage {
    private $s3;
    private $bucket;
    private $image_url;
    function __construct($Endpoint = CONFIG['MINIO_ENDPOINT'], $Key = CONFIG['MINIO_KEY'], $Secret = CONFIG['MINIO_SECRET'], $Bucket = CONFIG['MINIO_BUCKET'], $ImageUrl = CONFIG['IMAGE_URL']) {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'endpoint' => $Endpoint,
            'region'  => 'us-east-1',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $Key,
                'secret' => $Secret,
            ],
        ]);
        $this->bucket = $Bucket;
        $this->image_url = $ImageUrl;
    }
    private function image_path($key) {
        return  $this->image_url . "/$key";
    }
    public function upload($Name, $Content) {
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($Content);
        $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $Name,
            'Body'   => $Content,
            'ContentType' => $mime_type,
        ]);
        return $this->image_path($Name);
    }

    public function multi_upload($Datas) {
        $commands = [];
        foreach ($Datas as $Data) {
            $Content = $Data['Content'];
            $Name = $Data['Name'];
            $commands[] = $this->s3->getCommand('PutObject', [
                'Bucket' => $this->bucket,
                'Key'    => $Name,
                'Body'   => $Content,
                'ContentType' => $Data['MimeType'],
            ]);
        }
        $ret = [];
        try {
            $results = CommandPool::batch($this->s3, $commands);
            foreach ($results as $Idx => $Result) {
                if ($Result instanceof \Aws\Result) {
                    $ret[] = $this->image_path($Datas[$Idx]['Name']);
                } else {
                    if ($Result->getStatusCode() != 200) {
                        throw new Exception("Upload failed, status code: " . $Result->getStatusCode() . " message: " . $Result->getAwsErrorMessage());
                    }
                }
            }
        } catch (CommandTransferException $e) {
            foreach ($e->getFailedCommands() as $failedCommand) {
                throw new Exception($e->getExceptionForFailedCommand($failedCommand)->getMessage());
            }
        }
        return $ret;
    }
}
