<?php

require_once 'HTTP/OAuth/Consumer.php';
require_once 'lib/classes/formvalidator.class.php';

/**
 * Satisfying Class Extends HTTP_OAuth_Consumer. Interacts with GetSatisfaction's
 * api as a client. Users can view feeds and if authenticated via OAuth, may post
 * comments, replies, and topics as well as star topics and MeToo topics. 
 * Requires HTTP_Request2 and HTTP_OAuth Pear packages
 * 
 * @author Jeremy Williams (jpwdesigns@gmail.com)
 * @package satisfyingclass
 * @version 2.0 
 */
class Satisfying extends HTTP_OAuth_Consumer {
    /**
     * Required for queries requesting community data feeds. 
     * Example: 'http://api.getsatisfaction.com'
     * Passed in when instantiating new object. 
     * 
     * @access public
     * @var string 
     */
    public $feed_root = '';

    /**
     * Required company name.
     * Example: 'quickoffice'
     * Passed in when instantiating new object.
     * 
     * @access public
     * @var string 
     */
    public $company = '';

    /**
     * Required company id provided by Get Satisfaction.
     * 
     * @access public
     * @var int 
     */
    public $company_id;

    /**
     * Required root for oAuth calls. 
     * Example: 'http://community.quickoffice.com/'
     * Passed in when instantiating new object.
     *
     * @access public
     * @var string 
     */
    public $auth_root = '';

    /**
     * Endpoint for Posts / Puts. Passed in as a parameter with the key of "url".
     * Example: http://api.getsatisfaction.com/topics/some_topic_slug(or ID)/replies.json
     * Default is set to api end point for new topics during instantiation.
     * 
     * @access public
     * @var string 
     */
    public $rest_endpoint = '';

    /**
     * Required to send posts to getsatisfaction api.
     * Example: '{"reply": { "content" :"Wow really great post!"}} ';
     *  
     * @access public
     * @var string  
     */
    public $requestBody = '';
    
    /**
     * Key provided by Get Satisfaction for OAuth.
     * 
     * @access protected
     * @var string 
     */
    protected $key;

    /**
     * Secret provided by Get Satisfaction for OAuth.
     * 
     * @access protected
     * @var string 
     */    
    protected $secret;
    
    /**
     * Last response received from api call.
     * 
     * @access public
     * @var string
     */
    public $lastResponse;
    
    /**
     * Last request sent in api call.
     * 
     * @access public
     * @var string
     */
    public $lastRequest;
    
    /**
     * Debug mode. Should only be true in development. 
     * Never set to true in production environment. 
     * 
     * @access public
     * @var boolean
     */
    public $debug = false;
    
    /**
     * Message sent to browser on error.
     * 
     * @access public
     * @var string
     */
    public $alert = '';
    
    /**
     * Errors received from api responses or input validation.
     * 
     * @access public
     * @var array
     */
    public $errors = array();
    
    /**
     * Id of page to show an alert message on.
     * 
     * @access public
     * @var string
     */
    public $alertPageId;
    
    /**
     * Url parameters used to build api calls
     * 
     * @access public
     * @var array
     */
    public $params = array();
    
    /**
     * Path to cache directory. For example '/var/www/cache/'
     * 
     * @access public
     * @var string
     */
    public $cache = ''; 
    
    /**
     * Parameters allowed to be passed in to addParams method. 
     * See http://www.php.net/manual/en/function.filter-input-array.php
     * 
     * @access private
     * @var array 
     */
    private $allowedParams = array(
        'q' => FILTER_SANITIZE_STRING,
        'query' => FILTER_SANITIZE_STRING,
        'sort' => FILTER_SANITIZE_STRING,
        'style' => FILTER_SANITIZE_STRING,
        'user' => FILTER_SANITIZE_STRING,
        'active_since' => FILTER_SANITIZE_STRING,
        'people' => FILTER_SANITIZE_STRING,
        'product' => FILTER_SANITIZE_STRING,
        'tag' => FILTER_SANITIZE_STRING,
        'status' => FILTER_SANITIZE_STRING,
        'user_defined_code' => FILTER_SANITIZE_STRING,
        'filter' => FILTER_SANITIZE_STRING,
        'type' => FILTER_SANITIZE_STRING,
        'include_comments' => FILTER_SANITIZE_STRING,
        'page' => FILTER_VALIDATE_INT,
        'topic' => FILTER_VALIDATE_INT,
        'reply' => FILTER_VALIDATE_INT,
        'limit' => FILTER_VALIDATE_INT
    );


