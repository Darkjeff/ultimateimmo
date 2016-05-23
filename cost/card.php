<?php
/* Copyright (C) 2013-2015 Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/cost/card.php
 * \ingroup immobilier
 * \brief   Card of cost
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
dol_include_once("/immobilier/class/immocost.class.php");
dol_include_once("/immobilier/class/local.class.php");
dol_include_once("/immobilier/class/loyer.class.php");
dol_include_once("/immobilier/class/immo_chargedet.class.php");
require_once ('../core/lib/immobilier.lib.php');
dol_include_once('/immobilier/class/html.immobilier.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// Langs
$langs->load("immobilier@immobilier");
$langs->load("compta");
$langs->load("other");

$mesg = '';
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);
$object = new Immocost($db);
$object->fetch($id);

$upload_dir = $conf->immobilier->dir_output . '/charge/' . dol_sanitizeFileName($object->id);
$modulepart = 'immobilier';

/*
 * 	Classify dispatch
 */
if (GETPOST ( "action" ) == 'dispatch') {
	$charge = new Charge ( $db );
	$charge->fetch ( $id );
	$result = $charge->set_dispatch ( $user );
	Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
}

if (GETPOST("sendit") && ! empty($conf->global->MAIN_UPLOAD_DOC)) {
	dol_add_file_process($upload_dir, 0, 1, 'userfile');
}

if (GETPOST("action") == 'add') {
	
	$dateacq = @dol_mktime($_POST["acqhour"], $_POST["acqmin"], $_POST["acqsec"], $_POST["acqmonth"], $_POST["acqday"], $_POST["acqyear"]);
	$datedu = @dol_mktime($_POST["duhour"], $_POST["dumin"], $_POST["dusec"], $_POST["dumonth"], $_POST["duday"], $_POST["duyear"]);
	$dateau = @dol_mktime($_POST["auhour"], $_POST["aumin"], $_POST["ausec"], $_POST["aumonth"], $_POST["auday"], $_POST["auyear"]);

	$charge = new Charge($db);

	$charge->local_id = GETPOST("local_id");
	$charge->libelle = GETPOST("libelle");

	$charge->local_id = GETPOST("local_id");
	$charge->type = GETPOST("type");
	$charge->montant_ttc = GETPOST("montant_ttc");
	$charge->date_acq = $dateacq;
	$charge->periode_du = $datedu;
	$charge->periode_au = $dateau;
	$charge->proprietaire_id = GETPOST("proprietaire_id");

	$res = $charge->create($user);
	if ($res == 0) {
	} else {
		if ($res == - 3) {
			$_error = 1;
			$action = "create";
		}
		if ($res == - 4) {
			$_error = 2;
			$action = "create";
		}
	}
	Header("Location: " . dol_buildpath('/immobilier/charge/document.php',1)."?id=" . $charge->id);
} 
elseif ($action == 'update')
{
	$error = 0;

	$dateacq = dol_mktime(0,0,0,GETPOST("acqmonth"), GETPOST("acqday"), GETPOST("acqyear"));
	$datedu = dol_mktime(0,0,0, GETPOST("dumonth"), GETPOST("duday"), GETPOST("duyear"));
	$dateau = dol_mktime(0,0,0, GETPOST("aumonth"), GETPOST("auday"), GETPOST("auyear"));

	$charge = new Charge($db);
	$charge->fetch($id);

	$charge->local_id = GETPOST("local_id");
	$charge->libelle = GETPOST("libelle");

	$charge->local_id = GETPOST("local_id");
	$charge->type = GETPOST("type");
	$charge->montant_ttc = GETPOST("montant_ttc");
	$charge->date_acq = $dateacq;
	$charge->periode_du = $datedu;
	$charge->periode_au = $dateau;
	$charge->proprietaire_id = GETPOST("proprietaire_id");
	$charge->commentaire = GETPOST("commentaire");

	$res = $charge->update($user);

	if ($res < 0) {
		setEventMessage($charge->error, 'errors');
	} else {
		setEventMessage($langs->trans("SocialContributionAdded"), 'mesgs');
	}

} elseif ($action == 'addrepart') {

	$chargedet = new Immo_chargedet($db);

	$chargedet->montant = GETPOST('montant', 'alpha');
	$chargedet->local_id = GETPOST('local_id', 'alpha');
	$chargedet->charge_id = $id;
	$chargedet->type = GETPOST('chargedet_type');

	$result = $chargedet->create($user);

	if ($result < 0) {
		setEventMessage($chargedet->error, 'errors');
	} else {
		//Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
	}
} elseif ($action == 'deleterepartline') {

	$id_line = GETPOST('idline');

	$chargedet = new Immo_chargedet($db);
	$result = $chargedet->fetch($id_line);
	if ($result < 0) {
		setEventMessage($relever->error, 'errors');
	}

	$result = $chargedet->delete($user);
	if ($result < 0) {
		setEventMessage($relever->error, 'errors');
	} else {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
	}
} elseif ($action=='delete') {
	//delete file
	$upload_dir = $conf->immobilier->dir_output;
	$file = $upload_dir . '/' . GETPOST ( "urlfile" ); // Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
	$ret = dol_delete_file ( $file );
	if ($ret)
		setEventMessage ( $langs->trans ( "FileWasRemoved", GETPOST ( 'urlfile' ) ) );
	else
		setEventMessage ( $langs->trans ( "ErrorFailToDeleteFile", GETPOST ( 'urlfile' ) ), 'errors' );
}
/*
 * View
 */

