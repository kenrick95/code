<?php
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name('login_session'), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
setcookie("login_session_cookie", "", 0, "/");

// Finally, destroy the session.
session_destroy();

?>