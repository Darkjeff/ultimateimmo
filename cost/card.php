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
dol_include_once("/immobilier/class/immoproperty.class.php");
dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/class/immo_costdet.class.php");
require_once (DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
require_once ('../core/lib/immobilier.lib.php');
dol_include_once('/immobilier/class/html.formimmobilier.class.php');
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
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel');

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);
$object = new Immocost($db);
$object->fetch($id);



/*
 * 	Classify dispatch
 */
if (GETPOST ( "action" ) == 'dispatch') {
	$charge = new Immocost ( $db );
	$charge->fetch ( $id );
	$result = $charge->set_dispatch ( $user );
	Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
}


/*
 *	Delete cost
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	//echo "hhhhhhhhhh";
	$object = new Immocost($db);
	$object->fetch($id);
	$result = $object->delete($user);

	if ($result > 0) {
		Header("Location: list.php");
		exit();
	} else {
		setEventMessages(null,$object->errors, 'errors');
	}
}

if (GETPOST("sendit") && ! empty($conf->global->MAIN_UPLOAD_DOC)) {
	dol_add_file_process($upload_dir, 0, 1, 'userfile');
}

if (GETPOST("action") == 'add') {
	
	$dateacq = @dol_mktime($_POST["acqhour"], $_POST["acqmin"], $_POST["acqsec"], $_POST["acqmonth"], $_POST["acqday"], $_POST["acqyear"]);
	$datedu = @dol_mktime($_POST["duhour"], $_POST["dumin"], $_POST["dusec"], $_POST["dumonth"], $_POST["duday"], $_POST["duyear"]);
	$dateau = @dol_mktime($_POST["auhour"], $_POST["aumin"], $_POST["ausec"], $_POST["aumonth"], $_POST["auday"], $_POST["auyear"]);

	$charge = new Immocost($db);

	$charge->fk_property = GETPOST("fk_property");
	$charge->label = GETPOST("label");
	$charge->socid = GETPOST("societe");
	$charge->fk_property = GETPOST("fk_property");
	$charge->type = GETPOST("type");
	$charge->amount = GETPOST("amount");
	$charge->date = $dateacq;
	$charge->date_start = $datedu;
	$charge->date_end = $dateau;
	$charge->fk_owner = GETPOST("fk_owner");

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
	Header("Location: " . dol_buildpath('/immobilier/cost/document.php',1)."?id=" . $charge->id);
} 
elseif ($action == 'update')
{
	$error = 0;

	$dateacq = dol_mktime(0,0,0,GETPOST("acqmonth"), GETPOST("acqday"), GETPOST("acqyear"));
	$datedu = dol_mktime(0,0,0, GETPOST("dumonth"), GETPOST("duday"), GETPOST("duyear"));
	$dateau = dol_mktime(0,0,0, GETPOST("aumonth"), GETPOST("auday"), GETPOST("auyear"));

	$charge = new Immocost($db);
	$charge->fetch($id);

	$charge->fk_property = GETPOST("fk_property");
	$charge->label = GETPOST("label");
	$charge->fk_property = GETPOST("fk_property");
	$charge->type = GETPOST("type");
	$charge->amount = GETPOST("amount");
	$charge->date = $dateacq;
	$charge->date_start = $datedu;
	$charge->date_end = $dateau;
	$charge->socid = GETPOST("societe");
	$charge->fk_owner = GETPOST("fk_owner");
	$charge->commentaire = GETPOST("commentaire");

	$res = $charge->update($user);

	if ($res < 0) {
		setEventMessage($charge->error, 'errors');
	} else {
		setEventMessage($langs->trans("SocialContributionAdded"), 'mesgs');
	}

} elseif ($action == 'addrepart') {

	$chargedet = new Immo_chargedet($db);

	$chargedet->amount = GETPOST('amount', 'alpha');
	$chargedet->fk_property = GETPOST('fk_property', 'alpha');
	$chargedet->fk_cost = $id;
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
} 

/*elseif ($action=='delete') {
	//delete file
	$upload_dir = $conf->immobilier->dir_output;
	$file = $upload_dir . '/' . GETPOST ( "urlfile" ); // Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
	$ret = dol_delete_file ( $file );
	if ($ret)
		setEventMessage ( $langs->trans ( "FileWasRemoved", GETPOST ( 'urlfile' ) ) );
	else
		setEventMessage ( $langs->trans ( "ErrorFailToDeleteFile", GETPOST ( 'urlfile' ) ), 'errors' );
}*/

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
	print '<td><input name="label" size="80" value="' . $charge->label . '"</td></tr>';
	
	
	
	
	
	print '<tr class="select_thirdparty_block"><td class="fieldrequired">' . $langs->trans("Company") . '</td><td colspan="3">';
	print $form->select_company(GETPOST('societe', 'int'), 'societe', '(s.client IN (1,3,2))', 1, 1);
	print '</td></tr>';


	print '<tr><td>'.$langs->trans("amount").'</td>';
	print '<td><input name="amount" size="30" value="' . $charge->amount . '"</td></tr>';
	print '<tr><td>'.$langs->trans("Date").'</td>';
	print '<td align="left">';
	print $html->select_date(! empty($dateacq) ? $dateacq : '-1', 'acq', 0, 0, 0, 'fiche_charge', 1);
	print '</td></tr>';
	print '<tr><td>'.$langs->trans("Building").'</td>';
	print '<td>';
	print $htmlimmo->select_property($charge->fk_property, 'fk_property');
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
	
	if($action== "edit"){
				llxheader('', $langs->trans("Charge"), '');

		$charge = new Immocost($db);
		$result = $charge->fetch($id);
		$object = new Immocost($db);
		$object->fetch($id);

		$upload_dir = $conf->immobilier->dir_output . '/' . dol_sanitizeFileName($object->id);
		$modulepart = 'immobilier';

		$head = charge_prepare_head($charge);

		dol_fiche_head($head, 'fiche', $langs->trans("Charge"), 0, 'propertie');

		$nbligne = 0;
		
		//Card
		
		print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
		
		print '<div class="fichecenter"><div class="fichehalfleft"><div class="underbanner clearboth"></div><table class="border tableforfield" width="100%"><tbody>';

		print '<td width="25%">'.$langs->trans("Label").'</td>';
		print '<td><input name="label" size="30" value="' . $charge->label . '"</td>';
		print '</tr>';

		

		// Tier
			print '<tr>';
			print '<td>'.fieldLabel('societe','socid',1).'</td>';
			print '<td>';
			print $form->select_thirdparty_list($charge->socid,'socid');
			print '</td>';
			print '</tr>';


		print '<tr>';
		print '<td>'.$langs->trans("Building").'</td>';
		print '<td>';
		print $htmlimmo->select_property($charge->fk_property, 'fk_property');
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("amount").'</td>';
		print '<td><input name="amount" size="30" value="' . $charge->amount . '"</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date, 'acq', 0, 0, 0, 'fiche_charge', 1);
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateStartPeriod").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date_start, 'du', 0, 0, 0, 'fiche_charge', 1);
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateEndPeriod").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date_end, 'au', 0, 0, 0, 'fiche_charge', 1);
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("Comment").'</td>';
		print '<td><textarea name="commentaire">' . $charge->commentaire . '</textarea></td>';
		print '</tr>';

		print '<tr><td>'.$langs->trans("Status").'</td>';
		print '<td align="left" nowrap="nowrap">';
		print $charge->LibStatut ( $charge->dispatch, 5 );
		print "</td></tr>";

		print '<tr>';
		print '<td>&nbsp;</td>';
		print '<td><input type="submit" class="button" value="' . $langs->trans("Sauvegarder") . '"><input type="cancel" class="button" value="' . $langs->trans("Cancel") . '"></td>';
		print '</tr>';


		print '</tbody></table></form>';
		
				/*
		 * Barre d'actions
		 */

		print '<div class="tabsAction">';

		if ($action != 'create' && $action != 'edit' && $action != 'nfcontact') {
			if ($user->rights->immobilier->renter->write) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('Modify') . '</a>';
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
			}
			if ($user->rights->immobilier->renter->delete) {
				print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $id . '">' . $langs->trans('Delete') . '</a>';
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Delete') . '</a>';
			}
		}

		print '</div>';

		
		
		print '</div><div class="fichehalfright"><div class="ficheaddleft"><div class="underbanner clearboth"></div>';		

		
		/*
		 * Liste des repartition
		 */
		
		$sql = "SELECT icd.rowid, icd.fk_property, icd.fk_cost, icd.amount, ll.name as nomlocal";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost_det as icd";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON icd.fk_property = ll.rowid";
		$sql .= " WHERE icd.fk_cost = " . $id;

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			$total = 0;
			echo '<table class="border tableforfield" width="100%"><tbody>';
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans("Local") . '</td>';
			print '<td>' . $langs->trans("Amount") . '</td>';
			print '<td>' . $langs->trans("Action") . '</td>';
			print '</tr>';

			$var = True;
			$totalpaye = 0;
			while ( $i < $num ) {
				$objp = $db->fetch_object($resql);
				$var = ! $var;
				print "<tr " . $bc[$var] . ">";
				print "<td>" . $objp->nomlocal . "</td>\n";
				print '<td>' . price($objp->amount) . "</td>";
				print '<td class=" center">';
				print '<a href="costdet.php?action=edit&id='. $objp->rowid .'">' . img_edit() . '</a><a class="delete" href="costdet.php?action=confirm_delete&confirm=yes&id=' . $objp->rowid . '&idcost='.$id.'">' . img_delete() . '</a>';
				print '</td>';
				print "</tr>";
				$totalpaye += $objp->amount;
				$i ++;
			}

			print '<tr><td>' . $langs->trans("Total") . " :</td><td><b>" . price($totalpaye) . "</b>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";

			print "<tbody></table></div></div></div>";
			$db->free($resql);
		} else {
			dol_print_error($db);
		}
		
		

		
		print "<div class='clearboth'></div>";

		/*print '<div class="fichecenter"><div class="fichehalfleft"><div class="underbanner clearboth"></div><table class="border tableforfield" width="100%"><tbody>';

		print '<tr>';

		print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";

		
		print '<td width="25%">'.$langs->trans("Label").'</td>';
		print '<td><input name="label" size="30" value="' . $charge->label . '"</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("supplier").'</td>';
		print '<td>';
		print $htmlimmo->select_supplier($charge->supplier, 'supplier', 1);
		print '</td>';
		print '</tr>';

		print '<tr><td valign="top">' . $langs->trans("Company") . '</td><td>';
						if ($charge->socid) {
							print '<a href="' . dol_buildpath('/comm/card.php', 1) . '?socid=' . $charge->socid . '">';
							print '<input type="hidden" name="societe" value="' . $charge->socid . '">';
							print img_object($langs->trans("ShowCompany"), "company") . ' ' . dol_trunc($charge->socname, 20) . '</a>';
						} else {
							print '&nbsp;';
							print '<input type="hidden" name="societe" value="">';
						}
						print '</td></tr>';


		print '<tr>';
		print '<td>'.$langs->trans("Building").'</td>';
		print '<td>';
		print $htmlimmo->select_property($charge->fk_property, 'fk_property');
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("amount").'</td>';
		print '<td><input name="amount" size="30" value="' . $charge->amount . '"</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date, 'acq', 0, 0, 0, 'fiche_charge', 1);
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateStartPeriod").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date_start, 'du', 0, 0, 0, 'fiche_charge', 1);
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateEndPeriod").'</td>';
		print '<td align="left">';
		print $html->select_date($charge->date_end, 'au', 0, 0, 0, 'fiche_charge', 1);
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

		print '</form>';
		print '</tbody></table>';
		
		print '</div><div class="fichehalfright"><div class="ficheaddleft"><div class="underbanner clearboth"></div>';

		

		print '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="addrepart">';
		print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
		echo '<table class="border tableforfield" width="100%"><tbody>';

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
		print $htmlimmo->select_property($chargedet->fk_property, 'fk_property');
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

		print '<tbody></table></div></div></div>';

		print '</form>';*/
		
	}else{
		
		llxheader('', $langs->trans("Charge"), '');

		$charge = new Immocost($db);
		$result = $charge->fetch($id);
		$object = new Immocost($db);
		$object->fetch($id);

		$upload_dir = $conf->immobilier->dir_output . '/' . dol_sanitizeFileName($object->id);
		$modulepart = 'immobilier';

		$head = charge_prepare_head($charge);

		dol_fiche_head($head, 'fiche', $langs->trans("Charge"), 0, 'propertie');

		$nbligne = 0;
		
		//Card
		print '<div class="fichecenter"><div class="fichehalfleft"><div class="underbanner clearboth"></div><table class="border tableforfield" width="100%"><tbody>';

		print '<tr>';
		print '<td width="25%">'.$langs->trans("Label").'</td>';
		print '<td>' . $charge->label . '</td>';
		print '</tr>';

		
		
		$thirdparty_static = new Societe($db);
		$thirdparty_static->id=$charge->soc_id;
		$thirdparty_static->name= $charge->socname;
		
		print '<tr>';
		print '<td>'.$langs->trans("Company").'</td>';
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
		print '</tr>';
		
		$propertystatic=new Immoproperty($db);
		$propertystatic->id = $charge->property_id;
		$propertystatic->name = $charge->nomlocal;
		

		print '<tr>';
		print '<td>'.$langs->trans("Building").'</td>';
		print '<td>'.$propertystatic->getNomUrl(1).'</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("amount").'</td>';
		print '<td>' . $charge->amount . '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td align="left">';
		print dol_print_date($charge->date, 'day');
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateStartPeriod").'</td>';
		print '<td align="left">';
		print dol_print_date($charge->date_start, 'day');
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("DateEndPeriod").'</td>';
		print '<td align="left">';
		print dol_print_date($charge->date_end, 'day');
		print '</td>';
		print '</tr>';

		print '<tr>';
		print '<td>'.$langs->trans("Comment").'</td>';
		print '<td>' . $charge->commentaire . '</td>';
		print '</tr>';

		print '<tr><td>'.$langs->trans("Status").'</td>';
		print '<td align="left" nowrap="nowrap">';
		print $charge->LibStatut ( $charge->dispatch, 5 );
		print "</td></tr>";

		print '</tbody></table>';
		
				/*
		 * Barre d'actions
		 */

		print '<div class="tabsAction">';

		if ($action != 'create' && $action != 'edit' && $action != 'nfcontact') {
			if ($user->rights->immobilier->renter->write) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('Modify') . '</a>';
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
			}
			if ($user->rights->immobilier->renter->delete) {
				print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $id . '">' . $langs->trans('Delete') . '</a>';
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Delete') . '</a>';
			}
		}

		print '</div>';

		
		
		print '</div><div class="fichehalfright"><div class="ficheaddleft"><div class="underbanner clearboth"></div>';		

		
		/*
		 * Liste des repartition
		 */
		
		$sql = "SELECT icd.rowid, icd.fk_property, icd.fk_cost, icd.amount, ll.name as nomlocal";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost_det as icd";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON icd.fk_property = ll.rowid";
		$sql .= " WHERE icd.fk_cost = " . $id;

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			$total = 0;
			echo '<table class="border tableforfield" width="100%"><tbody>';
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans("Local") . '</td>';
			print '<td>' . $langs->trans("Amount") . '</td>';
			print '<td>' . $langs->trans("Action") . '</td>';
			print '</tr>';

			$var = True;
			$totalpaye = 0;
			while ( $i < $num ) {
				$objp = $db->fetch_object($resql);
				$var = ! $var;
				print "<tr " . $bc[$var] . ">";
				print "<td>" . $objp->nomlocal . "</td>\n";
				print '<td>' . price($objp->amount) . "</td>";
				print '<td class=" center">';
				print '<a href="costdet.php?action=edit&id='. $objp->rowid .'">' . img_edit() . '</a><a class="delete" href="costdet.php?action=confirm_delete&confirm=yes&id=' . $objp->rowid . '&idcost='.$id.'">' . img_delete() . '</a>';
				print '</td>';
				print "</tr>";
				$totalpaye += $objp->amount;
				$i ++;
			}

			print '<tr><td>' . $langs->trans("Total") . " :</td><td><b>" . price($totalpaye) . "</b>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";

			print "<tbody></table></div></div></div>";
			$db->free($resql);
		} else {
			dol_print_error($db);
		}
		
		


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
}

llxFooter();
$db->close();