<?
InitializeCSRFToken();


//----------------------------------------------------------------------------------
//  SetupCSRFToken()
//
//  Initalizes a CSRF token. This should be called after StartSession()
//
//  PARAMETERS: none
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function InitializeCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}



//----------------------------------------------------------------------------------
//  GetCSRFToken()
//
//  Gets the current CSRF token from the session
//
//  PARAMETERS: none
//
//  RETURN: The current CSRF token
//-----------------------------------------------------------------------------------
function CSRFToken()
{
    return $_SESSION['csrf_token'];
}



//----------------------------------------------------------------------------------
//  CheckCSRFToken()
//
//  Checks the passed in CSRF token to verify that it's valid
//
//  PARAMETERS:
//    token - The CSRF token to check
//
//  RETURN: The current CSRF token
//-----------------------------------------------------------------------------------
function CheckCSRFToken($token)
{
    if(!hash_equals(CSRFToken(), $token))
    {
      trigger_error("Invalid CSRF token", E_USER_WARNING);
      exit("Invalid CSRF Token");
    }
}
?>