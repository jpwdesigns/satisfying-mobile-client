<?php 
require_once('init/inc.init.php'); 
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // send request to meToo
    $response = $satisfying->meToo();
    header('HTTP/1.1 '.$response->getStatus());
} else {
    header('HTTP/1.1 405 Method Not Allowed');
}

?>