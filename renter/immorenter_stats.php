<?php
/* Copyright (C) 2003-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe Grand       <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *  \file       htdocs/custom/ultimateimmo/renter/stats.php
 *  \ingroup    ultimateimmo
 *  \brief      Page of renter outcomes statistics
 */


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

// Classes
include_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
include_once DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/theme_vars.inc.php';
dol_include_once('/ultimateimmo/class/immorenterstats.class.php');
dol_include_once('/ultimateimmo/lib/immorenter.lib.php');

//$WIDTH = DolGraph::getDefaultGraphSizeForStats('width', 500);
//$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height', 200);

$userid = GETPOST('userid', 'int');
if ($userid < 0) $userid = 0;
$socid = GETPOST('socid', 'int');
if ($socid < 0) $socid = 0;

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
if ($user->societe_id > 0) $socid = $user->societe_id;
$result = restrictedArea($user, 'ultimateimmo', $id);

$year = strftime("%Y", time());
$startyear = $year - 2;
$endyear = $year;

$mode = GETPOST("mode") ? GETPOST("mode") : 'customer';
$codageccam = GETPOST('codageccam');
$typevisit = GETPOST('typevisit');

if (empty($conf->ultimateimmo->enabled)) accessforbidden();


/*
 * View
 */

$langs->load("ultimateimmo@ultimateimmo");

$form = new Form($db);

llxHeader();

$title = $langs->trans("Statistics");
$dir = $conf->ultimateimmo->dir_temp;

print load_fiche_titre($title, $mesg);

dol_mkdir($dir);

$morefilter = '';
$stats = new RenterStats($db, $socid, $mode, ($userid > 0 ? $userid : 0), $morefilter);

// Build graphic number of object
// $data = array(array('Lib',val1,val2,val3),...)
$data = $stats->getNbByMonthWithPrevYear($endyear, $startyear);
//var_dump($data);

$filenamenb = $dir . "/outcomesnbinyear-" . $year . ".png";
$fileurlnb = DOL_URL_ROOT . '/viewimage.php?modulepart=ultimateimmo_temp&amp;file=outcomesnbinyear-' . $year . '.png';

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
if (!$mesg) {
	$px1->SetData($data);
	$i = $startyear;
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px1->SetLegend($legend);
	$px1->SetMaxValue($px1->GetCeilMaxValue());
	//$px1->SetWidth($WIDTH);
	$px1->SetHeight('200');
	$px1->SetYLabel($langs->trans("NumberReceipt"));
	$px1->SetShading(3);
	$px1->SetHorizTickIncrement(1);
	$px1->mode = 'depth';
	$px1->SetTitle($langs->trans("NumberReceiptByMonth"));

	$px1->draw($filenamenb, $fileurlnb);
}

// Build graphic amount of object
$data = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);
//var_dump($data);
// $data = array(array('Lib',val1,val2,val3),...)

$filenameamount = $dir . "/outcomesamountinyear-" . $year . ".png";
$fileurlamount = DOL_URL_ROOT . '/viewimage.php?modulepart=ultimateimmo_temp&amp;file=outcomesamountinyear-' . $year . '.png';

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
if (!$mesg) {
	$px2->SetData($data);
	$i = $startyear;
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px2->SetLegend($legend);
	$px2->setShowLegend(2);
	$px2->SetMaxValue($px2->GetCeilMaxValue());
	$px2->SetMinValue(min(0, $px2->GetFloorMinValue()));
	$px2->setShowPercent(1);
   // $px2->SetType(array('pie'));
	$px2->SetHeight('200');
	$px2->SetYLabel($langs->trans("Amount"));
	$px2->SetShading(3);
	$px2->SetHorizTickIncrement(1);
	$px2->mode = 'depth';
	$px2->SetTitle($langs->trans("AmountByMonth"));

	$px2->draw($filenameamount, $fileurlamount);
}


print '<div class="fichecenter"><div class="fichethirdleft">';


// Show filter box
print '<form name="stats" method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td class="liste_titre" colspan="2">' . $langs->trans("Filter") . '</td></tr>';
/*print '<tr><td>'.$langs->trans("ThirdParty").'</td><td>';
if ($mode == 'customer') $filter='s.client in (1,2,3)';
if ($mode == 'supplier') $filter='s.fournisseur = 1';
print $form->select_company($socid,'socid',$filter,1,0,0,array(),0,'','style="width: 95%"');
print '</td></tr>';
*/
print '<tr><td>' . $langs->trans("User") . '</td><td>';
print $form->select_dolusers($userid, 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth300');
print '</td></tr>';

print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="' . $langs->trans("Refresh") . '"></td></tr>';
print '</table>';
print '</form>';

print '<br><br>';

// Show array
$data = $stats->getAllByYear();

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre" height="24">';
print '<td align="center">' . $langs->trans("Year") . '</td>';
print '<td align="right">' . $langs->trans("Number") . '</td>';
print '<td align="right">' . $langs->trans("AmountTotal") . '</td>';
print '<td align="right">' . $langs->trans("AmountAverage") . '</td>';
print '</tr>';

$oldyear = 0;
foreach ($data as $val) {
	$year = $val['year'];
	while ($year && $oldyear > $year + 1) {	// If we have empty year
		$oldyear--;
		print '<tr height="24">';
		print '<td align="center">' . $oldyear . '</td>';
		print '<td align="right">0</td>';
		print '<td align="right">0</td>';
		print '<td align="right">0</td>';
		print '</tr>';
	}
	print '<tr height="24">';
	print '<td align="center">';
	//print '<a href="month.php?year='.$year.'&amp;mode='.$mode.'">';
	print $year;
	//print '</a>';
	print '</td>';
	print '<td align="right">' . $val['nb'] . '</td>';
	print '<td align="right">' . price(price2num($val['total'], 'MT'), 1) . '</td>';
	print '<td align="right">' . price(price2num($val['avg'], 'MT'), 1) . '</td>';
	print '</tr>';
	$oldyear = $year;
}

print '</table>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


// Show graphs
print '<table class="border" width="100%"><tr valign="top"><td align="center">';
if ($mesg) {
	print $mesg;
} else {
	print $px1->show();
	print "<br>\n";
	print $px2->show();
}
print '</td></tr></table>';


print '</div></div></div>';
print '<div style="clear:both"></div>';


dol_fiche_end();

llxFooter();

$db->close();
