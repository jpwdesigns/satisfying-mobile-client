<?PHP
/**
 * This class has been improved from origial dev below and extended with improved
 * version of email validation function from http://www.tienhuis.nl/
 * @author Jeremy Williams (jpwdesigns@gmail.com)
 */

/*
  -------------------------------------------------------------------------
  PHP Form Validator (formvalidator.php)
  Version 1.1
  This program is free software published under the
  terms of the GNU Lesser General Public License.

  This program is distributed in the hope that it will
  be useful - WITHOUT ANY WARRANTY; without even the
  implied warranty of MERCHANTABILITY or FITNESS FOR A
  PARTICULAR PURPOSE.

  For updates, please visit:
  http://www.html-form-guide.com/php-form/php-form-validation.html

  Questions & comments please send to info@html-form-guide.com
  -------------------------------------------------------------------------
 */

/**
 * Carries information about each of the form validations
 */
class ValidatorObj {

    var $variable_name;
    var $validator_string;
    var $error_string;

}

/**
 * Base class for custom validation objects
 * */
class CustomValidator {

    function DoValidate(&$formars, &$error_hash) {
        return true;
    }

}

/** Default error messages */
define("E_VAL_REQUIRED_VALUE", "Please enter the value for %s");
define("E_VAL_MAXLEN_EXCEEDED", "Maximum length exceeded for %s.");
define("E_VAL_MINLEN_CHECK_FAILED", "Please enter input with length more than %d for %s");
define("E_VAL_ALNUM_CHECK_FAILED", "Please provide an alpha-numeric input for %s");
define("E_VAL_ALNUM_S_CHECK_FAILED", "Please provide an alpha-numeric input for %s");
define("E_VAL_NUM_CHECK_FAILED", "Please provide numeric input for %s");
define("E_VAL_ALPHA_CHECK_FAILED", "Please provide alphabetic input for %s");
define("E_VAL_ALPHA_S_CHECK_FAILED", "Please provide alphabetic input for %s");
define("E_VAL_EMAIL_CHECK_FAILED", "Please provide a valida email address");
define("E_VAL_LESSTHAN_CHECK_FAILED", "Enter a value less than %f for %s");
define("E_VAL_GREATERTHAN_CHECK_FAILED", "Enter a value greater than %f for %s");
define("E_VAL_REGEXP_CHECK_FAILED", "Please provide a valid input for %s");
define("E_VAL_DONTSEL_CHECK_FAILED", "Wrong option selected for %s");
define("E_VAL_SELMIN_CHECK_FAILED", "Please select minimum %d options for %s");
define("E_VAL_SELONE_CHECK_FAILED", "Please select an option for %s");
define("E_VAL_EQELMNT_CHECK_FAILED", "Value of %s should be same as that of %s");
define("E_VAL_NEELMNT_CHECK_FAILED", "Value of %s should not be same as that of %s");

/**
 * FormValidator: The main class that does all the form validations
 * */
class FormValidator {

    var $validator_array;
    var $error_hash;
    var $custom_validators;

    function FormValidator() {
        $this->validator_array = array();
        $this->error_hash = array();
        $this->custom_validators = array();
    }

    function AddCustomValidator(&$customv) {
        array_push($this->custom_validators, $customv);
    }

    function addValidation($variable, $validator, $error) {
        $validator_obj = new ValidatorObj();
        $validator_obj->variable_name = $variable;
        $validator_obj->validator_string = $validator;
        $validator_obj->error_string = $error;
        array_push($this->validator_array, $validator_obj);
    }

    function GetErrors() {
        return $this->error_hash;
    }

