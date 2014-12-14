<?php
class EAPI
{
    const VERIFY_USER_FAILURE = 2001;
    const CURL_ERROR = 2002;
    const PHP_SESSION_NOT_STARTED = 2003;
    const MISSING_PARAMETERS = 2004;

    public $url;
    public $clientCode;
    public $username;
    public $password;
    public $sslCACertPath;

    public function __construct($url = null, $clientCode = null, $username = null, $password = null, $sslCACertPath = null)
    {
        $this->url = $url;
        $this->clientCode = $clientCode;
        $this->username = $username;
        $this->password = $password;
        $this->sslCACertPath = $sslCACertPath;
    }

    public function invoke($request, $parameters = array())
    {
        //$this->debug("invoke $request $this Parameters<pre>".print_r($parameters,true)."</pre>");
        //validate that all required parameters are set
        if(!$this->url OR !$this->clientCode OR !$this->username OR !$this->password){
            throw new Exception('Missing parameters', self::MISSING_PARAMETERS);
        }

        //add extra params
        $parameters['request'] = $request;
        $parameters['clientCode'] = $this->clientCode;
        $parameters['version'] = '1.0';
        if($request != "verifyUser") $parameters['sessionKey'] = $this->getSessionKey();

        $this->debug("Preparing request to {$this->url} with params<br /><pre>".print_r($parameters,true)."</pre>");
        //create request
        $handle = curl_init($this->url);

        //set the payload
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $parameters);

        //return body only
        curl_setopt($handle, CURLOPT_HEADER, 0);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

