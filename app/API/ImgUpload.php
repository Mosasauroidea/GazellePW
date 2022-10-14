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
        if (empty($_FILES['images'])) {
            $response["Error"] = "Invalid Request";
            return $response;
        }

        $user_id = $LoggedUser['UserID'];
        $names = $_FILES["images"]['name'];
        $tmp_names = $_FILES["images"]['tmp_name'];
        $Data = [];
        
        for ($i = 0; $i < count($names); $i += 1) {
            $name = $names[$i];
            $tmp_name = $tmp_names[$i];
            $extension = strtolower(end(explode(".", $name)));
            if (!\ImageTools::valid_extension($extension)) {
                $response["Error"] = "Invalid ext: $extension";
                $response["name"] = "$name";
                $response["tmp_name"] = "$tmp_name";
                return $response;
            }
            $path = 'user/' . $user_id . '/' . date('Ymd', time()) . '/' . uniqid() . '.' . $extension;
            $Data[] = ['Url' => $tmp_name, 'Name' => $path];
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
