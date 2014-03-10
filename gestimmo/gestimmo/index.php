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
 *	\file		mypage.php
 *	\ingroup	mymodule
 *	\brief		This file is an example php page
 *				Put some comments here
 */

//if (! defined('NOREQUIREUSER'))	define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))		define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))	define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))	define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))		define('NOCSRFCHECK','1');
//if (! defined('NOTOKENRENEWAL'))	define('NOTOKENRENEWAL','1');
// If there is no menu to show
//if (! defined('NOREQUIREMENU'))	define('NOREQUIREMENU','1');
// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREHTML'))	define('NOREQUIREHTML','1');
//if (! defined('NOREQUIREAJAX'))	define('NOREQUIREAJAX','1');
// If this page is public (can be called outside logged session)
//if (! defined("NOLOGIN"))			define("NOLOGIN",'1');
// Choose the following lines to use the correct relative path
// (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php")) {
	$res = @include("../main.inc.php");
}
/*
if (! $res && file_exists("../../main.inc.php")) {
	$res = @include("../../main.inc.php");
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include("../../../main.inc.php");
}
// The following should only be used in development environments
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include("../../../dolibarr/htdocs/main.inc.php");
}
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include("../../../../dolibarr/htdocs/main.inc.php");
}
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include("../../../../../dolibarr/htdocs/main.inc.php");
}
 */
 require_once DOL_DOCUMENT_ROOT.'/gestimmo/lib/gestimmo.lib.php';
if (! $res) {
	die("Main include failed");
}
// Change this following line to use the correct relative path from htdocs
// (do not remove DOL_DOCUMENT_ROOT)
//require_once DOL_DOCUMENT_ROOT . "gestimmo/class/gestimmo.class.php";
//require('../main.inc.php');
// Load translation files required by the page
$langs->load("gestimmo@mgestimmo");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$myparam = GETPOST('myparam', 'alpha');

// Access control
if ($user->societe_id > 0) {
	// External user
	accessforbidden();
}


/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

if ($action == 'add') {
	$myobject = new SkeletonClass($db);
	$myobject->prop1 = $_POST["field1"];
	$myobject->prop2 = $_POST["field2"];
	$result = $myobject->create($user);
	if ($result > 0) {
		// Creation OK
	} {
		// Creation KO
		$mesg = $myobject->error;
	}
}


/*
 * VIEW
 *
 * Put here all code to build page
 */

llxHeader('', 'Gestion Location', '');

$form = new Form($db);
//show tabs
// Show tabs


$text="Liste des biens ";
print_barre_liste($langs->trans("$text"), $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, "", $num);
 print '<div class="tabBar">';
print '<table width="auto">';
$head=gestimmo_prepare_head($user);
$titre=$langs->trans("Gestion immobiliere");
$picto='gestimmo';
dol_fiche_head($head, 'Mandats', $titre, 0, $picto);
//colonne gauche
print '<tr><td width=auto>';
print '<table class="noborder" width="400px">';
print '<tr class="liste_titre"><td colspan=4>'.$langs->trans("Biens a la location").'</td></tr>';

//print_fiche_titre($text);

$i = 0;
print '<table class="noborder" width="400px">';
print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans("Id"),$_SERVER['PHP_SELF'],"c.rowid","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("N°mandat"),$_SERVER['PHP_SELF'],"c.intitule","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Biens"),$_SERVER['PHP_SELF'],"c.bien","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Propio"),$_SERVER['PHP_SELF'],"c.proprio","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Date mandat"),$_SERVER['PHP_SELF'],"c.datec","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("type de bien"),$_SERVER['PHP_SELF'],"c.typebien","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("ref locataire"),$_SERVER['PHP_SELF'],"c.loc","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("date entre"),$_SERVER['PHP_SELF'],'' ,'&arch='.$arch,'',$sortfield,$sortorder);
print "</tr>\n";
   
// Put here content of your page
// Example 1 : Adding jquery code
echo '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_needroot();
	});
});
</script>';


// Example 2 : Adding jquery code
$somethingshown = $myobject->showLinkedObjectBlock();


// End of page
llxFooter();
$db->close();