        //create errors on timeout and on response code >= 300
        curl_setopt($handle, CURLOPT_TIMEOUT, 45);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);

        //set up host and cert verification
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($handle, CURLOPT_SSLVERSION,3);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        if($this->sslCACertPath) {
            curl_setopt($handle, CURLOPT_CAINFO, $this->sslCACertPath);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        }

        //run
        $response = curl_exec($handle);
        $error = curl_error($handle);
        $errorNumber = curl_errno($handle);
        curl_close($handle);
        if($error) throw new Exception('CURL error: '.$response.':'.$error.': '.$errorNumber, self::CURL_ERROR);

        $response = json_decode($response);

        $this->debug("Response status<br /><pre>".print_r($response->status,true)."</pre>");

        $this->throwExceptionIfNecessary($parameters , $response);

        return $response;
    }

    protected function getSessionKey()
    {
        $this->debug("Getting session key");
        //test for session
        if(!isset($_SESSION)) throw new Exception('PHP session not started', self::PHP_SESSION_NOT_STARTED);

        //if no session key or key expired, then obtain it
        if(
            !isset($_SESSION['EAPISessionKey'][$this->clientCode][$this->username]) ||
            !isset($_SESSION['EAPISessionKeyExpires'][$this->clientCode][$this->username]) ||
            $_SESSION['EAPISessionKeyExpires'][$this->clientCode][$this->username] < time()
        ) {
            $this->debug("Making request for new session key");
            //make request
            $response = $this->invoke("verifyUser", array("username" => $this->username, "password" => $this->password));


            //check failure
            if(!isset($response->records[0]->sessionKey)) {
                unset($_SESSION['EAPISessionKey'][$this->clientCode][$this->username]);
                unset($_SESSION['EAPISessionKeyExpires'][$this->clientCode][$this->username]);

                $e = new Exception('Verify user failure', self::VERIFY_USER_FAILURE);
                $e->response = $response;
                throw $e;
            }

            //cache the key in PHP session
            $_SESSION['EAPISessionKey'][$this->clientCode][$this->username] = $response->records[0]->sessionKey;
            $_SESSION['EAPISessionKeyExpires'][$this->clientCode][$this->username] = time() + $response->records[0]->sessionLength - 30;

        }

        //return cached key
        $key = $_SESSION['EAPISessionKey'][$this->clientCode][$this->username];
        $this->debug("Session key is $key");
        return $key;
    }


    private function throwExceptionIfNecessary($request , $response){

        if ($response->status->errorCode == 0) {
            return;
        }

        $error = array();
        $error[1000] = "API is under maintenance, please try again in a couple of minutes.";
        $error[1001] = "Account not found.";
        $error[1002] = "Hourly request limit (by default 1000 requests) has been exceeded for this account. Please resume next hour.";
        $error[1003] = "Cannot connect to account database.";
        $error[1005] = "API call is not specified, or unknown API call. {request}";
        $error[1006] = "This API call is not available on this account. (Account needs upgrading, or an extra module needs to be installed.)";
        $error[1007] = "Unknown output format requested; input parameter responseType should be either JSON or XML.";
        $error[1008] = "Either a) database is under regular maintenance (please try again in a couple of minutes), or b) your application is not connecting to the correct API server. Make sure that you are using correct API URL: https://yourcustomercode.erply.com/api/. If your API URL is correct, it might be that your ERPLY account has been recently transferred between hosting environments and your local DNS cache is out of date (domain name yourcustomercode.erply.com is not being resolved to correct web server). Try flushing DNS cache in your computer, server, or application engine.";
        $error[1009] = "This API call requires authentication parameters (a session key, authentication key, or service key), but none were found.";
        $error[1010] = "Required parameter '{errorField}' are missing.";
        $error[1011] = "Invalid classifier ID '{errorField}={errorFieldInputValue}', there is no such item.";
        $error[1012] = "A parameter must have a unique value. ('{errorField}={errorFieldInputValue}'";
        $error[1013] = "Inconsistent parameter set (for example, both product and service IDs specified for an invoice row).";
        $error[1014] = "Incorrect data type or format. ('{errorField}={errorFieldInputValue}')";
        $error[1015] = "Malformed request (eg. parameters containing invalid characters).";
        $error[1016] = "Invalid value. ('{errorField}={errorFieldInputValue}')";
        $error[1017] = "Document has been confirmed and its contents and warehouse ID cannot be edited any more.";
        $error[1020] = "Bulk API call contained more than 100 sub-requests (max 100 allowed). The whole request has been ignored.";
        $error[1021] = "Another instance of the same report is currently running. Please wait and try again in a minute. (For long-running reports, API processes incoming requests only one at a time.)";
        $error[1040] = "Invalid coupon identifier – such coupon has not been issued.";
        $error[1041] = "Invalid coupon identifier – this coupon has already been redeemed.";
        $error[1042] = "Customer does not have enough reward points.";
        $error[1043] = "Employee already has an appointment on that time slot. Please choose a different start and end time for appointment.";
        $error[1044] = "Default length for this service has not been defined in Erply backend – cannot suggest possible time slots.";
        $error[1045] = "Invalid coupon identifier – this coupon has expired.";
        $error[1046] = "Sales Promotion – The promotion contains multiple conflicting requirements or conditions, please specify only one.";
        $error[1047] = "Sales Promotion – Promotion requirements or conditions not specified.";
        $error[1048] = "Sales Promotion – The promotion contains multiple conflicting awards, please specify only one.";
        $error[1049] = "Sales Promotion – Promotion awards not specified.";
        $error[1050] = "Username/password missing.";
        $error[1051] = "Login failed.";
        $error[1052] = "User has been temporarily blocked because of repeated unsuccessful login attempts.";
        $error[1053] = "No password has been set for this user, therefore the user cannot be logged in.";
        $error[1054] = "API session has expired. Please call API “verifyUser” again (with correct credentials) to receive a new session key.";
        $error[1055] = "Supplied session key is invalid; session not found.";
        $error[1056] = "Supplied session key is too old. User switching is no longer possible with this session key, please perform a full re-authentication via API “verifyUser”.";
        $error[1057] = "Your time-limited demo account has expired. Please create a new ERPLY demo account, or sign up for a paid account.";
        $error[1060] = "No viewing rights (in this module/for this item).";
        $error[1061] = "No adding rights (in this module).";
        $error[1062] = "No editing rights (in this module/for this item).";
        $error[1063] = "No deleting rights (in this module/for this item).";
        $error[1064] = "User does not have access to this location (store, warehouse).";
        $error[1065] = "This user account does not have API access. (It may be limited to POS or Erply backend operations only.)";
        $error[1071] = "This customer can buy for a full up-front payment only.";
        $error[1072] = "This customer does not earn new reward points and cannot exchange reward points for coupons.";
        $error[1080] = "Printing service is not running at the moment. (User can turn printing service on from their Erply account).";
        $error[1081] = "E-mail sending failed.";
        $error[1082] = "E-mail sending has been incorrectly set up, review settings in ERPLY. (Missing sender’s address or empty message content).";
        $error[1090] = "No file attached.";
        $error[1091] = "Attached file is not encoded with Base64.";
        $error[1092] = "Attached file exceeds allowed size limit.";

        if(isset($error[$response->status->errorCode])){

            $subs = array(
                '{request}' =>$response->status->request,
                '{errorCode}' =>$response->status->errorCode,
                '{errorField}' =>$response->status->errorField,
                '{errorFieldInputValue}' =>!isset($request[$response->status->errorField])? 'N/A' : $request[$response->status->errorField],
            );

            $message = strtr($error[$response->status->errorCode] , $subs);
            throw new Exception("Erply exeption #" . $response->status->errorCode . ". " . $message);
        }else{
            throw new Exception("Unknown Erply exeption #" . $response->status->errorCode);
        }

    }


    private function debug($s){
       // echo "<p><strong>EAPI:</strong> $s</p>\n";
    }

    function __toString()
    {
        return print_r($this , true);
    }


}