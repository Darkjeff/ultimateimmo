<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018-2022 Philippe GRAND       <philippe.grand@atoo-net.com>
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
 */

/**
 * \file htdocs/custom/ultimateimmo/fiche_locataire.php
 * \ingroup ultimateimmo
 * \brief Page fiche locataire
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

// Class
dol_include_once("/ultimateimmo/class/immorent.class.php");
dol_include_once('/ultimateimmo/lib/immorent.lib.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');

// Langs
$langs->load("ultimateimmo@ultimateimmo");

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

$mesg = '';

$limit = $conf->liste_limit;

/*
* loyer et paiement par contrat
*
*/

$wikihelp = 'EN:Module_Ultimateimmo_EN#Owners|FR:Module_Ultimateimmo_FR#Configuration_des_contrats_de_location';
llxheader('', $langs->trans("bilancontrat"), $wikihelp);

$object = new ImmoRent($db);
$result = $object->fetch($id);
$head = immorentPrepareHead($object);

print dol_get_fiche_head($head, 'bilan', $langs->trans("Bilan"), -1, 'agreement');

// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';

	// Ref renter
	$staticImmorenter = new ImmoRenter($db);
	$staticImmorenter->fetch($object->fk_renter);
	$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $staticImmorenter->ref, $object, $permissiontoadd, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $staticImmorenter->ref . ' - ' . $staticImmorenter->getFullName($langs), $object, $permissiontoadd, 'string', '', null, null, '', 1);
	// Thirdparty
	$company = new Societe($db);
	if ($object->fk_soc) {
		$result = $company->fetch($object->fk_soc);
	}
	// Project
	if (isModEnabled('projet')) {
		$langs->load("projects");
		$morehtmlref .= '<br>' . $langs->trans('Project') . ' ';
		if ($user->rights->ultimateimmo->creer) {
			if ($action != 'classify')
			//$morehtmlref.='<a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
			$morehtmlref .= ' : ';
			if ($action == 'classify') {
				//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
				$morehtmlref .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '">';
				$morehtmlref .= '<input type="hidden" name="action" value="classin">';
				$morehtmlref .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
				$morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
				$morehtmlref .= '<input type="submit" class="button valignmiddle" value="' . $langs->trans("Modify") . '">';
				$morehtmlref .= '</form>';
			} else {
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
			}
		} else {
			if (!empty($object->fk_project)) {
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				$morehtmlref .= '<a href="' . DOL_URL_ROOT . '/projet/card.php?id=' . $object->fk_project . '" title="' . $langs->trans('ShowProject') . '">';
				$morehtmlref .= $proj->ref;
				$morehtmlref .= '</a>';
			} else {
				$morehtmlref .= '';
			}
		}
	}
	$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . $company->getNomUrl(1, 'renter');
	if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $staticImmorenter->fk_soc > 0) $morehtmlref .= ' (<a href="' . dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1) . '?socid=' . $staticImmorenter->fk_soc . '&search_fk_soc=' . urlencode($staticImmorenter->fk_soc) . '">' . $langs->trans("OtherRents") . '</a>)';
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';

	print '</div>';

	print dol_get_fiche_end();

$sql = "(SELECT l.date_start as date , l.total_amount as debit, 0 as credit , l.label as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
$sql .= " WHERE l.fk_rent =" . $id;
$sql .= ")";
$sql .= "UNION (SELECT p.date_payment as date, 0 as debit , p.amount as credit, p.note_public as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
$sql .= " WHERE p.fk_rent =" . $id;
$sql .= ")";
$sql .= "ORDER BY date";

$result = $db->query($sql);
if ($result) {
	$num_lignes = $db->num_rows($result);
	$i = 0;

	print '<table class="border" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("date") . '</td>';
	print '<td>' . $langs->trans("debit") . '</td>';
	print '<td>' . $langs->trans("credit") . '</td>';
	print '<td>' . $langs->trans("description") . '</td>';
	print "</tr>\n";

	$var = True;
	while ($i < min($num_lignes, $limit)) {

		$objp = $db->fetch_object($result);
		$var = !$var;
		print "<tr $bc[$var]>";

		print '<td>' . dol_print_date($db->jdate($objp->date), 'day') . '</td>';
		print '<td>' . $objp->debit . '</td>';
		print '<td>' . $objp->credit . '</td>';
		print '<td>' . $objp->des . '</td>';

		print "</tr>";
		$i++;
	}
	print '</table>';
} else {
	print $db->error();
}

llxFooter();

$db->close();
