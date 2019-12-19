<?php

require __DIR__.'/db.settings.user.php';

try{
	$db = new PDO("mysql:host=".$db_host.";dbname=".$db_name.";port=".$db_port, $db_username, $db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->exec("SET NAMES 'utf8'");	
} catch (Exception $e) {
	echo "Could not connect to database.";
	exit;
}