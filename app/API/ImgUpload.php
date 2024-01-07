<?

namespace Gazelle\API;

use Exception;

class ImgUpload extends AbstractAPI {
    public function run() {

        $response = array();

        // Checking whether there is an image
        if (empty($_POST['urls'])) {
            $response["Error"] = "Invalid Request";
            return $response;
        }

        $user_id = $this->user['ID'];
        $urls = $_POST['urls'];
        $Data = [];

        for ($i = 0; $i < count($urls); $i += 1) {
            $url = $urls[$i];
            $extension = strtolower(end(explode(".", $url)));
            if (!\ImageTools::valid_extension($extension)) {
                $response["Error"] = "Invalid ext: $extension";
                return $response;
            }
            $path = CONFIG['IMAGE_PATH_PREFIX'] . '/user/' . $user_id . '/' . date('Ymd', time()) . '/' . uniqid() . '.' . $extension;
            $Data[] = ['Url' => $url, 'Name' => $path, 'Ext' => $extension];
        }

        try {
            $RetPath = \ImageTools::multi_fetch_upload($Data);
        } catch (Exception $e) {
            $response["Error"] = $e->getMessage();
            return $response;
        }

        $Ret = [];
        foreach ($RetPath as $RetName) {
            $Ret[] = ['name' => $RetName];
        }

        $response["files"] = $Ret;
        return $response;
    }
}
