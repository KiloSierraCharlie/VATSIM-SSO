<?php
    session_start();
    ini_set('error_reporting', E_ALL);
    ini_set("display_errors", 1);
    require('OAuth/OAuth.php');
    require('OAuth/SSO.class.php');
    require('OAuth/reporting.php');
    require('config.php');
    $SSO = new SSO($sso['base'], $sso['key'], $sso['secret'], $sso['method'], $sso['cert']);
    $sso_return = $sso['return'];
    unset($sso);
    if (isset($_GET['return']) && isset($_GET['oauth_verifier']) && !isset($_GET['oauth_cancel'])){
        if (isset($_SESSION[SSO_SESSION]) && isset($_SESSION[SSO_SESSION]['key']) && isset($_SESSION[SSO_SESSION]['secret'])){
            if (@$_GET['oauth_token']!=$_SESSION[SSO_SESSION]['key']){
                reportStatus( "Returned token does not match", $Auth_ErrorURL );
                die();
            }
            if (@!isset($_GET['oauth_verifier'])){
                reportStatus( "No verification code provided", $Auth_ErrorURL );
                die();
            }
            $user = $SSO->checkLogin($_SESSION[SSO_SESSION]['key'], $_SESSION[SSO_SESSION]['secret'], @$_GET['oauth_verifier']);
            if ($user){
                unset($_SESSION[SSO_SESSION]);
                loggedIn( $user->user );
                die();
            } else {
                $error = $SSO->error();
                reportStatus( "Code: " . $error['code'] . PHP_EOL . $error['message'], $Auth_ErrorURL );
                die();
            }
        } 
    } else if (isset($_GET['return']) && isset($_GET['oauth_cancel'])){
        reportStatus( "You cancelled your login!", $Auth_ErrorURL );
        die();
    }                                        
    $token = $SSO->requestToken($sso_return, false, false);
    if ($token){
        $_SESSION[SSO_SESSION] = array(
            'key' => (string)$token->token->oauth_token,
            'secret' => (string)$token->token->oauth_token_secret
        );
        $SSO->sendToVatsim();
    } else {
        $error = $SSO->error();
        reportStatus( "Code: " . $error['code'] . PHP_EOL . $error['message'], $Auth_ErrorURL );
    }
?>