if ($action == 'create') {

	llxheader('', $langs->trans("addcharge"), '');

	print '<form name="add" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';

	dol_fiche_head('');

	print '<table class="border" width="100%">';
	print '<tr><td width="25%">'.$langs->trans("Label").'</td>';
	print '<td><input name="libelle" size="80" value="' . $charge->libelle . '"</td></tr>';

	print '<tr><td>'.$langs->trans("Montant_ttc").'</td>';
	print '<td><input name="montant_ttc" size="30" value="' . $charge->montant_ttc . '"</td></tr>';
	print '<tr><td>'.$langs->trans("Date").'</td>';
	print '<td align="left">';
	print $html->select_date(! empty($dateacq) ? $dateacq : '-1', 'acq', 0, 0, 0, 'fiche_charge', 1);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans("Building").'</td>';
	print '<td>';
	print $htmlimmo->select_propertie($charge->local_id, 'local_id');
	print '</td></tr>';
	print '<td>'.$langs->trans("Type").'</td>';
	print '<td>';
	print $htmlimmo->select_type($charge->type, 'type');
	print '</td></tr>';

	print '<tr><td>'.$langs->trans("DateStartPeriod").'</td>';
	print '<td align="left">';
	print $html->select_date(! empty($datedu) ? $datedu : '-1', 'du', 0, 0, 0, 'fiche_charge', 1);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans("DateEndPeriod").'</td>';
	print '<td align="left">';
	print $html->select_date(! empty($dateau) ? $dateau : '-1', 'au', 0, 0, 0, 'fiche_charge', 1);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans("Comment").'</td>';
	print '<td><input name="commentaire" size="120" value="' . $charge->commentaire . '"></td></tr>';

	print '</tbody>';
	print "</table>\n";

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'"></div>';

	print "</form>\n";
}

