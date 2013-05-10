<?php
// include app configuration
require_once('app_config.php');

$root_dir = '/';
$errors_html = $errors_html_reply = '';
$resp = '';
$code = 0;
$_SERVER['DOCUMENT_ROOT'] = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

require_once('lib/classes/satisfying.class.php');

// settings for get satisfaction oauth
$auth_root = GET_SATISFACTION_OAUTH_ROOT;

// settings for get satisfaction json endpoints
$feed_root = GET_SATISFACTION_API_ROOT;

// company name. used in get statisfaction urls.
$company = GET_SATISFACTION_COMPANY_NAME;

// company id used to filter a users followed topics
$company_id = GET_SATISFACTION_COMPANY_ID;

// instantiate satisfying class
$satisfying = new Satisfying(GET_SATISFACTION_KEY, GET_SATISFACTION_SECRET, $auth_root, $feed_root, $company, $company_id);

// turn on/off debugging NOT for use in production
$satisfying->debug = GET_SATISFACTION_DEBUG;

//////////////////////////////////////////////////////
// logic for satisfying api class ////////////////////
//////////////////////////////////////////////////////
if ((isset($_GET['resp']) && (int)$_GET['resp']>400) || ((isset($code)) && (int)$code >400)) {

    // delete token cookies something went wrong with oAuth
    $satisfying->clearAuth();
    $satisfying->setAlert('Woops, there was a problem posting. Please try again.');
    if (isset($_COOKIE['postData'])) {
        $postData = $satisfying->cleanData(json_decode(stripslashes($_COOKIE['postData']), true));
    }
} else {
    if (isset($_COOKIE['token'])) {
        // get request token from cookie
        $satisfying->setToken($_COOKIE['token']);
    }
    if (isset($_COOKIE['token_secret'])) {
        // get request token secret from cookie
        $satisfying->setTokenSecret($_COOKIE['token_secret']);
    }  
}
// login
if ((isset($_REQUEST['login'])) && $_REQUEST['login']=='true'){
     $satisfying->clearAuth();
     $satisfying->getAuth();

// Post Logic + oAuth //      
// do if post 
} else if (!empty($_POST['action'])) {
    
    // save cookie of post data for processing upon return
    setcookie('postData', utf8_encode(json_encode($_POST)));
    $postData = $_POST;
    
    // if token is set, submit form to gs
    if ((isset($_COOKIE['token'])) && (!(empty($_COOKIE['token'])))) {

        // check for validation errors
        if ($satisfying->validatePost()) {

            // pass POST vars to object
            $satisfying->setRequestBody($_POST);
            // send request
            $response = $satisfying->sendRequest();
            // get response code
            $code = $response->getStatus();
            // string for redir url
            $resp = 'resp='.$code;  
            
        } else {
            // process errors, validation failed
            $errors = $satisfying->errors;
            if(array_key_exists('reply',$postData)) {
                $errors_html_reply = '<p>'.implode('<br />',$errors).'</p>';
            } else {
                $errors_html = '<p>'.implode('<br />',$errors).'</p>';
            }
            $errors = implode(PHP_EOL,$errors);
            $satisfying->setAlert($errors);
        }
        
        // 401 means not authenticated so redirect to get authentication
        if ((int)$code == 401) {
            // delete token cookies something went wrong with oAuth
            $satisfying->debug('dumping cookies because the code was:'.$code);
            $satisfying->clearAuth();
            $satisfying->getAuth();
        // 400 Bad Request, usually contains errors like "subject can't be blank" etc.. so inform user.
        } else if ((int)$code == 400){
            // process errors 
            preg_match_all('|\["{1}([a-z]+)[",]{3}([^"]+)|',$response->getBody(),$matches,PREG_SET_ORDER);
            $satisfying->debug($matches);
            if (is_array($matches)) {
                $errors = array();
                foreach ($matches as $array) {
                    if ($array[1] !== "slug") {
                        $errors[] = ucfirst($array[1]) . ' ' . $array[2];
                    }
                }
                $errors_html = '<p>'.implode('<br />',$errors).'</p>';
                $errors = implode('\n',$errors);
                $satisfying->setAlert($errors);
            }
        // 201 Success    
        } else if ((int)$code == 201){
            // get body of response
            $body = json_decode($response->getBody());
            // if this was a topic post we should redir to topic page
            if (!empty($body->subject)) {
                $topic_id = $body->id;
                // build url here and use it instead of getCurPage below.
                $redir = $root_dir.'detail?topic='.$topic_id.'&'.$resp;
            }
            // delete post data cookie, we're done with it
            setcookie('postData', NULL, '1');
            // get current page to redirect to after successful post
            if (empty($redir)) { $redir = $satisfying->getCurPage($resp); }
            $satisfying->debug('rediretcing to: '.$redir);
            header('Location: '. $redir);
        } 
        
    } else {
        // authenticate
        $satisfying->getAuth();
    }  
// catch response from gs
} else if (isset($_GET['oauth_token'])) {
              
    if (isset($_COOKIE['request_token'])) {
        // get request token from cookie
        $satisfying->setToken($_COOKIE['request_token']);
    }
    if (isset($_COOKIE['request_token_secret'])) {
        // get request token secret from cookie
        $satisfying->setTokenSecret($_COOKIE['request_token_secret']);
    }  
    
    // if the oauth_token and our request_token match then we exchange for access tokens.
    if ((string)$_GET['oauth_token'] == (string)$satisfying->getToken()) {
    
        // exchange for access tokens
        $satisfying->getAccessToken();
    
        // set cookies and destroy old request cookies
        setcookie('token', $satisfying->getToken(), time()+60*60*24*30, '/');
        setcookie('token_secret', $satisfying->getTokenSecret(), time()+60*60*24*30, '/');
        setcookie('request_token', NULL, '1');
        setcookie('request_token_secret', NULL, '1');
              
        if (isset($_COOKIE['postData'])) {
            //get post data from cookie
            $postData = json_decode(stripslashes($_COOKIE['postData']), true);
            // check for validation errors
            if ($satisfying->validatePost($postData)) {
               
                // pass POST vars to object
                $satisfying->setRequestBody($postData);
                // send request
                $response = $satisfying->sendRequest();
                // get response code
                $code = $response->getStatus();

                $resp = 'resp='.$code;
                // 201 is success. Anything else means it failed
                if ($code == 201) {
                    // get body of response
                    $body = json_decode($response->getBody());
                    // if this was a topic post we should redir to topic page
                    if (!empty($body->subject)) {
                        $topic_id = $body->id;
                        // build url here and use it instead of getCurPage below.
                        $redir = $root_dir.'detail?topic='.$topic_id.'&'.$resp;
                    }
                    // delete post data cookie, we're done with it
                    setcookie('postData', NULL, '1');
                } 
                
            } else {
                // process errors, validation failed
                $errors = $satisfying->errors;// 
                if(array_key_exists('reply',$postData)) {
                    $errors_html_reply = '<p>'.implode('<br />',$errors).'</p>';
                } else {
                    $errors_html = '<p>'.implode('<br />',$errors).'</p>';
                }
                $errors = implode(PHP_EOL,$errors);
                $satisfying->setAlert($errors);
            }
        }
        if ($code == 201 || $code = 0) {
            // return to page
            if (empty($redir)) { $redir = $satisfying->getCurPage($resp); }
            $satisfying->debug('rediretcing to: '.$redir);
            header('Location: '. $redir);            
        } 
    }
} 
//set local var $user_id if cookie is set
if (isset($_COOKIE['user_id'])) { 
   $user_id = (int)$_COOKIE['user_id'];
} else {
    $user_id = '';
}

//get products list 
$products_feed = $satisfying->getProducts();
if (!$products_feed) { header('HTTP/1.0 404 Not Found'); $satisfying->error($_SERVER['SCRIPT_FILENAME'].': getProducts() failed.'); die(); }
?>