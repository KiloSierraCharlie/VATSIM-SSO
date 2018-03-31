<?php

require('OAuth.php');
require('SSO.class.php');

class Authentication{

  private $SSOHandler;
  private $loggedIn;
  private $User;
  private $returnURL;
  
  function __construct( $returnURL="/login" ){
    
    $this->Base = "http://sso.hardern.net/server/";
    $this->Key = "SSO_DEMO";
    $this->Secret = "js8Sm7nit-2a_~k_~My6_~";
    $this->Method = "RSA";
    $this->Cert = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAtHWe0DVmsY4oZWu7N4Xnv0QJQr/fpPDllEGzEFatcpXpIYkF
ZjvtCkyS/wz/q8ZUYF+gojzfrVA6gsu08ph61GlaFFYKTI+fJfNKxgK8YFHiO67b
bSUo1JhTURAM+0l5OB0ZfG9P7CcZuPp8CvRR/sWrq0D5dchw2XAG55BR1OoshH7V
iT93EZT5rIHKenSduZYcgjg0G6sJZUoSQJWJ5gM9CCdX8c16AL4V0ono+g0Liubr
CNji4vzlv2mlpcCXp280yPL87+AI7PDP/8IJJTD0lC5ziWLr81iIYKEUj8ZW6itp
g18HW35yO/Uh8jx5o9IFyyeeM8VvtGHBnC/x8QIDAQABAoIBAEcLW1eq/l/+kGbF
T/Je9Eq3dVHjCh/8etWwO9e92BEZdauvLwH9q6d3WBOzQWCV9xft7eaRQpoP0Mk3
e2haoVjIlR8WdH2JuWUOcCyeXR0qf39xqo9Apt9zjLGIOSQg2+k8g5EanYNiLV1G
t9j0MOe1WI1WSh+Vpt/jm8REbAL++MLwC6er4SGod47K5bRfWFURqqphFkRDC4hR
LBA76xwXzfH07SPMC7czpSIDZ/rugd2lcG5eyPNTmieGlhxzmpGXf7CqnJ9N9lW2
4br+O1vKttBngJr7/E44MX+3geDMD/Y/v5ocUXxNNFq4U6No/1nCZuJbvksUIQMK
xA+TkXECgYEA4yJPc5Lc/tzB/9+rjS0ivTR/NVT2xnIyjy1iGEVLcRH41aE3WArI
RXgjAmUSbDlUpRl0lAhb1wZKLurCZq8w7SnLwORt018pNa4RAOim6oQxoNx53ZOH
3Pxy8yJwMRH0ygcqecXb5HG26O4jVM0v5OxQrGhNdKGQTQAamOQC4kUCgYEAy2TG
0+F+SwaUyreb5wvsPO4f8dezJiZlicXLiTaDva8NSnYy7uD8YR79zCQHKMnnQRrS
3wQRdTa8Uux/qQ9KNgsJoK3qds9EZbBkC9WmdkpWScZElcbmAnNdU1xxgV5s9me2
7dYt62SWmp5o83TgQuRGto6XrYlpkqxh1quUIb0CgYBxBk3LETXvjSLslPzlD6TN
yiXTACicr48BOHAr66+S4IKWq9bCdPsbtqCVPH5iZFT+oyAj2dT5tyOLHT8Hof4S
xk2h+wm7uQrkr22+qgcFhCq+BXiPDi75hJYe7vAtOuY48j/swMyfbQa7+mSSelhu
7jlm0bBZbVxFNNwHYM8ekQKBgDHl4VltwpwJW0t4TAkcxbIVAUVFk6/ST6rDGbHp
69wHLA2OfeRY0dHJ1p3UYOVC0zcHq6AG0XmeGgmF97O0CpLDlnMS96h6JN+FrKWy
non6prympYDRMPB8+PJQhZAlaDnzK8+hcdaD8Oax4jGIBNSkSCzVQqTSR1IydDZE
Fa1JAoGBAMEVbkFUZOyu4hUuJMSbjpmXZ9c3zWUT8/rloEzE2ULwXAQZK4bMZC0K
m5KIwKcrg+VqgwA3Ed0+PC05AB0wsHmBUxakAMerQQOLkotn9+10MnpaZ51KmuwV
qKqwSlZfUCpYDhdfv6aO24nirb0UHWoxQhU6h7vvUotRWxjzQRMR
-----END RSA PRIVATE KEY-----
EOD;
    
    $this->returnURL = $returnURL . "?return";
    $this->loggedIn = false;
    $this->User = array();
    
    if ( session_status() === PHP_SESSION_NONE ){
      session_start();
    }
    
    if( isset( $_SESSION['AuthHandler'] ) ){
    
      $StoredHandler = unserialize( $_SESSION['AuthHandler'] );
      $this->User = $StoredHandler->getUserDetails();
      $this->loggedIn = $StoredHandler->isLoggedIn();
      
    }
    
    $this->SSO = new SSO( $this->Base, $this->Key, $this->Secret, $this->Method, $this->Cert );
    
  }

  public function Login(){
    
    $token = $this->SSO->requestToken( $this->returnURL, false, false );
    if ( $token ){
      
        $_SESSION['oauth'] = array(
            'key' => (string)$token->token->oauth_token,
            'secret' => (string)$token->token->oauth_token_secret
        );
        
        return $this->SSO->sendToVatsim();
        
    } else {
      
        return false;
        
    }
    
  }
  
  public function checkLogin(){
    
    if ( isset( $_GET['return'] ) && isset( $_GET['oauth_verifier'] ) && !isset( $_GET['oauth_cancel'] ) ){
      
      if ( isset( $_SESSION['oauth'] ) && isset( $_SESSION['oauth']['key'] ) && isset( $_SESSION['oauth']['secret'] ) ){
        
        if ( @$_GET['oauth_token'] != $_SESSION['oauth']['key'] ){
          
          return false;
          
        }
        if (@!isset($_GET['oauth_verifier'])){
          
          return false;
          
        }
        
        $user = $this->SSO->checkLogin( $_SESSION['oauth']['key'], $_SESSION['oauth']['secret'],  @$_GET['oauth_verifier'] );
        
        if( $user ){
          
          unset( $_SESSION['oauth'] );
          $this->User = $user->user;
          $this->loggedIn = true;
          return true;
          
        } else {
          
          return false;
          
        }
      } 
    } else if ( isset( $_GET['return'] ) && isset( $_GET['oauth_cancel'] ) ){
      
        return false;
        
    }      
    
  }
  
  public function shouldCheckLogin(){
    
    return isset( $_GET['return'] ) && isset( $_GET['oauth_verifier'] ) && !isset( $_GET['oauth_cancel'] );
    
  }
  
  public function getUserDetails(){
    
    if( $this->loggedIn ){
      
      return $this->User;
      
    }
    
    return false;
    
  }
    
  public function isLoggedIn(){
    
    return $this->loggedIn;
    
  }
  
  public function Logout(){
    
    $this->User = Array();
    $this->loggedIn = false;
    unset( $_SESSION["AuthHandler"] );
    
  }
  
}
