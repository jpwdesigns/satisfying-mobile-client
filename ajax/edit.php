<?php 
require_once('init/inc.init.php'); 
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $_SERVER['REQUEST_METHOD'] == "POST") {
    // pass POST vars to object
    $satisfying->setRequestBody($_POST);
    // manually set rest_endpoing because setRestEndpoint isn't recoginzing the url correctly
    $satisfying->rest_endpoint = $_POST['url'];
    // send request
    $response = $satisfying->sendRequest('satisfaction',array(),'PUT');
    // send back header with status and response message
    header('HTTP/1.1 '.$response->getStatus().' '.$response->getReasonPhrase());
} else {
    header('HTTP/1.1 405 Method Not Allowed');
}
?>