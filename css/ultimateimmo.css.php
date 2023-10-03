<?php
/* Copyright (C) 2018-2019 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/css/ultimateimmo.css.php
 * \ingroup ultimateimmo
 * \brief   CSS file for module Ultimateimmo.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC', '1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK', 1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL', 1);
if (! defined('NOLOGIN'))         define('NOLOGIN', 1);          // File must be accessed by logon page so without login
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML', 1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX', '1');

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/../main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// false or '' = keep cache instruction added by server
// 'public'  = remove cache instruction added by server and if no cache-control added later, a default cache delay (10800) will be added by PHP.

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && ! empty($_SESSION['dol_login']))
{
	$user->fetch('',$_SESSION['dol_login']);
	$user->getrights();
}*/


// Define css type
top_httphead('text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=10800, public, must-revalidate');
else header('Cache-Control: no-cache');

//$path='/cabinetmed';    // This value may be used in future for external module to overwrite theme
//$theme='cabinetmed';
$path='';    	// This value may be used in future for external module to overwrite theme
$theme='eldy';	// Value of theme
if (! empty($conf->global->MAIN_OVERWRITE_THEME_RES)) { $path='/'.$conf->global->MAIN_OVERWRITE_THEME_RES; $theme=$conf->global->MAIN_OVERWRITE_THEME_RES; }

// Define image path files
$dol_hide_topmenu=$conf->dol_hide_topmenu;
$dol_hide_leftmenu=$conf->dol_hide_leftmenu;
$dol_optimize_smallscreen=$conf->dol_optimize_smallscreen;
$dol_no_mouse_hover=$conf->dol_no_mouse_hover;
$dol_use_jmobile=$conf->dol_use_jmobile;
?>

legend {
	font-weight: normal;
	color: #442288;
}

div.mainmenu.properties {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immoproperty.png', 1) ?>);
}

div.mainmenu.immoowners {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immoowner.png', 1) ?>);
}

div.mainmenu.immorenters {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immorenter.png', 1) ?>);
}

div.mainmenu.immorents {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immorent.png', 1) ?>);
}

div.mainmenu.immoreceipts {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immoreceipt.png', 1) ?>);
}

div.mainmenu.rentalloads {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immocost.png', 1) ?>);
}

div.mainmenu.result {
	background-image: url(<?php echo dol_buildpath('/ultimateimmo/img/immoresult.png', 1) ?>);
}

.quatrevingtpercent, .inputsearch {
	width: 80%;
}

/* Card for dashboard */
.ultimateimmo-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	grid-gap: 30px;
	align-items: stretch;
	justify-items: center;
}

.ultimateimmo-card {
	width: 270px;
	min-height: 110px;
	background-color: #fff;
	border-radius: .25em;
	color: #fff;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px;
	padding: 0;
	display: flex;
	margin-right: 1%;
	margin-bottom: 1%;
}

.ultimateimmo-left-side {
	background-color: #FFBF69;
	border-top-left-radius: .25rem;
	border-bottom-left-radius: .25rem;
	width: 10%;
	display: flex;
	justify-content: center;
	align-items: center;
	text-align: center;
}

.ultimateimmo-left-side .icon {
	font-size: 50px;
}

.ultimateimmo-right-side {
	width: 75%;
}

.ultimateimmo-right-side .inner {
	padding: 1% 3% 1% 3%;
	color: #0c0c0c;
}

.ultimateimmo-right-side .line-info {
	padding: 1% 0 1% 0;
}

.ultimateimmo-right-side .line-info span, .color-span {
	padding: 1px 6px 1px 6px;
	color: white;
	border-radius: .25em;
}

.ultimateimmo-card-list {
	width: 550px;
	min-height: 110px;
	background-color: #fff;
	border-radius: .25em;
	color: #fff;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px;
	padding: 0;
	display: flex;
	margin-right: 1%;
	margin-bottom: 1%;
}
