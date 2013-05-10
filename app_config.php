<?php
/**
 * Application configuration script
 * @author Mike Brant
 * @author Jeremy Williams (jpwdesigns@gmail.com)
 * @version 1 2012-06-21
 */

/**
 * Define key directories
 */
define('CACHE_DIRECTORY', $server_config->cache_directory, false);

/**
* Define application-specific variables
*/
define('WEBSITE_DOMAIN', '', false);                // Example: help.quickoffice.com
define('COOKIE_DOMAIN', '', false);                 // Example: help.quickoffice.com
define('USE_GOOGLE_ANALYTICS', 1, false);           // Example: 1 or 0
define('GOOGLE_ANALYTICS_ACCOUNT_ID', '', false);   // Example: UA-xxxxxxxx-x
define('GOOGLE_ANALYTICS_DOMAIN', '', false);       // Example: help.quickoffice.com
define('GET_SATISFACTION_OAUTH_ROOT', '', false);   // Example: https://community.quickoffice.com/
define('GET_SATISFACTION_API_ROOT', '', false);     // Example: https://api.getsatisfaction.com
define('GET_SATISFACTION_COMPANY_NAME', '', false); // Example: quickoffice
define('GET_SATISFACTION_COMPANY_ID', '', false);   // Example: 86366
define('GET_SATISFACTION_KEY', '', false);          // Key provided by GetSat
define('GET_SATISFACTION_SECRET', '', false);       // Secret provided by GetSat
define('GET_SATISFACTION_DEBUG', false, false);     // true or false. Do not set to true in production

/**
 * Define HTTPS_ON value based on whether the X-Forwarded-Proto header from ELB is set to 'https'
 * This is helpful if hosted in AWS or other cloud environment and the ssl cert is hosted on a
 * load balancer instead of local machine.
 */
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || 
   (isset($_REQUEST['X-Forwarded-Proto']) && $_REQUEST['X-Forwarded-Proto'] === 'https') ||
   (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
   (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && (int)$_SERVER['HTTP_X_FORWARDED_PORT'] == 443)) {
    define('HTTPS_ON', true, false);
} else {
    define('HTTPS_ON', false, false);
}