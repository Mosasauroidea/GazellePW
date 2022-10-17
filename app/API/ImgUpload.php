<?
namespace Gazelle\API;

class ImgUpload extends AbstractAPI {
    public function run() {

        $response = array();

        // Fetching the userid of the user by api key
        $LoggedUser = $this->cache->get_value("api_apps_{$_GET['api_key']}")[0];
        if (!is_array($LoggedUser)) {
            $json_response["error"] = "Unauthorized Access";
            return $json_response;
        }
        
        // Checking whether there is an image
        if (empty($_POST['urls'])) {
            $response["Error"] = "Invalid Request";
            return $response;
        }

        $user_id = $LoggedUser['UserID'];
        $urls = $_POST['urls'];
        $Data = [];
        
        for ($i = 0; $i < count($urls); $i += 1) {
            $url = $urls[$i];
            $extension = strtolower(end(explode(".", $url)));
            $path = 'user/' . $user_id . '/' . date('Ymd', time()) . '/' . uniqid() . '.' . $extension;
            $Data[] = ['Url' => $url, 'Name' => $path];
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
