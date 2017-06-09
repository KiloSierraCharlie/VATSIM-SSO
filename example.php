<?php
require( "Authentication.php" );

if ( session_status() === PHP_SESSION_NONE ){ session_start(); }

if( isset( $_SESSION['AuthHandler'] ) ){
  
  $AuthHandler = unserialize( $_SESSION['AuthHandler'] );
  
}else{

  $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https://' : 'http://';
  $AuthHandler = new Authentication( $http.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'] );
  
}
if( $AuthHandler->shouldCheckLogin() ){
  
  $AuthHandler->checkLogin();
  
}
if( !$AuthHandler->isLoggedIn() ){
  
  $_SESSION["AuthHandler"] = serialize( $AuthHandler );
  $AuthHandler->Login();
  
}else{
  
  print( "Hello", $AuthHandler->getUserDetails()->name_first );
  echo '<pre style="font-size: 11px;">';
    print_r( $AuthHandler->getUserDetails() );
  echo '</pre>'

}

$_SESSION["AuthHandler"] = serialize( $AuthHandler );