if ($id > 0) {

	llxheader('', $langs->trans("changecharge"), '');

	$charge = new Immocost($db);
	$result = $charge->fetch($id);
	$object = new Immocost($db);
	$object->fetch($id);

	$upload_dir = $conf->immobilier->dir_output . '/' . dol_sanitizeFileName($object->id);
	$modulepart = 'immobilier';

	$head = charge_prepare_head($charge);

	dol_fiche_head($head, 'fiche', $langs->trans("Charge"), 0, 'propertie');

	$nbligne = 0;

	print '<table>';

	print '<tr><td>';

	print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";

	//Card
	print '<table class="border" width="100%">';

	print '<tr>';
	print '<td width="25%">'.$langs->trans("Label").'</td>';
	print '<td><input name="libelle" size="30" value="' . $charge->libelle . '"</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("Building").'</td>';
	print '<td>';
	print $htmlimmo->select_propertie($charge->local_id, 'local_id');
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("Montant_ttc").'</td>';
	print '<td><input name="montant_ttc" size="30" value="' . $charge->montant_ttc . '"</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("Date").'</td>';
	print '<td align="left">';
	print $html->select_date($charge->date_acq, 'acq', 0, 0, 0, 'fiche_charge', 1);
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("DateStartPeriod").'</td>';
	print '<td align="left">';
	print $html->select_date($charge->periode_du, 'du', 0, 0, 0, 'fiche_charge', 1);
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("DateEndPeriod").'</td>';
	print '<td align="left">';
	print $html->select_date($charge->periode_au, 'au', 0, 0, 0, 'fiche_charge', 1);
	print '</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("Comment").'</td>';
	print '<td><input name="commentaire" size="80" value="' . $charge->commentaire . '"</td>';
	print '</tr>';

	print '<tr><td>'.$langs->trans("Status").'</td>';
	print '<td align="left" nowrap="nowrap">';
	print $charge->LibStatut ( $charge->dispatch, 5 );
	print "</td></tr>";

	print '<tr>';
	print '<td>&nbsp;</td>';
	print '<td><input type="submit" class="button" value="' . $langs->trans("Sauvegarder") . '"><input type="cancel" class="button" value="' . $langs->trans("Cancel") . '"></td>';
	print '</tr>';

	print '</table>';
	print '</form>';

	print '</td><td valign="top" width="100%">';

	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addrepart">';
	print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
	print '<table class="border" width="100%">';

	print '<tr  class="liste_titre">';
	print '<td>';
	print $langs->trans('localid');
	print '</td>';
	print '<td>';
	print $langs->trans('Type');
	print '</td>';
	print '<td>';
	print $langs->trans('Amount');
	print '</td>';
	print '<td>';
	print '&nbsp';
	print '</td>';

	print '<tr>';

	print '<td>';
	print $htmlimmo->select_propertie($chargedet->local_id, 'local_id');
	print '</td>';

	print '<td>';
	print '<input type="text" size="15" name="chargedet_type"/>';
	print '</td>';

	print '<td>';
	print '<input type="text" size="15" name="montant"/>';
	print '</td>';

	print '<td>';
	print '<input type="submit" value="' . $langs->trans('addrepart') . '" name="addrepart"/>';
	print '</td>';

	print '</tr>';

	print '</table>';

	print '</form>';

	/*
	 * Liste des repartition
	 */
	$sql = "SELECT icd.rowid, icd.local_id, icd.charge_id, icd.montant as amount, ll.nom as nomlocal";
	$sql .= " FROM " . MAIN_DB_PREFIX . "immo_chargedet as icd";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_local as ll ON icd.local_id = ll.rowid";
	$sql .= " WHERE icd.charge_id = " . $id;

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$i = 0;
		$total = 0;
		echo '<table class="border" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans("Local") . '</td>';
		print '<td>' . $langs->trans("Amount") . '</td>';
		print '<td>&nbsp;</td>';
		print '</tr>';

		$var = True;
		$totalpaye = 0;
		while ( $i < $num ) {
			$objp = $db->fetch_object($resql);
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			print "<td>" . $objp->nomlocal . "</td>\n";
			print '<td>' . $objp->type . '</td>';
			print '<td>' . price($objp->amount) . "</td>";
			print '<td>';
			print '<a href="' . $_SERVER['SELF'] . '?action=deleterepartline&idline=' . $objp->rowid . '&id=' . $id . '">DeleteLine</a>';
			print '</td>';
			print "</tr>";
			$totalpaye += $objp->amount;
			$i ++;
		}

		print '<tr><td colspan="2">' . $langs->trans("Total") . " :</td><td><b>" . price($totalpaye) . "</b></td><td>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";

		print "</table>";
		$db->free($resql);
	} else {
		dol_print_error($db);
	}
	print '</td></tr>';

	print '</table>';

	$form = new Form($db);

	// Construit liste des fichiers
	$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
	$totalsize = 0;
	foreach ( $filearray as $key => $file ) {
		// var_dump($file);
		// $file['level1name']='charge/';
		$totalsize += $file['size'];
	}

	$formfile = new FormFile($db);

	// List of document
	$formfile->list_of_documents($filearray, $object, $modulepart, $param);
	
	print "<div class=\"tabsAction\">\n";
	print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=dispatch&id=' . $id . '">' . $langs->trans ( 'ClassifyDispatch' ) . '</a>';
	print "</div>";
}

llxFooter();

$db->close();
