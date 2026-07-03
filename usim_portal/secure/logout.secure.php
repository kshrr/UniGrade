<?php
// 0. MUST BE CALLED BEFORE session_start() to target the correct vault
session_name('USIM_SECURE_SESSION'); 
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy the specific session cookie in the user's browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to login
header("Location: login_secure.php");
exit();
?>