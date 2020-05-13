<?php
include "config.php";
include "curl.php";

$response['status'] = "error";
$response['message'] = "";

$api_user = $_config['ideone_api_user'];
$api_pass = $_config['ideone_api_password'];

$client = new SoapClient( "http://ideone.com/api/1/service.wsdl" ); //create new SoapClient

function checkStatus($link) {
    global $client;
    global $api_user;
    global $api_pass;
    return $client->getSubmissionStatus( $api_user, $api_pass, $link );
}

function checkDetails($link) {
    global $client;
    global $api_user;
    global $api_pass;
    return $client->getSubmissionDetails( $api_user, $api_pass, $link, true, true, true, true, true );
}
function submit($code, $lang, $input, $run, $private ) {
    global $client;
    global $api_user;
    global $api_pass;
    return $client->createSubmission( $api_user, $api_pass, $code, $lang, $input, $run, $private );
}
function check($link) {
    $status = checkStatus($link);
    if ( $status['error'] == 'OK' ) {
        $stat_code = $status['status'];
        if ($stat_code < 0)
        {
            $stat = "Pending";
            $details = null;
        }
        else if ($stat_code == 1)
        {
            $stat = "Compiling";
            $details = null;
        }
        else if ($stat_code == 3)
        {
            $stat = "Running";
            $details = null;
        }
        else if ($stat_code == 0)
        {
            $stat = "Done";
            $details = checkDetails($link);
            if ( $details['error'] == 'OK' ) {

                // Translate 'result' code
                $result_string = '';
                switch ($details['result'])
                {
                    case 0:
                        $result_string = "Not running";
                    break;
                    case 11:
                        $result_string = "Compilation error";
                    break;
                    case 12:
                        $result_string = "Runtime error";
                    break;
                    case 13:
                        $result_string = "Time limit exceeded";
                    break;
                    case 15:
                        $result_string = "Success";
                    break;
                    case 17:
                        $result_string = "Memory limit exceeded";
                    break;
                    case 19:
                        $result_string = "Illegal system call";
                    break;
                    case 20:
                        $result_string = "Internal error";
                    break;
                }
                $details['result_string'] = $result_string;

            } else {
                throw new Exception("checkDetails: ". $details['error']);
            }
        }
    } else {
        throw new Exception("checkStatus: " . $status['error']);
    }
    return array('status' => $stat, 'details' => $details);
}

try {
    if (empty($_POST['action'])) {
        throw new Exception("Action is empty");
    }
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $language = isset($_POST['language']) ? $_POST['language'] : '';
    $source = isset($_POST['source']) ? $_POST['source'] : '';
    $input = isset($_POST['input']) ? trim($_POST['input']) : '';
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';
    
    $run     = true;
    $private = true;
    if (strlen($input) > 1) {
        if ($input[strlen($input)-1] != "\n") {
            $input .= "\n";
        }
    }
    
    if ($action == 'submit') {
        if (empty($_POST['source'])) {
            throw new Exception("Source is empty");
        }
        if (empty($_POST['language'])) {
            throw new Exception("Language is empty");
        }
        $result = $client->createSubmission( $api_user, $api_pass, $source, $language, $input, $run, $private );

        if ( $result['error'] == 'OK' ) {
            $link = $result['link'];
            $status = check($link);
        } else {
            throw new Exception ("createSubmission: " . $result['error']);
        }
    } else {
        $status = check($link);
    }
   

    //var_dump($params);
    $response['status'] = 'OK';
    $response['message'] = $status;
    $response['message']['link'] = $link;
    $response['message']['date'] = date("Y-m-d\TH:i:s\Z");

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    die (json_encode($response));
}

echo json_encode($response);

?>