    /**
     * Constructs Satisfying class object.
     * 
     * @param string $key
     * @param string $secret
     * @param string $auth_root
     * @param string $feed_root
     * @param string $company
     * @param int $company_id
     * @param string $token
     * @param string $tokenSecret 
     */
    public function __construct($key, $secret, $auth_root, $feed_root, $company, $company_id, $token = null, $tokenSecret = null) {
        $this->key = $key;
        $this->secret = $secret;
        $this->auth_root = $auth_root;
        $this->feed_root = $feed_root;
        $this->company = $company;
        $this->company_id = $company_id;
        $this->setToken($token);
        $this->setTokenSecret($tokenSecret);
        $this->rest_endpoint = $this->feed_root . '/companies/' . $this->company . '/topics.json';
    }

    
    /**
     * Empties tokens and token cookies. 
     */
    function clearAuth() {
        setcookie('token', NULL, '1');
        setcookie('token_secret', NULL, '1');
        setcookie('request_token', NULL, '1');
        setcookie('request_token_secret', NULL, '1');
        $this->setToken(null);
        $this->setTokenSecret(null);
    }

    
    /**
     * Starts OAuth authentication process. This could be changed if you were
     * saving tokens in a db or data store other than cookies.
     * 
     * @access public
     * @param boolean $return Set to true in order to not redirect but pass url back as string.
     * @return string
     */
    public function getAuth($return=false) {

        // Requests a token.
        try {
            $this->getRequestToken();
        } catch (HTTP_OAuth_Consumer_Exception_InvalidResponse $e) {
            $this->error('getAuth() getRequestToken() Error: ' . $e->getMessage());
        }

        // Saves cookies to use for "request access token".
        setcookie('request_token', $this->getToken(), time() + 60 * 60 * 24 * 30, '/');
        setcookie('request_token_secret', $this->getTokenSecret(), time() + 60 * 60 * 24 * 30, '/');

        // Gets OAuth auth url.
        $goToUrl = $this->getAuthorizeUrl();

        if ($return) {
            return $goToUrl;
        } else {
            header('Location: ' . $goToUrl);
            exit;
        }
    }

    
    /**
     * Sets user_id cookie if it is not set already. 
     */
    public function getUserId() {
        if ((!isset($_COOKIE['user_id'])) && ($this->token !== null) && ($this->tokenSecret !== null)) {
            // cookie wasn't there. get it from /me handle
            $person = $this->sendRequest('http://api.getsatisfaction.com/me.json', array(), 'GET');
            $code = $person->getStatus();
            if ($code == 200) {
                $personDetails = json_decode($person->getBody());
                $this->rest_endpoint = $this->feed_root . '/people/' . (int) $personDetails->id . '/followed/topics.json';
                // set user_id cookie 
                setcookie('user_id', $personDetails->id, time() + 60 * 60 * 24 * 30, '/');
            }
        }
    }


    /**
     * Adds a star to a reply. 
     * Url endpoint used is this format: 
     * /topics/topic_id_or_slug/replies/reply_id/stars.json
     * 
     * @return string 
     */
    public function star() {
        $this->addParams();
        $this->getUserId();
        $this->requestBody = '';
        $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '/replies/' . $this->params['reply'] . '/stars.json';
        $this->sendRequest($this->rest_endpoint, array(), 'POST');
        return $this->lastResponse;
    }

    
    /**
     * Removes a star from a reply
     * Url endpoint used is in this format:
     * /topics/topic_id_or_slug/replies/reply_id/stars/remove.json
     * 
     * @return type 
     */
    public function unstar() {
        $this->addParams();
        $this->getUserId();
        $this->requestBody = '';
        $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '/replies/' . $this->params['reply'] . '/stars/remove.json';
        $this->sendRequest($this->rest_endpoint, array(), 'DELETE');
        return $this->lastResponse;
    }

    
    /**
     * Adds topic to user's followed topics.
     * 
     * @return string 
     */
    public function follow() {
        $this->addParams();
        $this->getUserId();
        if ((isset($_COOKIE['user_id'])) && (!(empty($_COOKIE['user_id'])))) {
            $user_id = (int)$_COOKIE['user_id'];
        } else {
            $user_id = '0';
        }
        $this->rest_endpoint = $this->feed_root . '/people/' . $user_id . '/followed/topics.json';
        $this->requestBody = '{"topic_id": ' . (int) $this->params['topic'] . '}';
        $this->sendRequest($this->rest_endpoint, array(), 'POST');
        return $this->lastResponse;
    }

    
    /**
     * Removes a topic from a user's followed topics.
     * 
     * @return string 
     */
    public function unFollow() {
        $this->addParams();
        $this->getUserId();
        if ((isset($_COOKIE['user_id'])) && (!(empty($_COOKIE['user_id'])))) {
            $user_id = (int)$_COOKIE['user_id'];
        } else {
            $user_id = '0';
        }
        $this->rest_endpoint = $this->feed_root . '/people/' . $user_id . '/followed/topics/' . (int) $this->params['topic'] . '.json';
        $this->requestBody = '{"topic_id": ' . (int) $this->params['topic'] . '}';
        $this->sendRequest($this->rest_endpoint, array(), 'DELETE');
        return $this->lastResponse;
    }

    
    /**
     * MeToo's a topic for user.
     * 
     * @return string 
     */
    public function meToo() {
        $this->addParams();
        $this->getUserId();
        $this->requestBody = '';
        $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '/me_toos.json';
        $this->sendRequest($this->rest_endpoint, array(), 'POST');
        return $this->lastResponse;
    }

    /*
     * function to get followed topics. Requires prior authentication
     * call requireAuth() before getFollowd().
     * returns object
     */
    