    function ValidateForm($data = array()) {
        $bret = true;

        $error_string = "";
        $error_to_display = "";

        if (count($data) > 0 ) {
             $form_variables = $data;
        } else if (strcmp($_SERVER['REQUEST_METHOD'], 'POST') == 0) {
            $form_variables = $_POST;
        } else {
            $form_variables = $_GET;
        }

        $vcount = count($this->validator_array);


        foreach ($this->validator_array as $val_obj) {
            if (!$this->ValidateObject($val_obj, $form_variables, $error_string)) {
                $bret = false;
                $this->error_hash[$val_obj->variable_name] = $error_string;
            }
        }

        if (true == $bret && count($this->custom_validators) > 0) {
            foreach ($this->custom_validators as $custom_val) {
                if (false == $custom_val->DoValidate($form_variables, $this->error_hash)) {
                    $bret = false;
                }
            }
        }
        return $bret;
    }

    function ValidateObject($validatorobj, $formvariables, &$error_string) {
        $bret = true;

        $splitted = explode("=", $validatorobj->validator_string);
        $command = $splitted[0];
        $command_value = '';

        if (isset($splitted[1]) && strlen($splitted[1]) > 0) {
            $command_value = $splitted[1];
        }

        $default_error_message = "";

        $input_value = ""; 
        $var_name_count = count(explode('[',$validatorobj->variable_name));
        if ($var_name_count > 1 && strstr($validatorobj->variable_name, '[]') === false) {
            preg_match_all("|([a-z_-]+)|i", $validatorobj->variable_name, $matches);  
            switch ($var_name_count) {
                case 2:
                    $input_value = @$formvariables[$matches[0][0]][$matches[0][1]];
                    break;
                case 3:
                    $input_value = @$formvariables[$matches[0][0]][$matches[0][1]][$matches[0][2]];
                    break;
                case 4:
                    $input_value = @$formvariables[$matches[0][0]][$matches[0][1]][$matches[0][2]][$matches[0][3]];
                    break;
            }         
        } else {
            if (isset($formvariables[$validatorobj->variable_name])) {
                $input_value = $formvariables[$validatorobj->variable_name];
            } 
        }    

        $bret = $this->ValidateCommand($command, $command_value, $input_value, $default_error_message, $validatorobj->variable_name, $formvariables);


        if (false == $bret) {
            if (isset($validatorobj->error_string) &&
                    strlen($validatorobj->error_string) > 0) {
                $error_string = $validatorobj->error_string;
            } else {
                $error_string = $default_error_message;
            }
        }//if
        return $bret;
    }

    function validate_req($input_value, &$default_error_message, $variable_name) {
        $bret = true;
        if (is_array($input_value)) {
            if (max(array_map('strlen', $input_value)) <= 0) {
                $bret = false;
                $default_error_message = sprintf(E_VAL_REQUIRED_VALUE, $variable_name);
            }
        } else if (!isset($input_value) ||  strlen($input_value) <= 0) {
            $bret = false;
            $default_error_message = sprintf(E_VAL_REQUIRED_VALUE, $variable_name);
        }
        return $bret;
    }

    function validate_maxlen($input_value, $max_len, $variable_name, &$default_error_message) {
        $bret = true;
        if (isset($input_value)) {
            $input_length = strlen($input_value);
            if ($input_length > $max_len) {
                $bret = false;
                $default_error_message = sprintf(E_VAL_MAXLEN_EXCEEDED, $variable_name);
            }
        }
        return $bret;
    }

    function validate_minlen($input_value, $min_len, $variable_name, &$default_error_message) {
        $bret = true;
        if (isset($input_value)) {
            $input_length = strlen($input_value);
            if ($input_length < $min_len) {
                $bret = false;
                $default_error_message = sprintf(E_VAL_MINLEN_CHECK_FAILED, $min_len, $variable_name);
            }
        }
        return $bret;
    }

    function test_datatype($input_value, $reg_exp) {
        if (ereg($reg_exp, $input_value)) {
            return false;
        }
        return true;
    }

