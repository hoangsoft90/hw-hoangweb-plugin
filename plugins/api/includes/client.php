<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/12/2015
 * Time: 10:50
 */
//hwlib_register();
/*create XML-RPC PHP client*/
//call method
$client = new HW_XMLRPC_Client( "http://localhost/wordpress/xmlrpc.php" );
//$available_methods = $client->call( 'system.listMethods' );
//print_r( $available_methods );