    /**
     * Gets followed topics for user. User must be authenticated. 
     * Use requireAuth() before getFollowd().
     * 
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getFollowd() {
        $this->rest_endpoint = '';

        if (isset($_COOKIE['user_id'])) {
        
            // Gets myTopics from people uri since user_id is set.
        $user_id = (int) $_COOKIE['user_id'];
        $this->rest_endpoint = $this->feed_root . '/people/' . $user_id . '/followed/topics.json?company=' . $this->company_id;
        
        } else if (($this->token !== null) && ($this->tokenSecret !== null)) {
            
            // Gets myTopics from /me if user_id is not set.
            $person = $this->sendRequest('http://api.getsatisfaction.com/me.json', array(), 'GET');
            
            // Gets response code.
            $code = $person->getStatus();
            
            // Redirects to login if not logged in.
            if ((int) $code == 401) {
                header('Location: ' . $this->getCurPage('login=true'));
            }
            
            $personDetails = json_decode($person->getBody());
            $this->rest_endpoint = $personDetails->url . '/followed/topics.json?company=' . $this->company_id;
            
            // Sets user_id cookie for use next time.
            if (!isset($_COOKIE['user_id'])) {
                setcookie('user_id', $personDetails->id, time() + 60 * 60 * 24 * 30, '/');
            }
            
        } else {
            // Redirects to login if no cookie or tokens are set.
            header('Location: ' . $this->getCurPage('login=true'));
        }

        if ($this->rest_endpoint !== '') {

            // Gets myTopics feed.
            $followd = $this->getFeed();

            if ($followd) {
                $data = json_decode($followd);
                return $data->data;
            } else {
                $this->debug('getFollowd: bad reply');
                return false;
            }
            
        } else {
            
            $this->debug('getFollowd: missing user_id.');
            return false;
            
        }
    }

    
    /**
     * Gets comments for reply.
     * 
     * @param int $id
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getComments($id='') {

        // Checks for topic id.
        if ((!empty($id)) && (!empty($this->params['topic']))) {
            // Sets up the url endpoint.
            $this->rest_endpoint = $this->feed_root . '/replies/' . $id . '/comments.json';

            // Gets feed.
            $comments = $this->getFeed(false);

            if ($comments) {
                $data = json_decode($comments);
                return $data;
            } else {
                $this->debug('getComments: bad reply');
                return false;
            }
        } else {
            $this->debug('getComments: missing id.');
            return false;
        }
    }

    
    /**
     * Gets replies for a topic.
     * 
     * @param boolean $noComment To get or not to get comments with replies.
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getReplies($noComment=true) {

        if ($noComment) {
            $this->addParams(array('include_comments' => 'false'));
        }

        $query = $this->getUrlQuery();

        if (!empty($this->params['topic'])) {
            $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '/replies.json' . $query;
        }

        $replies = $this->getFeed(true);

        if ($replies) {
            $this->debug('getReplies: ' . var_export($replies, true));
            $data = json_decode($replies);
            return $data;
        } else {
            $this->debug('getReplies: bad reply');
            return false;
        }
    }

    /*
     * function to get Official replies
     */
    public function getOfficialReplies() {


        // check for product
        if (!empty($this->params['topic'])) {
            // set up the url endpoint
            $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '/replies.json?filter=company_promoted';
        }
        // get feed
        $replies = $this->getFeed(true);

        if ($replies) {
            $this->debug('getOfficialReplies: ' . var_export($replies, true));
            $data = json_decode($replies);
            return $data;
        } else {
            $this->debug('getOfficialReplies: bad reply');
            return false;
        }
    }

