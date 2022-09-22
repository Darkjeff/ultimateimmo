<?php
/*
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
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

 if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disables token renewal
 if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
 if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
 if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
 if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) { $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) { $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) { $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) { $res=@include "../main.inc.php";
}
if (! $res && file_exists("../../main.inc.php")) { $res=@include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) { $res=@include "../../../main.inc.php";
}
if (! $res) { die("Include of main fails");
}

dol_include_once('ultimateimmo/class/ultimateimmo_infobox.class.php');



 $boxid=GETPOST('boxid', 'int');
 $boxorder=GETPOST('boxorder');
 $userid=GETPOST('userid');
 $zone=GETPOST('zone', 'int');
 $userid=GETPOST('userid', 'int');


 /*
  * View
  */

 top_httphead();

 print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

 // Add a box
if ($boxid > 0 && $zone !='' && $userid > 0)
{
	$tmp=explode('-', $boxorder);
	$nbboxonleft=substr_count($tmp[0], ',');
	$nbboxonright=substr_count($tmp[1], ',');
	print $nbboxonleft.'-'.$nbboxonright;
	if ($nbboxonleft > $nbboxonright) $boxorder=preg_replace('/B:/', 'B:'.$boxid.',', $boxorder);    // Insert id of new box into list
	else $boxorder=preg_replace('/^A:/', 'A:'.$boxid.',', $boxorder);    // Insert id of new box into list
}

 // Registering the location of boxes after a move
if ($boxorder && $zone != '' &&  $userid > 0)
{
	// boxorder value is the target order: "A:idboxA1,idboxA2,A-B:idboxB1,idboxB2,B"
	dol_syslog("AjaxBox boxorder=".$boxorder." zone=".$zone." userid=".$userid, LOG_DEBUG);

	$result=UltimateImmoInfoBox::InfoBox::saveboxorder($db, $zone, $boxorder, $userid);
	if ($result > 0)
	{
		$langs->load("boxes");
		if (! GETPOST('closing'))
		{
			setEventMessages($langs->trans("BoxAdded"), null);
		}
	}
}
