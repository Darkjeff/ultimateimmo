#!/usr/bin/php
<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		scripts/myscript.php
 *	\ingroup	mymodule
 *	\brief		This file is an example command line script
 *				Put some comments here
 */

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi') {
	echo "Error: You are using PHP for CGI. To execute ";
	echo $script_file;
	echo " from command line, you must use PHP for CLI mode.\n";
	exit;
}

// Global variables
$version = '1.29';
$error = 0;


// -------------------- START OF YOUR CODE HERE --------------------
// Include Dolibarr environment
require_once $path . "../../htdocs/master.inc.php";
// After this $db, $mysoc, $langs and $conf->entity are defined.
// Opened handler to database will be closed at end of file.
//$langs->setDefaultLang('en_US'); 	// To change default language of $langs
$langs->load("main");	// To load language file for default language
@set_time_limit(0);	 // No timeout for this script
// Load user and its permissions
// Load user for login 'admin'. Comment line to run as anonymous user.
$result = $user->fetch('', 'admin');
if ( ! $result > 0) {
	dol_print_error('', $user->error);
	exit;
}
$user->getrights();


echo "***** " . $script_file . " (" . $version . ") *****\n";
if (! isset($argv[1])) {
	// Check parameters
	echo "Usage: " . $script_file . " param1 param2 ...\n";
	exit;
}
echo '--- start' . "\n";
echo 'Argument 1=' . $argv[1] . "\n";
echo 'Argument 2=' . $argv[2] . "\n";


// Start of transaction
$db->begin();


// Examples for manipulating class skeletonclass
require_once DOL_DOCUMENT_ROOT . "/mymodule/myclass.class.php";
$myobject = new SkeletonClass($db);

// Example for inserting creating object in database
/*
	dol_syslog($script_file." CREATE", LOG_DEBUG);
	$myobject->prop1='value_prop1';
	$myobject->prop2='value_prop2';
	$id=$myobject->create($user);
	if ($id < 0) { $error++; dol_print_error($db,$myobject->error); }
	else echo "Object created with id=".$id."\n";
 */

// Example for reading object from database
/*
	dol_syslog($script_file." FETCH", LOG_DEBUG);
	$result=$myobject->fetch($id);
	if ($result < 0) { $error; dol_print_error($db,$myobject->error); }
	else echo "Object with id=".$id." loaded\n";
 */

// Example for updating object in database
// ($myobject must have been loaded by a fetch before)
/*
	dol_syslog($script_file." UPDATE", LOG_DEBUG);
	$myobject->prop1='newvalue_prop1';
	$myobject->prop2='newvalue_prop2';
	$result=$myobject->update($user);
	if ($result < 0) { $error++; dol_print_error($db,$myobject->error); }
	else echo "Object with id ".$myobject->id." updated\n";
 */

// Example for deleting object in database
// ($myobject must have been loaded by a fetch before)
/*
	dol_syslog($script_file." DELETE", LOG_DEBUG);
	$result=$myobject->delete($user);
	if ($result < 0) { $error++; dol_print_error($db,$myobject->error); }
	else echo "Object with id ".$myobject->id." deleted\n";
 */


// An example of a direct SQL read without using the fetch method
/*
	$sql = "SELECT field1, field2";
	$sql.= " FROM ".MAIN_DB_PREFIX."c_pays";
	$sql.= " WHERE field3 = 'xxx'";
	$sql.= " ORDER BY field1 ASC";

	dol_syslog($script_file." sql=".$sql, LOG_DEBUG);
	$resql=$db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$i = 0;
		if ($num) {
			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				if ($obj) {
					// You can use here results
					echo $obj->field1;
					echo $obj->field2;
				}
				$i++;
			}
		}
	} else {
		$error++;
		dol_print_error($db);
	}
 */


// -------------------- END OF YOUR CODE --------------------

if ( ! $error) {
	$db->commit();
	echo '--- end ok' . "\n";
} else {
	echo '--- end error code=' . $error . "\n";
	$db->rollback();
}

$db->close(); // Close database opened handler

return $error;
