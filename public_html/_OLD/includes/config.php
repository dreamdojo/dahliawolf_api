<?php
/*
$config = array(
			'Database' => array(
				 'driver' =>	'mysql'
				,'persistent'	=> false
				,'host' => 'localhost'
				,'login' => 'root'
				,'password'	=> ''
				,'database' => 'dahlia_db'
				,'prefix'	=> ''
				,'encoding'	=> 'utf-8'
			)
			,'JsonFile' => array(
				'auctions' =>'tmp/games.json'
			)
			,'APIServer' => array(
				 'host' => 'http://api.dahliawolf.com'
				,'version' =>''
				,'arcade' =>'/arcade.php'
			)
		);
*/
$config = array(
			'Database' => array(
				 'driver' =>	'mysql'
				,'persistent'	=> false
				,'host' => 'localhost'
				,'login' => 'dahlia_db'
				,'password'	=> 'Jgv9EZ3G.WE6'
				,'database' => 'dahlia_db'
				,'prefix'	=> ''
				,'encoding'	=> 'utf-8'
			)
			,'JsonFile' => array(
				'auctions' =>'tmp/games.json'
			)
			,'APIServer' => array(
				 'host' => 'http://api.dahliawolf.com'
				,'version' =>''
				,'arcade' =>'/arcade.php'
			)
		);
			
$config['basedir']     =  '/var/www/www.dahliawolf.com/htdocs/dahliawolf';
$config['baseurl']     =  'http://www.dahliawolf.com';

$DBTYPE = 'mysql';
$DBHOST = 'localhost';
$DBUSER = 'shop_production';
$DBPASSWORD = 'orMicntKNP';
$DBNAME = 'dahlia_pinmeweb2';

$config['enable_fc'] = "1";

?>