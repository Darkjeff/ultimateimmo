<?php
/* Copyright (C) 2013-2016  Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2016       Alexandre Spangaro	 <aspangaro@zendsi.com>
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
 * \file 		immobilier/charge/fiche_chargedet.php
 * \ingroup 	Immobilier
 * \brief 		Page fiche ventilation
 */
// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once ( "/immobilier/class/paie.class.php" );
dol_include_once ( "/immobilier/class/loyer.class.php" );
dol_include_once ( "/immobilier/class/immo_chargedet.class.php" );
dol_include_once("/immobilier/class/charge.class.php");
dol_include_once('/immobilier/class/html.immobilier.php');

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action', 'alpha' );

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);

// Actions

if (GETPOST ( "action" ) == 'add') {

    $chargedet = new Immo_chargedet ( $db );

	
		
		$chargedet->montant = GETPOST ( 'montant', 'alpha' );
		$chargedet->local_id = GETPOST ( 'local_id', 'alpha' );
		$chargedet->charge_id = GETPOST ( 'charge_id', 'alpha' );
		
		
		$id = $chargedet->create ( $user );
		header ( "Location: " . DOL_URL_ROOT . "/custom/immobilier/charge/fiche_charge.php?id=" . $chargedet->charge_id );
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}


if ($action == 'update') {

		$chargedet = new Immo_chargedet ( $db );
	
		
		$result = $chargedet->fetch ( $id );
		
		$chargedet->montant = GETPOST ( 'montant', 'alpha' );
		$chargedet->local_id = GETPOST ( 'local_id', 'alpha' );
		$chargedet->charge_id = GETPOST ( 'charge_id', 'alpha' );
		
		$result = $chargedet->update ( $user );
		header ( "Location: " . DOL_URL_ROOT . "/custom/immobilier/charge/fiche_charge.php?id=" . $chargedet->charge_id );
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}


/*
 * View
 */

$form = new Form ( $db );

llxheader ( '', $langs->trans ( "newchargedet" ), '' );

if ($action == 'create') {
	$charge = new Charge ( $db );
	$result = $charge->fetch ( $id );
	
	print '<form name="add" action="' . $_SERVER["PHP_SELF"] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	

	print '<input type="hidden" name="charge_id" size="10" value="' . $id . '">';
	
	print '<tr><td width="25%">local</td>';
	print '<td>';
	print $htmlimmo->select_propertie($chargedet->local_id, 'local_id');
	print '</td></tr>';

	print '<tr><td>' . $langs->trans ( "Montant" ) . '</td>';
	print '<td><input name="montant" size="30" value="' . $chargedet->montant . '"</td></tr>';

	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
if (GETPOST ( "action" ) == 'update') {
	$chargedet = new Chargedet ( $db );
	$result = $chargedet->fetch ( $id );
	
	print '<form action="fiche_chargedet.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="25%">' . $langs->trans ( "local_id" ) . '</td>';
	print '<td><input name="commentaire" size="10" value="' . $chargedet->local_id . '"</td></tr>';

	
	print '<tr><td>' . $langs->trans ( "Montant" ) . '</td>';
	print '<td><input name="montant" size="30" value="' . $chargedet->montant . '"</td></tr>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

llxFooter();

$db->close();
