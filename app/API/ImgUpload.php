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
        if (empty($_POST['name']) || empty($_POST['urls'])) {
            $response["Error"] = "Invalid Request";
            return $response;
        }

        $user_id = $LoggedUser['UserID'];
        $names = $_POST['name'];
        $urls = $_POST['urls'];
        $Data = [];
        
        for ($i = 0; $i < count($names); $i += 1) {
            $name = $names[$i];
            $url = $urls[$i];
            $Data[] = ['Url' => $url, 'Name' => $name];
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
