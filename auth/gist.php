<?php
include "config.php";
include "curl.php";

$response['status'] = "error";
$response['message'] = "";

try {
    $url = "https://api.github.com/gists";
    if (empty($_POST['source'])) {
        throw new Exception("Source is empty");
    }
    if (empty($_POST['extension'])) {
        throw new Exception("Extension is empty");
    }
    $extension = isset($_POST['extension']) ? $_POST['extension'] : '';
    $data['files']['source.'.$extension]['content'] = isset($_POST['source']) ? $_POST['source'] : '';
    $data['description'] = isset($_POST['description']) ? $_POST['description'] : '';
    $data['public'] = isset($_POST['public']) ? $_POST['public'] : '';

    $params = json_encode($data);
    //var_dump($params);
    $response['status'] = 'OK';
    $response['message'] = httpRequest($url, $params);

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    die (json_encode($response));
}

echo json_encode($response);

?>