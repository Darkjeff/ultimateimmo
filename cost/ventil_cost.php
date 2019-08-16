<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2014      Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2018-2019 Philippe GRAND 		<philippe.grand@atoo-net.com>
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
 * \file htdocs/compta/ventilation/card.php
 * \ingroup compta
 * \brief Page fiche ventilation
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once("/ultimateimmo/class/immocost.class.php");
dol_include_once("/ultimateimmo/class/immoproperty.class.php");
dol_include_once("/ultimateimmo/class/immoreceipt.class.php");
dol_include_once("/ultimateimmo/class/immocost_detail.class.php");
dol_include_once('/ultimateimmo/class/immorent.class.php');
require_once ('../core/lib/ultimateimmo.lib.php');
dol_include_once('/ultimateimmo/class/html.ultimateimmo.php');

$res = dol_include_once("/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php");
if (! $res)
	die("Include of ultimateimmo");

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo","bills","compta"));

$mesg = '';
$id = GETPOST('id', 'int');
$action = GETPOST('action');

$html = new Form($db);
$htmlimmo = new FormUltimateimmo($db);
// Actions

/*
 * Add ventil charge
 */

if ($action == 'ventil') {
	$mesLignesCochees = GETPOST('mesCasesCochees');
	
	$cpt = 0;
	$error=0;
	
	$db->begin();
	
	foreach ( $mesLignesCochees as $maLigneCochee=>$localid ) {
		
		
		$ChargeDet = new Immo_costdet($db);
		
		// main info loyer
		$ChargeDet->amount = GETPOST('amount_'.$localid);
		$ChargeDet->cost_type = GETPOST('typecharge');
		$ChargeDet->fk_cost = $id;
		$ChargeDet->fk_property = $localid;
		
		$result = $ChargeDet->create($user);
		
		if ($result < 0) {
			setEventMessage($ChargeDet->error,'errors');
			$error++;
		}
		$cpt ++;
	}
	
	if (empty($error)) {
		$charge = new Charge($db);
		$result = $charge->fetch($id);
		if ($result < 0) {
			setEventMessage($charge->error, 'errors');
			$error++;
		}
		$charge->dispatch=1;
		$result = $charge->update($user);
		if ($result < 0) {
			setEventMessage($charge->error, 'errors');
			$error++;
		}
		
	}
	
	if (empty($error)) {
		$db->commit();
		setEventMessage($langs->trans("ReceiptPaymentsAdded"), 'mesgs');
		Header("Location: " . dol_buildpath('/ultimateimmo/cost/immocost_card.php',1)."?id=" . $id);
	} else {
		$db->rollback();
	}
}

/*
 * View
 *
 */

$form = new Form($db);
$loyer = new Immoreceipt($db);
llxheader('', $langs->trans("newventilcharge"), '');

$charge = new ImmoCost ($db);
$result = $charge->fetch($id);

if ($result < 0) {
	setEventMessage($charge->error, 'errors');
}

$local = new ImmoProperty($db);
$result = $local->fetch($charge->fk_property);

if ($result < 0) {
	setEventMessage($charge->error, 'errors');
}


if (! empty($local->id)) {
	$result = $local->fetchAllByBuilding();
	
	if ($result < 0) {
		setEventMessage($local->error, 'errors');
	}
}

$head = charge_prepare_head($charge);

dol_fiche_head($head, 'repartition', $langs->trans("Charge"), 0, 'propertie');

if ($id > 0) {
	
/*
 * List properties
 */
	
	$i = 0;
	$total = 0;
	print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="ventil">';
	
	
	print '<br><div class="fichecenter"><div class="underbanner clearboth"></div><table class="border tableforfield" width="100%"><tbody>';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans('fk_cost') . '</td>';
	print '<td>' . $langs->trans('fk_property') . '</td>';
	print '<td>' . $langs->trans('nomlocal') . '</td>';
	print '<td align="right">' . $langs->trans('amount') . '</td>';
	print '<td align="right">' . $langs->trans('select') . '</td>';
	print "</tr>\n";
	
	$i = 0;
	if (count($local->lines) > 0) 
	{
		$amount = 0;
		$loc_array = array ();
		// Calcul amount par appartement
		foreach ( $local->lines as $local_line ) {
			// Trouve les loyer existant sur cee lieu pour cette période
			$result = $loyer->fetchByLocalId($local_line->id, array (
					'insidedateloyer' => $charge->datec 
			));
			if ($result < 0) {
				setEventMessage($loyer->error, 'errors');
			}
			if (count($loyer->lines) > 0) {
				$loc_array[$local_line->id] = $local_line->id;
			}
		}
		
		if (count($loc_array) > 0) {
			$amount = $charge->amount / count($loc_array);
		}
		// Affichage
		foreach ( $local->lines as $local_line ) {
			
			print '<tr class="oddeven">';
			print '<td>' . $charge->id . '</td>';
			print '<td>' . $local_line->id . '</td>';
			print '<td>' . $local_line->name;
			if (array_key_exists($local_line->id, $loc_array)) {
				print ' ' . img_picto($langs->trans('Louer'), 'statut4');
			} else {
				print ' ' . img_picto($langs->trans('Vide'), 'statut0');
			}
			print '</td>';
			
			if (array_key_exists($local_line->id, $loc_array)) {
				print '<td  align="right"><input name="amount_'.$local_line->id.'" value="' . $amount . '" size="30"></td>';
			} else {
				print '<td  align="right"><input name="amount_'.$local_line->id.'" value="" size="30"></td>';
			}
			
			// Colonne choix appartement
			print '<td align="center">';
			
			print '<input type="checkbox" name="mesCasesCochees[]" value="' . $local_line->id .'"'. ((array_key_exists($local_line->id, $loc_array)) ? "checked" : "") . '/>';
			print '</td>';
			print '</tr>';
			
			$i ++;
		}
	}
	print '<tr><td colspan="5" align="center">'.$langs->trans('Type').'<input class="flat" type="text" value="" name="typecharge"></td></tr>';
	print '<tr><td colspan="5" align="center"><input class="button" type="submit" value="' . $langs->trans("AddRepartition") . '" name="addrepartition"></td></tr>';
	
	print "</tbody></table></div>";
	
	print '</form>';
}

llxFooter();

$db->close();
