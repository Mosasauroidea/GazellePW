<?
class Minio implements ImageStorage {
	private $s3;
	private $bucket;
	private $image_url;
	function __construct($Endpoint = MINIO_ENDPOINT, $Key = MINIO_KEY, $Secret = MINIO_SECRET, $Bucket = MINIO_BUCKET, $ImageUrl = IMAGE_URL) {
		$this->s3 = new Aws\S3\S3Client([
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
		$bucket = $this->bucket;
		return  $this->image_url . "/$bucket/$key";
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
}