    /**
     * Gets topic from GetSatisfaction.
     * 
     * @param boolean $auth
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getTopic($auth=true) {

        $query = $this->getUrlQuery();

        if (!empty($this->params['topic'])) {
            $this->rest_endpoint = $this->feed_root . '/topics/' . $this->params['topic'] . '.json' . $query;
        }

        $topic = $this->getFeed($auth);

        if ($topic) {
            $data = json_decode($topic);
            return $data;
        } else {
            $this->debug('getTopic: bad reply');
            return false;
        }
    }

    
    /**
     * Gets latest update message.
     * 
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getUpdateMessage() {

        $url = $this->feed_root . '/companies/' . $this->company . '/topics.json?style=update&limit=1';
        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET, array('ssl_verify_host' => false,'ssl_verify_peer' => FALSE));

        try {
            $this->debug('getUpdateMessage: trying to request ' . $url);
            $response = $request->send();
            if (200 == $response->getStatus()) {
                return json_decode($response->getBody());
            } else {
                $this->debug('getUpdateMessage: Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $this->error('getUpdateMessage Error: ' . $e->getMessage());
            return false;
        }
    }

    
    /**
     * Gets topics list for people or companies depending on whether or not
     * people property is empty.
     * 
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getTopics() {

        $query = $this->getUrlQuery();

        // Checks for people param and requests topics for people otherwise companies.
        if (!empty($this->params['people'])) {
            $this->rest_endpoint = $this->feed_root . '/people/' . $this->params['people'] . '/topics.json' . $query;
        } else {
            $this->rest_endpoint = $this->feed_root . '/companies/' . $this->company . '/topics.json' . $query;
        }

        $topics = $this->getFeed();

        if ($topics) {
            $data = json_decode($topics);
            return $data;
        } else {
            $this->debug('getTopics: bad reply');
            return false;
        }
    }

    
    /**
     * Gets product. Makes use of cache, however the cached products
     * list must be generated manually saved as a json file from output of 
     * products api response. This speeds up page load considerably since this
     * method is called nearly every pageload. Note that if your products change,
     * you must update the cached file manually or else new products won't show up.
     * 
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getProduct() {

        $addParams = $this->addParams(INPUT_COOKIE);
        $addParams = $this->addParams();

        if (!empty($this->params['product'])) {

            // Checks for cached products page and get product info from cache if exists.
            $products = $this->isCached($this->feed_root . '/companies/' . $this->company . '/products.json');

            if ($products) {
                
                $products_obj = json_decode($products);
                
                foreach ($products_obj->data as $key => $array) {
                    if ($array->id == $this->params['product']) {
                        $product_key = $key;
                    }
                }
                
                $product = json_encode($products_obj->data[$product_key]);

            } else {
                
                $this->rest_endpoint = $this->feed_root . '/products/' . $this->params['product'] . '.json';
                $product = $this->getFeed();
            
            }

            if ($product) {
                $data = json_decode($product);
                return $data;
            } else {
                $this->debug('getProduct: bad reply');
                return false;
            }
        } else {
            $this->debug('getProduct: no product parameter set:' . var_export($this->params, true));
            return false;
        }
    }

    
    /**
     * Gets products list. Makes use of cache, however the cached products
     * list must be generated manually saved as a json file from output of 
     * products api response. This speeds up page load considerably since this
     * method is called nearly every pageload. Note that if your products change,
     * you must update the cached file manually or else new products won't show up.
     * 
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getProducts() {

        $this->rest_endpoint = $this->feed_root . '/companies/' . $this->company . '/products.json';

        $products = $this->isCached($this->rest_endpoint);

        if (false === $products) {
            $products = $this->getFeed();
        }

        if ($products) {
            $data = json_decode($products);
            return $data->data;
        } else {
            $this->debug('getProducts: bad reply');
            return false;
        }
    }

    
    /**
     * Gets product slug.
     * 
     * @param int $id
     * @return boolean|object False if unsuccessful, object otherwise.
     */
    public function getProductSlug($id) {
        $productSlugs = $this->isCached('gs_product_map.json');
        if ($productSlugs) {
            $data = json_decode($productSlugs, true);
            $slug = array_search($id, $data);
            if ($slug) {
                return $slug;
            } else {
                $this->error('getProductSlug(' . $id . ') Slug not found in cache file');
            }
        } else {
            $this->error('getProductSlug(' . $id . ') Cache File not found');
            return false;
        }
    }

    
    /**
     * Executes api call to retrieve feed from Get Satisfaction.
     * 
     * @param boolean $useAuth
     * @return boolean|string False if unsuccessful, json string otherwise.
     */
    public function getFeed($useAuth=false) {
        $url = filter_var($this->rest_endpoint, FILTER_SANITIZE_URL);
        $this->debug($url);
        // Sends request with authentication tokens if useAuth is true and 
        // neccessary tokens and user_id exist.
        if (((isset($_COOKIE['user_id'])) && ($this->token !== null) && ($this->tokenSecret !== null)) && ($useAuth)) {

            try {
                $this->debug('getFeed: trying to request ' . $url);
                $response = $this->sendRequest($url, array(), 'GET');
                if (200 == $response->getStatus()) {
                    return $response->getBody();
                } else {
                    $this->debug('Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
                    return false;
                }
            } catch (HTTP_Request2_Exception $e) {
                $this->error('getFeed() sendRequest Error: ' . $e->getMessage());
                return false;
            }
        
        // Sends request without authentication.
        } else {

            $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET, array('ssl_verify_host' => false,'ssl_verify_peer' => FALSE));
            try {
                $this->debug('getFeed: trying to request ' . $url);
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    return $response->getBody();
                } else {
                    $this->debug('Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
                    return false;
                }
            } catch (HTTP_Request2_Exception $e) {
                $this->error('getFeed() HTTP_Request2 Error: ' . $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Adds allowed parameters to url endpoint for api call.
     * 
     * @param mixed $type Parameters to be added to params. Default uses INPUT_GET. 
     * Accepts INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, INPUT_ENV or array.
     * 
     * @return boolean 
     */
    public function addParams($type=INPUT_GET) {
        $goodParams = array();
        $type_array = array(INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, INPUT_ENV);

        if (is_array($type)) {

            foreach ($type as $key => $val) {
                if (array_key_exists($key, $this->allowedParams)) {
                    $filters = $this->allowedParams;
                    $val = filter_var($val, $filters[$key]);
                    $goodParams[$key] = $val;
                    $this->debug('addParams added: ' . var_export($goodParams, true));
                }
            }

            $this->params = array_merge($this->params, $goodParams);
        
        } else if (in_array($type, $type_array)) {

            $params = filter_input_array($type, FILTER_SANITIZE_STRING);

            if (is_array($params)) {

                foreach ($params as $key => $val) {
                    if (array_key_exists($key, $this->allowedParams)) {
                        $goodParams[$key] = $val;
                        $this->debug('addParams added: ' . var_export($goodParams, true));
                    }
                }

                if (count($goodParams) > 0) {
                    $this->params = array_merge($this->params, $goodParams);
                    return true;
                } else {
                    $this->debug('addParams: no parameters added.');
                    return false;
                }
            } else {
                $this->debug('addParams: no parameters added.');
                return false;
            }
        } else {
            $this->debug('addParams: failed because type supplied is wrong.');
            return false;
        }
    }

    
    /**
     * Gets query portion of api endpoint.
     * 
     * @return string 
     */
    public function getUrlQuery() {

        $this->addParams();
        $params = $this->params;

        // Changes product key to products for query.
        if (isset($params['product']) && $params['product'] !== "") {
            $slug = $this->getProductSlug($params['product']);
            if ($slug) {
                $params['product'] = $slug;
            }
        }

        // Removes vars from params which are used to build request endpoint path.
        unset($params['people']);
        unset($params['topic']);

        if (is_array($params) && (!empty($params))) {

            $queryString = http_build_query($params);
            $queryString = '' ? '' : '?' . $queryString;
            return $queryString;
            $this->debug('getUrlQuery:' . $queryString);
        } else {

            $this->debug('getUrlQuery: no query present.' . var_export($this->params, true));
            return '';
        }
    }


    /**
     * Gets current page url for callbacks and form actions.
     * 
     * @param string|array $extra Extra parameters to add to end of URL returned.
     * For example: 'somekey=someval&someotherkey=someotherval'
     * or it can be array:
     * example: array(  'somekey'=>'someval',
     *                  'someotherkey'=>'someotherval');
     * 
     * @return string 
     */
    public function getCurPage($extra='') {
        // Don't pass these parameters.
        $dontPass = array('action', 'oauth_token', 'resp', 'q', 'query', 'login', 'reply','app');
        $filteredParamsString = '';
        $filteredParams = array();
        $keyval = array();
        $reqURI = '';
        $pageURL = 'http';
        if (true === HTTPS_ON) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= WEBSITE_DOMAIN . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= WEBSITE_DOMAIN;
        }
        
        // Filters dontPass params out of request_uri.
        if (!empty($_SERVER["REQUEST_URI"])) {
            $reqURI = parse_url($_SERVER["REQUEST_URI"]);
            if (!empty($reqURI['query'])) {
                $reqURIArr = explode('&', $reqURI['query']);
                foreach ($reqURIArr as $params) {
                    if (!empty($params)) {
                        $keyval = explode('=', $params);
                        if (!in_array($keyval[0], $dontPass)) {
                            $filteredParams[] = $keyval[0] . '=' . $keyval[1];
                        }
                    }
                }

                $filteredParamsString = implode('&', $filteredParams);
            }

            $pageURL .= rtrim($reqURI['path'] . '?' . $filteredParamsString, '&?');
        }
        if (!empty($extra)) {
            if (is_array($extra)) {
                $string = '';
                foreach ($extra as $key => $val) {
                    $string .= $key . '=' . $val;
                    $string .= '&';
                }
                $extra = rtrim($string, '&');
            }
            if (stripos($pageURL, '?')) {
                $pageURL .= '&' . $extra;
            } else {
                $pageURL .= '?' . $extra;
            }
        }
        // sanitize url
        $pageURL = str_replace(array("'",'"',"(",")","{","}","|","\\","[","]","`","<",">",";","%"),'',$pageURL);
        return (string) filter_var($pageURL, FILTER_SANITIZE_URL);
    }


    /**
     * Gets next page url for pagination.
     * 
     * @return string 
     */
    public function getNextPage() {
        $p = (int)((isset($this->params['page']) && $this->params['page'] >1) ? $this->params['page'] : 1);
        $np = $p+1;
        // Don't pass these parameters
        $dontPass = array('action', 'oauth_token', 'resp', 'q', 'query', 'login', 'reply', 'page');
        $filteredParamsString = '';
        $filteredParams = array();
        $keyval = array();
        $reqURI = '';
        $pageURL = 'http';
        if (true === HTTPS_ON) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= WEBSITE_DOMAIN . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= WEBSITE_DOMAIN;
        }
        
        // Filters dontPass params out of request_uri.
        if (!empty($_SERVER["REQUEST_URI"])) {
            $reqURI = parse_url($_SERVER["REQUEST_URI"]);
            if (!empty($reqURI['query'])) {
                $reqURIArr = explode('&', $reqURI['query']);
                foreach ($reqURIArr as $params) {
                    if (!empty($params)) {
                        $keyval = explode('=', $params);
                        if (!in_array($keyval[0], $dontPass)) {
                            $filteredParams[] = $keyval[0] . '=' . $keyval[1];
                        }
                    }
                }

                $filteredParamsString = implode('&', $filteredParams);
            }

            $pageURL .= rtrim($reqURI['path'] . '?' . $filteredParamsString, '&?');
        }
        
        if (stripos($pageURL, '?')) {
            $pageURL .= '&page=' . $np;
        } else {
            $pageURL .= '?page=' . $np;
        }
        // sanitize url
        return (string) filter_var($pageURL, FILTER_SANITIZE_URL);
    }

    /**
     * Gets prev page url for pagination
     * 
     * @return string 
     */
    public function getPrevPage() {
        $p = (int)((isset($this->params['page']) && $this->params['page'] >1) ? $this->params['page'] : 1);
        $np = ( ( ($p-1)>1 ) ? ($p-1) : false );
        // don't pass these parameters
        $dontPass = array('action', 'oauth_token', 'resp', 'q', 'query', 'login', 'reply', 'page');
        $filteredParamsString = '';
        $filteredParams = array();
        $keyval = array();
        $reqURI = '';
        $pageURL = 'http';
        if (true === HTTPS_ON) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= WEBSITE_DOMAIN . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= WEBSITE_DOMAIN;
        }
        //filters dontPass params out of request_uri
        if (!empty($_SERVER["REQUEST_URI"])) {
            $reqURI = parse_url($_SERVER["REQUEST_URI"]);
            if (!empty($reqURI['query'])) {
                $reqURIArr = explode('&', $reqURI['query']);
                foreach ($reqURIArr as $params) {
                    if (!empty($params)) {
                        $keyval = explode('=', $params);
                        if (!in_array($keyval[0], $dontPass)) {
                            $filteredParams[] = $keyval[0] . '=' . $keyval[1];
                        }
                    }
                }

                $filteredParamsString = implode('&', $filteredParams);
            }

            $pageURL .= rtrim($reqURI['path'] . '?' . $filteredParamsString, '&?');
        }
        if ($np) {
            if (stripos($pageURL, '?')) {
            $pageURL .= '&page=' . $np;
            } else {
                $pageURL .= '?page=' . $np;
            }
        }
        
        // sanitize url
        return (string) filter_var($pageURL, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitizes url passed url strings.
     * 
     * @return string 
     */
    public function cleanUrl($url) {
        $matches = array();
        // Checks for matches and checks if match page exists.
        $host = 'http://'.WEBSITE_DOMAIN;
        preg_match('/^(https?):\/\/'.                                          // protocol
                    '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
                    '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
                    '@)?(?#'.                                                  // auth requires @
                    ')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
                    '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
                    '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
                    '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
                    ')(:\d+)?'.                                                // port
                    ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
                    '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
                    '?)?)?'.                                                   // path and query string optional
                    '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
                    '$/i', $url, $matches);
        if (is_array($matches) && (!empty($matches[0]))) {
            if (substr($matches[0], 0, strlen($host)) == $host) {
                return $matches[0];
            } else {
                return $host;
            }
        } else {
            return $host;
        }
    }

    /**
     * Sanitizes passed Uri.
     * 
     * @return string 
     */
    public function cleanUri($uri) {
        preg_match('|/[a-zA-Z0-9]+\?[a-zA-Z0-9]+=[a-zA-Z0-9]+(&[a-zA-Z0-9]+=[a-zA-Z0-9]+)?|i', $uri, $matches);
        if (is_array($matches) && (!empty($matches[0]))) {
            
                return $matches[0];
           
        } else {
            return '/';
        }
    }
    
    /**
     * Gets request token modified to set url automatically and remove callback since it's not needed yet.
     * for GS's api
     *
     * @param string $url        Request token url
     * @param array  $additional Additional parameters to be in the request
     *                           recommended in the spec.
     * @param string $method     HTTP method to use for the request
     *
     * @return void
     * @throws HTTP_OAuth_Consumer_Exception_InvalidResponse Missing token/secret
     */
    public function getRequestToken($url='satisfaction', $callback='oob', array $additional = array(), $method = 'POST') {
        if ($url == 'satisfaction') {
            $url = $this->auth_root . 'api/request_token';
        }
        $this->debug('getRequestToken()  Getting request token from ' . $url);
        $response = $this->sendRequest($url, $additional, $method);
        $this->debug('getRequestToken() ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
        // should do some better parsing of body to get reason for failure. the exception below sucks. tells nothing.
        $data = $response->getDataFromBody();
        if (empty($data['oauth_token']) || empty($data['oauth_token_secret'])) {
            throw new HTTP_OAuth_Consumer_Exception_InvalidResponse(
                    'getRequestToken() Failed getting request token and token secret from response', $response
            );
        }
        $this->setToken($data['oauth_token']);
        $this->setTokenSecret($data['oauth_token_secret']);
    }

    /**
     * Gets authorize url modified to get url and callback automatically.
     * The callback needs to be set manually if the callback url is different 
     * from the page where the request started.
     *
     * @param string $url        Authorize url
     * @param string $callback   Callback url
     * @param array  $additional Additional parameters for the auth url
     *
     * @return string Authorize url
     */
    public function getAuthorizeUrl($url='satisfaction', array $additional = array(), $callback='satisfaction') {
        if ($url == 'satisfaction') {
            $url = $this->auth_root . 'api/authorize';
        }

        if ($callback == 'satisfaction') {
            $callback = $this->getCurPage();
        }
        $additional['oauth_callback'] = $callback;

        $this->debug('getAuthorizeUrl ()callback: ' . $callback);
        $params = array('oauth_token' => $this->getToken());
        $params = array_merge($additional, $params);

        return sprintf('%s?%s', $url, HTTP_OAuth::buildHTTPQuery($params));
    }

    /**
     * Gets access token set url automatically.
     *
     * @param string $url        Access token url
     * @param string $verifier   OAuth verifier from the provider
     * @param array  $additional Additional parameters to be in the request
     *                           recommended in the spec.
     * @param string $method     HTTP method to use for the request
     *
     * @return array Token and token secret
     * @throws HTTP_OAuth_Consumer_Exception_InvalidResponse Mising token/secret
     */
    public function getAccessToken($url='satisfaction', $verifier = '', array $additional = array(), $method = 'POST'
    ) {

        if ($url == 'satisfaction') {
            $url = $this->auth_root . 'api/access_token';
        }
        if ($this->getToken() === null || $this->getTokenSecret() === null) {
            throw new HTTP_OAuth_Exception('No token or token_secret');
        }

        $this->debug('getAccessToken() Getting access token from ' . $url);
        if ($verifier !== null) {
            $additional['oauth_verifier'] = $verifier;
        }

        $this->debug('getAccessToken() verifier: ' . $verifier);
        $response = $this->sendRequest($url, $additional, $method);
        $data = $response->getDataFromBody();
        if (empty($data['oauth_token']) || empty($data['oauth_token_secret'])) {
            throw new HTTP_OAuth_Consumer_Exception_InvalidResponse(
                    'getAccessToken() Failed getting access token and token secret from response', $response
            );
        }

        $this->setToken($data['oauth_token']);
        $this->setTokenSecret($data['oauth_token_secret']);
    }

    /**
     * Send request. Modified for GS api which requries json payload and json content-type.
     * before calling sendRequest(); do setRequestBody
     *
     * @param string $url        URL of the protected resource
     * @param array  $additional Additional parameters
     * @param string $method     HTTP method to use
     *
     * @return HTTP_OAuth_Consumer_Response Instance of a response class
     */
    public function sendRequest($url='satisfaction', array $additional = array(), $method = 'POST') {
        if ($url == 'satisfaction') {
            $url = $this->rest_endpoint;
        } else {
            $url = urldecode($url);
            $url = filter_var($url, FILTER_SANITIZE_URL);
            if ((strpos($url, $this->feed_root) === false) && (strpos($url, $this->auth_root) === false)) {
                $this->error('sendRequest() Url submitted is unacceptable');
                return false;
            }
        }
        $this->debug('sendRequest() URL to be used for sendRequest: ' . $url);

        if ($this->requestBody !== '') {
            $request_body = (string) $this->requestBody;
        }
        $params = array(
            'oauth_consumer_key' => $this->key,
            'oauth_signature_method' => $this->getSignatureMethod()
        );

        if ($this->getToken()) {
            $params['oauth_token'] = $this->getToken();
        }

        $params = array_merge($additional, $params);
        $this->debug($params);

        $req = clone $this->getOAuthConsumerRequest();
        $req->setConfig(array(
            'ssl_verify_peer'   => FALSE,
            'ssl_verify_host'   => FALSE
        ));
        $req->setUrl($url);
        $req->setMethod($method);
        $req->setSecrets($this->getSecrets());
        $req->setParameters($params);
        if (isset($request_body)) {
            $req->setHeader('Content-Type', 'application/json');
            $req->setBody($request_body);
        }

        $this->lastResponse = $req->send();
        $this->debug('sendRequest() ' . $this->lastResponse->getStatus() . ' ' . $this->lastResponse->getReasonPhrase());
        $this->lastRequest = $req;
        return $this->lastResponse;
    }

    /**
     * Validates posts. This is called directly before calling setRequestBody. 
     * Used to validate form fields.
     * 
     * @return boolean|object True if successful, object of errors otherwise.
     */
    public function validatePost($data = array()) {
        if (count($data) == 0) {
            $data = $_POST;
        }
        $validator = new FormValidator();

        switch ($data) {
            case (array_key_exists('comment', $data)):
                $validator->addValidation("comment[content]", "req", "Your comment can not be blank.");
                break;
            case (array_key_exists('reply', $data)):
                $validator->addValidation("reply[content]", "req", "Your reply can not be blank.");
                break;
            case (array_key_exists('topic', $data)):
                $validator->addValidation("topic[subject]", "req", "Please add a title.");
                $validator->addValidation("topic[additional_detail]", "req", "Please add some details.");
                break;
        }

        if ($validator->ValidateForm($data)) {
            return true;
        } else {
            $this->errors = $validator->GetErrors();
        }
    }

    /**
     * Sets Request Body. This is called directly before calling sendRequest. 
     * This is also critical in setting up the endpoint for posts/puts
     * 
     * @param array $data
     */
    public function setRequestBody($data) {

        //This should only be empty if you are passing in url to sendRequest();
        if (isset($data['url']) && $data['url'] !== '') {
            $this->setEndpointUrl($data);
        } else {
            $this->debug('setRequestBody() URL was not passed to setEndpointUrl');
        }

        $data = $this->filterRequestBodyData($data);

        $this->debug('setRequestBody() FilterRequestBodyData to follow:');
        $this->debug($data);

        $payload = json_encode($data);

        $this->debug('setRequestBody() Payload: ' . $payload);

        $this->requestBody = $payload;
    }

    /**
     * Processes Data Array for request setBody. 
     * Set form field names to appropriate keys below.
     * 
     * @param array $data typically set by passing $_POST data. 
     * 
     * @return array $data_clean
     */
    private function filterRequestBodyData($data) {

        $data = $this->cleanData($data);
        $data_clean = array();

        // The following arrays are the possible fields accepted by
        // get satsifaction api. Value set to required if it is a required 
        // field.

        $topic = array(
            'subject' => 'required',
            'style' => '',
            'keywords' => '',
            'additional_detail' => 'required',
            'products' => '',
            'emotitag' => ''
        );

        $reply = array(
            'content' => 'required',
            'emotitag' => ''
        );

        $comment = array(
            'content' => 'required',
            'parent_id' => ''
        );

        $emotitag = array(
            'face' => '',
            'feeling' => '',
            'intensity' => ''
        );

        // Process data as a comment
        if (array_key_exists('comment', $data)) {

            // Checks for required fields in $data
            $this->isRequired($data, 'comment', $comment);

            $data_clean['comment'] = array_intersect_key($data['comment'], $comment);

        // Process data as a reply
        } else if (array_key_exists('reply', $data)) {

            // Checks for required fields in $data
            $this->isRequired($data, 'reply', $reply);

            $data_clean['reply'] = array_intersect_key($data['reply'], $reply);
            if (!empty($data['reply']['emotitag'])) {
                $data_clean['reply']['emotitag'] = array_intersect_key($data['reply']['emotitag'], $emotitag);
            }

        // Process data as a topic post    
        } else if (array_key_exists('topic', $data)) {

            // Checks for required fields in $data
            $this->isRequired($data, 'topic', $topic);

            $data_clean['topic'] = array_intersect_key($data['topic'], $topic);
            if (!empty($data['topic']['emotitag'])) {
                $data_clean['topic']['emotitag'] = array_intersect_key($data['topic']['emotitag'], $emotitag);
            }
        }

        return $data_clean;
    }

    /**
     * Checks for missing required fields
     * 
     * @return boolean|array True if successful, array of errors otherwise.
     */
    private function isRequired($data, $type, $rulesArray) {

        $reqArray = array_keys($rulesArray, 'required');
        foreach ($reqArray as $key) {
            if (empty($data[$type][$key])) {

                $this->errors[] = ucwords(str_replace('_', ' ', $key)) . ' can\'t be blank.';
            }
        }

        return true;
    }

    /**
     * Creates endpoint url for puts/posts/deletes.
     * Analyzes data[url] to match patterns and creates endpoint url based on matches,
     * otherwise does nothing leaving rest_endpoint unchanged
     * 
     * @param array $data
     * 
     */
    private function setEndpointUrl($data='') {
        $matches = array();

        $url = filter_var(urldecode($data['url']), FILTER_SANITIZE_URL);

        if ((!empty($data)) && (filter_var($url, FILTER_VALIDATE_URL))) {

            $this->debug('setEndpointUrl() Url was passed to setEndpointURL...');

            if (preg_match('|^http(s?)://api.getsatisfaction.com/{1}(topics)/([0-9]*)/{1}(replies)/([0-9]*)|', $url, $matches)) {

                $this->debug('setEndpointUrl() Matched endpoint url as a comment');
                $this->rest_endpoint = 'http' . $matches[1] . '://api.getsatisfaction.com/' . $matches[4] . '/' . $matches[5] . '/comments.json';
            } else if (preg_match('|^http(s?)://api.getsatisfaction.com/{1}(topics)/([0-9]*)|', $url, $matches)) {

                $this->debug('setEndpointUrl() Matched endpoint url as a reply');
                $this->rest_endpoint = 'http' . $matches[1] . '://api.getsatisfaction.com/' . $matches[2] . '/' . $matches[3] . '/replies.json';
            } else { // can do further regex for other endpoints such as stars, me-toos etc... 
                $this->debug('setEndpointUrl() setEndpointUrl: Unable to match url pattern.');
            }
        }
    }

    /**
     * Cleans post / get data. 
     * @example $_POST = array_map('cleanData', $_POST);
     * @param mixed $data
     * @return string
     * 
     */
    public function cleanData($data,$strip=false) {
        $array = array();
        
        if (is_array($data)) {
            if ($this->isAssoc($data)) {

                foreach ($data as $dat => $ta) {

                    $dat = trim($dat);
                    $dat = mb_convert_encoding($dat, 'UTF-8', 'UTF-8');
                    if ($strip) {
                        $dat = htmlentities($dat, ENT_NOQUOTES, 'UTF-8');
                        $dat = filter_var($dat,FILTER_SANITIZE_SPECIAL_CHARS, array('FILTER_FLAG_STRIP_LOW','FILTER_FLAG_STRIP_HIGH'));
                        
                    }
                    if (is_array($ta)) {
                        $ta = Satisfying::cleanData($ta);
                    } else {
                        $ta = trim($ta);
                        $ta = mb_convert_encoding($ta, 'UTF-8', 'UTF-8');
                        if ($strip) {
                        $ta = htmlentities($ta, ENT_NOQUOTES, 'UTF-8');
                        $ta = filter_var($ta,FILTER_SANITIZE_SPECIAL_CHARS,  array('FILTER_FLAG_STRIP_LOW','FILTER_FLAG_STRIP_HIGH'));
                            
                        }
                    }
                    $array[$dat] = $ta;
                }
                return $array;
            } else {
                foreach ($data as $dat) {

                    $dat = trim($dat);
                    $dat = mb_convert_encoding($dat, 'UTF-8', 'UTF-8');
                    if ($strip) {
                        $dat = htmlentities($dat, ENT_NOQUOTES, 'UTF-8');
                        $dat = filter_var($dat,FILTER_SANITIZE_SPECIAL_CHARS,  array('FILTER_FLAG_STRIP_LOW','FILTER_FLAG_STRIP_HIGH'));
                        
                    }
                    $array[] = $dat;
                }
                return $array;
            }
        } else {

            $data = trim($data);
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            if ($strip) {
            $data = htmlentities($data, ENT_QUOTES, 'UTF-8');
            $data = filter_var($data,FILTER_SANITIZE_SPECIAL_CHARS,  array('FILTER_FLAG_STRIP_LOW','FILTER_FLAG_STRIP_HIGH'));
                
            }
            return $data;
        }
    }

    /**
     * Checks for cache json file.
     * Format should be like this: api.getsatisfaction.com_companies_quickoffice_products.json
     * @return boolean|string False if no cache found otherwise file contents as string.
     */
    private function isCached($url) {
        $filename = str_replace('/', '_', ltrim($url, 'http://'));
        if (file_exists($this->cache .DIRECTORY_SEPARATOR . $filename)) {
            $string = file_get_contents($this->cache .DIRECTORY_SEPARATOR . $filename);
            return $string;
        } else {
            return false;
        }
    }

    /**
     * Checks for associative array.
     * @param array $arr
     * @return boolean 
     */
    private function isAssoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Formats dates the GetSatisfaction way. 
     * @example "37 minutes ago."
     * borrowed from https://github.com/kjbekkelund/php-satisfaction
     * 
     * @param date/time $time
     * @return string 
     */
    public function format_date($time) {
        $now = new DateTime();
        $now = $now->format('Y/m/d h:i:s O'); //2011/09/13 21:38:30 +0000
        $diff = strtotime($now) - strtotime($time);

        if ($diff < 90)
            return "about a minute ago";# short circuit the otherwise-good logic below

        if ($diff < 60) {
            $result = $diff;
            $result .= $result == 1 ? " second" : " seconds";
        } else if ($diff < 3600) {
            $result = (ceil($diff / 60));
            $result .= $result == 1 ? " minute" : " minutes";
        } else if ($diff < 24 * 3600) {
            $result = (ceil($diff / 3600));
            $result .= $result == 1 ? " hour" : " hours";
        } else if ($diff < 7 * 24 * 3600) {
            $result = (ceil($diff / (24 * 3600)));
            $result .= $result == 1 ? " day" : " days";
        } else if ($diff < 30 * 24 * 3600) {
            $result = (ceil($diff / (7 * 24 * 3600)));
            $result .= $result == 1 ? " week" : " weeks";
        } else if ($diff < 365 * 24 * 3600) {
            $result = (ceil($diff / (30 * 24 * 3600)));
            $result .= $result == 1 ? " month" : " months";
        } else {
            $result = (ceil($diff / (365.24 * 24 * 3600)));
            $result .= $result == 1 ? " year" : " years";
        }

        return ($result . " ago");
    }

    /**
     * Strips tags. Good for limiting formatting on mobile
     * @param string $string
     * @return string
     */
    public function format_reply($string) {
        $string = strip_tags(stripslashes($string), "<p><em><b><strong><i><img><a><br>");
        $string = explode($this->reply_foot_divider, $string);
        $string = $string[0];
        return $string;
    }

    public function format_strip_delux($string) {
        $return='';
        $pieces = str_split($string);
        foreach ($pieces as $piece) {
            if (preg_match("|[\w\s]|",$piece)) {
                $return .= $piece;
            }
        }
        
        return $return;
    }    
    
    /**
     * Truncates a string.
     * 
     * @param string $string The string you want to truncate
     * @param int $limit The max number of characters you want returned.
     * @param string $break Breakpoint for truncation.
     * @param string $pad Characters to append to the truncated string.
     * 
     * @return string
     */
    public function format_truncate($string, $limit, $break=".", $pad="...") {
        $string = explode($this->reply_foot_divider, $string);
        $string = $string[0];
        $string = strip_tags(stripslashes($string));
        // Return with no change if string is shorter than $limit
        if (strlen($string) <= $limit)
            return $string;

        // Is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }

    /**
     * Sends data string to error log for debugging.
     * 
     * @param mixed $data
     */
    public function debug($data) {
        if ($this->debug) {
            trigger_error(str_replace(array("\\", "\n"), '', var_export($data, true)));
        }
    }

    /**
     * Sends string to error log on error.
     * 
     * @param mixed $data
     */
    public function error($data) {
        trigger_error(var_export($data, true));
    }

    /**
     *  Sends popup modal alert message to jquery mobile page.
     * 
     * @param string $message
     * @param string $alertPageId the page id where the alert should execute.
     */
    public function setAlert($message, $alertPageId = false) {
        if ($alertPageId) {
            $this->alertPageId = json_encode((string) $alertPageId);
        }
        $this->alert = json_encode((string) $message);
    }

    /**
     * Gets alert message if it exists.
     * 
     * @return string|null Null if no alert exists.
     */
    public function getAlert() {
        if (!empty($this->alert)) {
            if (!empty($this->alertPageId)) {
                $pageId = '#' . ltrim($this->alertPageId, '#');
            } else {
                $pageId = '[data-role="page"]';
            }
            $data = '<script type="text/javascript">
                        $(document).delegate("' . $pageId . '", "pageinit", function() { alert(' . $this->alert . '); });
                        $(document).undelegate("' . $pageId . '");
                    </script>';
            $this->alert = '';
            return $data;
        }
    }
}
?>
