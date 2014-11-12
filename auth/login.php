<?php
/*******************************
** TODO: IMPLEMENT MORE SECURITY
** READ MORE: https://developer.mozilla.org/en-US/Persona/Security_Considerations
**
**/
session_start();
include "config.php";
include "curl.php";

function verify ($assertion) {
    $url = "https://verifier.login.persona.org/verify";
    global $audience;
    $params = "assertion=".urlencode($assertion)."&audience=".urlencode($audience);
    $data = httpRequest($url, $params);
    return $data;
}

try {
    $assertion = filter_input(
    INPUT_POST,
    'assertion',
    FILTER_UNSAFE_RAW,
    FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH
);
    $audience = $_config['audience'];
    $verify = json_decode(verify($assertion), true);

    $response['status'] = "error";
    $response['message'] = "";

    if ($verify['status'] === 'okay') {
        $response['status'] = 'OK';
        $data['email'] = $verify['email'];
        $data['issuer'] = $verify['issuer'];
        $data['audience'] = $verify['audience'];
        $data['expires'] = $verify['expires'];

        $_SESSION['login_session'] = $data['email']; 
        setcookie("login_session_cookie", $_SESSION['login_session'], time() + 24 * 60 * 60, "/");
        //setcookie("login_session_cookie", $_SESSION['login_session'], (int) ($data['expires'] / 1000), "/");

        $response['message'] = $data;
    } else {
        $response['status'] = 'error';
        $response['message'] = $verify['reason'];
    }
    echo json_encode($response);
    

# '{"audience":"http://localhost:80","expires":1388052449798,"issuer":"gmail.login.persona.org","email":"kenrick95@gmail.com","status":"okay"}'

} catch (Exception $e) {
    //die("FAILED: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    die (json_encode($response));
}
?>