    function validate_email($email) {
        /**
         * Modified from original developer's version to improve a couple functions.
         */
        
        
        /**
        * Function to validate email address format
        *
        *   $Id: VerifyEmailAddress.php 8 2008-01-13 22:51:10Z visser $ 
        *
        *   Email address verification with SMTP probes
        *   Dick Visser <dick@tienhuis.nl>
        *
        *   INTRODUCTION
        *
        *   This function tries to verify an email address using several tehniques,
        *   depending on the configuration.
        *
        *   Arguments that are needed:
        *
        *   $email (string)
        *   The address you are trying to verify
        *
        *   $domainCheck (boolean)
        *   Check if any DNS MX records exist for domain part
        *
        *   $verify (boolean)
        *   Use SMTP verify probes to see if the address is deliverable.
        *
        *   $probe_address (string)
        *   This is the email address that is used as FROM address in outgoing
        *   probes. Make sure this address exists so that in the event that the
        *   other side does probing too this will work.
        *
        *   $helo_address (string)
        *   This should be the hostname of the machine that runs this site.
        *
        *   $return_errors (boolean)
        *   By default, no errors are returned. This means that the function will evaluate
        *   to TRUE if no errors are found, and false in case of errors. It is not possible
        *   to return those errors, because returning something would be a TRUE.
        *   When $return_errors is set, the function will return FALSE if the address
        *   passes the tests. If it does not validate, an array with errors is returned.
        *
        *
        *   A global variable $debug can be set to display all the steps.
        *
        *
        *   EXAMPLES
        *
        *   Use more options to get better checking.
        *   Check only by syntax:  validateEmail('dick@tienhuis.nl')
        *   Check syntax + DNS MX records: validateEmail('dick@tienhuis.nl', true);
        *   Check syntax + DNS records + SMTP probe:
        *   validateEmail('dick@tienhuis.nl', true, true, 'postmaster@tienhuis.nl', 'outkast.tienhuis.nl');
        *
        *
        *   WARNING
        *
        *   This function works for now, but it may well break in the future.
        *
        * @param mixed $email
        * @param mixed $domainCheck
        * @param mixed $verify
        * @param mixed $probe_address
        * @param mixed $helo_address
        * @param mixed $return_errors
        * @return string
        */
            $domainCheck = false;
            $verify = false;
            $probe_address='';
            $helo_address='';
            $return_errors=false;
            global $debug;
            $server_timeout = 180; # timeout in seconds. Some servers deliberately wait a while (tarpitting)
            if($debug) {echo "<pre>";}
            # Check email syntax with regex
            if (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $email, $matches)) {
                $user = $matches[1];
                $domain = $matches[2];
                # Check availability of DNS MX records
                if ($domainCheck && function_exists('checkdnsrr')) {
                    # Construct array of available mailservers
                    if(getmxrr($domain, $mxhosts, $mxweight)) {
                        for($i=0;$i<count($mxhosts);$i++){
                            $mxs[$mxhosts[$i]] = $mxweight[$i];
                        }
                        asort($mxs);
                        $mailers = array_keys($mxs);
                    } elseif(checkdnsrr($domain, 'A')) {
                        $mailers[0] = gethostbyname($domain);
                    } else {
                        $mailers=array();
                    }
                    $total = count($mailers);
                    # Query each mailserver
                    if($total > 0 && $verify) {
                        # Check if mailers accept mail
                        for($n=0; $n < $total; $n++) {
                            # Check if socket can be opened
                            if($debug) { echo "Checking server $mailers[$n]...\n";}
                            $connect_timeout = $server_timeout;
                            $errno = 0;
                            $errstr = 0;
                            # Try to open up socket
                            if($sock = @fsockopen($mailers[$n], 25, $errno , $errstr, $connect_timeout)) {
                                $response = fgets($sock);
                                if($debug) {echo "Opening up socket to $mailers[$n]... Succes!\n";}
                                stream_set_timeout($sock, 30);
                                $meta = stream_get_meta_data($sock);
                                if($debug) { echo "$mailers[$n] replied: $response\n";}
                                $cmds = array(
                                    "HELO $helo_address",
                                    "MAIL FROM: <$probe_address>",
                                    "RCPT TO: <$email>",
                                    "QUIT",
                                );
                                # Hard error on connect -> break out
                                # Error means 'any reply that does not start with 2xx '
                                if(!$meta['timed_out'] && !preg_match('/^2\d\d[ -]/', $response)) {
                                    $error = "Error: $mailers[$n] said: $response\n";
                                    break;
                                }
                                foreach($cmds as $cmd) {
                                    $before = microtime(true);
                                    fputs($sock, "$cmd\r\n");
                                    $response = fgets($sock, 4096);
                                    $t = 1000*(microtime(true)-$before);
                                    if($debug) {echo htmlentities("$cmd\n$response") . "(" . sprintf('%.2f', $t) . " ms)\n";}
                                    if(!$meta['timed_out'] && preg_match('/^5\d\d[ -]/', $response)) {
                                        $error = "Unverified address: $mailers[$n] said: $response";
                                        break 2;
                                    }
                                }
                                fclose($sock);
                                if($debug) { echo "Succesful communication with $mailers[$n], no hard errors, assuming OK";}
                                break;
                            } elseif($n == $total-1) {
                                $error = "None of the mailservers listed for $domain could be contacted";
                            }
                        }
                    } elseif($total <= 0) {
                        $error = "No usable DNS records found for domain '$domain'";
                    }
                }
            } else {
                $error = 'Address syntax not correct';
            }
            if($debug) { echo "</pre>";}

            if($return_errors) {
                # Give back details about the error(s).
                # Return FALSE if there are no errors.
                if(isset($error)) return htmlentities($error); else return false;
            } else {
                # 'Old' behaviour, simple to understand
                if(isset($error)) return false; else return true;
            }
    }

    function validate_for_numeric_input($input_value, &$validation_success) {

        $more_validations = true;
        $validation_success = true;
        if (strlen($input_value) > 0) {

            if (false == is_numeric($input_value)) {
                $validation_success = false;
                $more_validations = false;
            }
        } else {
            $more_validations = false;
        }
        return $more_validations;
    }

    function validate_lessthan($command_value, $input_value, $variable_name, &$default_error_message) {
        $bret = true;
        if (false == $this->validate_for_numeric_input($input_value, $bret)) {
            return $bret;
        }
        if ($bret) {
            $lessthan = doubleval($command_value);
            $float_inputval = doubleval($input_value);
            if ($float_inputval >= $lessthan) {
                $default_error_message = sprintf(E_VAL_LESSTHAN_CHECK_FAILED, $lessthan, $variable_name);
                $bret = false;
            }//if
        }
        return $bret;
    }

    function validate_greaterthan($command_value, $input_value, $variable_name, &$default_error_message) {
        $bret = true;
        if (false == $this->validate_for_numeric_input($input_value, $bret)) {
            return $bret;
        }
        if ($bret) {
            $greaterthan = doubleval($command_value);
            $float_inputval = doubleval($input_value);
            if ($float_inputval <= $greaterthan) {
                $default_error_message = sprintf(E_VAL_GREATERTHAN_CHECK_FAILED, $greaterthan, $variable_name);
                $bret = false;
            }//if
        }
        return $bret;
    }

    function validate_select($input_value, $command_value, &$default_error_message, $variable_name) {
        $bret = false;
        if (is_array($input_value)) {
            foreach ($input_value as $value) {
                if ($value == $command_value) {
                    $bret = true;
                    break;
                }
            }
        } else {
            if ($command_value == $input_value) {
                $bret = true;
            }
        }
        if (false == $bret) {
            $default_error_message = sprintf(E_VAL_SHOULD_SEL_CHECK_FAILED, $command_value, $variable_name);
        }
        return $bret;
    }

    function validate_dontselect($input_value, $command_value, &$default_error_message, $variable_name) {
        $bret = true;
        if (is_array($input_value)) {
            foreach ($input_value as $value) {
                if ($value == $command_value) {
                    $bret = false;
                    $default_error_message = sprintf(E_VAL_DONTSEL_CHECK_FAILED, $variable_name);
                    break;
                }
            }
        } else {
            if ($command_value == $input_value) {
                $bret = false;
                $default_error_message = sprintf(E_VAL_DONTSEL_CHECK_FAILED, $variable_name);
            }
        }
        return $bret;
    }

    function ValidateCommand($command, $command_value, $input_value, &$default_error_message, $variable_name, $formvariables) {
        $bret = true;
        switch ($command) {
            case 'req': {
                    $bret = $this->validate_req($input_value, $default_error_message, $variable_name);
                    break;
                }

            case 'maxlen': {
                    $max_len = intval($command_value);
                    $bret = $this->validate_maxlen($input_value, $max_len, $variable_name, $default_error_message);
                    break;
                }

            case 'minlen': {
                    $min_len = intval($command_value);
                    $bret = $this->validate_minlen($input_value, $min_len, $variable_name, $default_error_message);
                    break;
                }

            case 'alnum': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z0-9]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALNUM_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

            case 'alnum_s': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z0-9 ]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALNUM_S_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

            case 'num':
            case 'numeric': {
                    $bret = $this->test_datatype($input_value, "[^0-9]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_NUM_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

            case 'alpha': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALPHA_CHECK_FAILED, $variable_name);
                    }
                    break;
                }
            case 'alpha_s': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z ]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALPHA_S_CHECK_FAILED, $variable_name);
                    }
                    break;
                }
            case 'email': {
                    if (isset($input_value) && strlen($input_value) > 0) {
                        $bret = $this->validate_email($input_value);
                        if (false == $bret) {
                            $default_error_message = E_VAL_EMAIL_CHECK_FAILED;
                        }
                    }
                    break;
                }
            case "lt":
            case "lessthan": {
                    $bret = $this->validate_lessthan($command_value, $input_value, $variable_name, $default_error_message);
                    break;
                }
            case "gt":
            case "greaterthan": {
                    $bret = $this->validate_greaterthan($command_value, $input_value, $variable_name, $default_error_message);
                    break;
                }

            case "regexp": {
                    if (isset($input_value) && strlen($input_value) > 0) {
                        if (!preg_match("$command_value", $input_value)) {
                            $bret = false;
                            $default_error_message = sprintf(E_VAL_REGEXP_CHECK_FAILED, $variable_name);
                        }
                    }
                    break;
                }
            case "dontselect":
            case "dontselectchk":
            case "dontselectradio": {
                    $bret = $this->validate_dontselect($input_value, $command_value, $default_error_message, $variable_name);
                    break;
                }//case

            case "shouldselchk":
            case "selectradio": {
                    $bret = $this->validate_select($input_value, $command_value, $default_error_message, $variable_name);
                    break;
                }//case
            case "selmin": {
                    $min_count = intval($command_value);

                    if (isset($input_value)) {
                        if ($min_count > 1) {
                            $bret = (count($input_value) >= $min_count ) ? true : false;
                        } else {
                            $bret = true;
                        }
                    } else {
                        $bret = false;
                        $default_error_message = sprintf(E_VAL_SELMIN_CHECK_FAILED, $min_count, $variable_name);
                    }

                    break;
                }//case
            case "selone": {
                    if (false == isset($input_value) ||
                            strlen($input_value) <= 0) {
                        $bret = false;
                        $default_error_message = sprintf(E_VAL_SELONE_CHECK_FAILED, $variable_name);
                    }
                    break;
                }
            case "eqelmnt": {

                    if (isset($formvariables[$command_value]) &&
                            strcmp($input_value, $formvariables[$command_value]) == 0) {
                        $bret = true;
                    } else {
                        $bret = false;
                        $default_error_message = sprintf(E_VAL_EQELMNT_CHECK_FAILED, $variable_name, $command_value);
                    }
                    break;
                }
            case "neelmnt": {
                    if (isset($formvariables[$command_value]) &&
                            strcmp($input_value, $formvariables[$command_value]) != 0) {
                        $bret = true;
                    } else {
                        $bret = false;
                        $default_error_message = sprintf(E_VAL_NEELMNT_CHECK_FAILED, $variable_name, $command_value);
                    }
                    break;
                }
        }//switch
        return $bret;
    }

//validdate command
}

?> 