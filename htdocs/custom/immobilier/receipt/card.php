<?php
/* Copyright (C) 2013-2015	Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2017	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2016		Jamelbaz			<jamelbaz@gmail.com>
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
 * \file 		immobilier/receipt/card.php
 * \ingroup 	immobilier
 * \brief 		Receipt page
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
dol_include_once("/immobilier/core/lib/immobilier.lib.php");
dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/core/modules/immobilier/modules_immobilier.php");
dol_include_once("/immobilier/class/immorent.class.php");

// Langs
$langs->load("immobilier@immobilier");
$langs->load("compta");
$langs->load("bills");

$mesg = '';
$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');
$action = GETPOST('action','alpha');
$cancel = GETPOST('cancel','alpha');

$object = new Immoreceipt($db);

// Actions

/*
 * 	Classify paid
 */
if ($action == 'paid') {
	$receipt = new Immoreceipt($db);
	$receipt->fetch($id);
	$result = $receipt->set_paid($user);
	Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
}

/*
 *	Delete rental
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	$receipt = new Immoreceipt($db);
	$receipt->fetch($id);
	$result = $receipt->delete($user);
	if ($result > 0) {
		header("Location: list.php");
		exit();
	} else {
		$mesg = '<div class="error">' . $receipt->error . '</div>';
	}
}

/*
 * Action generate quitance
 */
if ($action == 'quittance') {
	// Define output language
	$outputlangs = $langs;
	
	$file = 'quittance_' . $id . '.pdf';
	
	$result = immobilier_pdf_create($db, $id, '', 'quittance', $outputlangs, $file);
	
	if ($result > 0) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action generate charge locative
 */
if ($action == 'chargeloc') {
	// Define output language
	$outputlangs = $langs;
	
	$file = 'chargeloc_' . $id . '.pdf';
	
	$result = immobilier_pdf_create($db, $id, '', 'chargeloc', $outputlangs, $file);
	
	if ($result > 0) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Add rental
 */
if ($action == 'add' && ! $cancel) {
	$error = 0;
	
	$datev = dol_mktime(12, 0, 0, GETPOST("datevmonth"), GETPOST("datevday"), GETPOST("datevyear"));
	$datesp = dol_mktime(12, 0, 0, GETPOST("datespmonth"), GETPOST("datespday"), GETPOST("datespyear"));
	$dateep = dol_mktime(12, 0, 0, GETPOST("dateepmonth"), GETPOST("dateepday"), GETPOST("dateepyear"));
	
	$object->nom = GETPOST("nom");
	$object->datesp = $datesp;
	$object->dateep = $dateep;
	$object->datev = $datev;
	
	if (empty($datev) || empty($datesp) || empty($dateep)) {
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Date")), 'errors');
		$error ++;
	}
	
	if (! $error) {
		$db->begin();
		
		$ret = $object->create($user);
		if ($ret > 0) {
			$db->commit();
			header("Location: index.php");
			exit();
		} else {
			$db->rollback();
			setEventMessages($object->error, $object->errors, 'errors');
			$action = "create";
		}
	}
	
	$action = 'create';
}

/*
 * Add all rental
 */

if ($action == 'addall') 
{
	
	$error=0;
	$dateech = dol_mktime(12,0,0, GETPOST("echmonth"), GETPOST("echday"), GETPOST("echyear"));
	$dateperiod = dol_mktime(12,0,0, GETPOST("periodmonth"), GETPOST("periodday"), GETPOST("periodyear"));
	$dateperiodend = dol_mktime(12,0,0, GETPOST("periodendmonth"), GETPOST("periodendday"), GETPOST("periodendyear"));
	
	if (empty($dateech)) {
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("DateDue")), 'errors');
		$action = 'create';
	} elseif (empty($dateperiod)) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Period")) . '</div>';
		$action = 'create';
	} elseif (empty($dateperiodend)) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Periodend")) . '</div>';
		$action = 'create';
	} else {
		
		$mesLignesCochees = GETPOST('mesCasesCochees');
		
		foreach ( $mesLignesCochees as $maLigneCochee ) {
			
			$receipt = new Immoreceipt($db);
			
			$maLigneCourante = split("_", $maLigneCochee);
			$monId = $maLigneCourante[0];
			$monLocal = $maLigneCourante[1];
			$monLocataire = $maLigneCourante[2];
			$monMontant = $maLigneCourante[3];
			$monLoyer = $maLigneCourante[4];
			$monCharges = $maLigneCourante[5];
			$monTVA = $maLigneCourante[7];
			$monOwner = $maLigneCourante[6];
			
			// main info loyer
			$receipt->name = GETPOST('name', 'alpha');
			$receipt->echeance = $dateech;
			$receipt->date_start = $dateperiod;
			$receipt->date_end = $dateperiodend;
			
			// main info contrat
			$receipt->fk_contract = $monId;
			$receipt->fk_property = $monLocal;
			$receipt->fk_renter = $monLocataire;
			$receipt->fk_owner = $monOwner;
			If ($monTVA == Oui) {
			$receipt->amount_total = $monMontant * 1.2;
			$receipt->vat = $monMontant * 0.2;}
			Else {
			$receipt->amount_total = $monMontant;}
			
			$receipt->rent = $monLoyer;
			$receipt->charges = $monCharges;
			$receipt->statut=0;
			$receipt->paye=0;
			
			$result = $receipt->create($user);
			if ($result < 0) {
				$error++;
				setEventMessages(null,$receipt->errors, 'errors');
				$action='createall';
			}
		}
	}
	
	if (empty($error)) {
		setEventMessage($langs->trans("SocialContributionAdded"), 'mesgs');
		Header("Location: " . dol_buildpath('/immobilier/receipt/list.php',1));
		exit();
	}
}

/*
 * Edit Receipt
 */

if ($action == 'update')
{
	$dateech = @dol_mktime(12,0,0, GETPOST("echmonth"), GETPOST("echday"), GETPOST("echyear"));
	$dateperiod = @dol_mktime(12,0,0, GETPOST("periodmonth"), GETPOST("periodday"), GETPOST("periodyear"));
	$dateperiodend = @dol_mktime(12,0,0, GETPOST("periodendmonth"), GETPOST("periodendday"), GETPOST("periodendyear"));
	/*if (! $dateech)
	 {
	 $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("DateDue")).'</div>';
	 $action = 'update';
	 }
	 elseif (! $dateperiod)
	 {
	 $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Period")).'</div>';
	 $action = 'update';
	 }
	 elseif (! $dateperiodend)
	 {
	 $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Periodend")).'</div>';
	 $action = 'update';
	 }
	 else
	 {
	 */
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($id);
	
	$receipt->nom 			= GETPOST('nom');
	If ($receipt->addtva != 0) {
	$receipt->amount_total 	= ($_POST["rent"] + $_POST["charges"])*1.2;}
	Else {
	$receipt->amount_total 	= $_POST["rent"] + $_POST["charges"];}
	$receipt->rent 			= $_POST["rent"];
	$receipt->charges 		= $_POST["charges"];
	If ($receipt->addtva != 0) {
	$receipt->vat 			= ($_POST["rent"]+$_POST["charges"])*0.2;}
	Else {
	$receipt->vat 			= 0;}
	
	$receipt->echeance 		= $dateech;
	$receipt->commentaire 	= $_POST["commentaire"];
	$receipt->statut 		= $_POST["statut"];
	$receipt->date_start 	= $dateperiod;
	$receipt->date_end 		= $dateperiodend;
	
	$result = $receipt->update($user);
	header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $receipt->id);
	if ($id > 0) {
		// $mesg='<div class="ok">'.$langs->trans("SocialContributionAdded").'</div>';
	} else {
		$mesg = '<div class="error">' . $receipt->error . '</div>';
	}
	// }
}

/*
 * View
 */

$form = new Form($db);

if ($action == 'create') {
	llxheader('', $langs->trans("Addnewrent"), '');
	
	$year_current = strftime("%Y", dol_now());
	$pastmonth = strftime("%m", dol_now());
	$pastmonthyear = $year_current;
	if ($pastmonth == 0) {
		$pastmonth = 12;
		$pastmonthyear --;
	}
	
	$datesp = dol_mktime(0, 0, 0, $datespmonth, $datespday, $datespyear);
	$dateep = dol_mktime(23, 59, 59, $dateepmonth, $dateepday, $dateepyear);
	
	if (empty($datesp) || empty($dateep)) // We define date_start and date_end
	{
		$datesp = dol_get_first_day($pastmonthyear, $pastmonth, false);
		$dateep = dol_get_last_day($pastmonthyear, $pastmonth, false);
	}
	
	print '<form name="fiche_loyer" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print load_fiche_titre($langs->trans("NewReceipt"), '', 'title_accountancy.png');
	
	dol_fiche_head('', '');
	
	print '<table class="border" width="100%">';
	
	print "<tr>";
	print '<td class="fieldrequired"><label for="rent">' . $langs->trans("Rent") . '</label></td><td>';
	print '<input name="rent" id="rent" size="30" value="' . $object->nom . '"</td>';
	print '</td></tr>';
	
	print '<tr><td><label for="datev">' . $langs->trans("DateValue") . '</label></td><td>';
	print $form->select_date((empty($datev) ? - 1 : $datev), "datev", '', '', '', 'add', 1, 1);
	print '</td></tr>';
	
	print "<tr>";
	print '<td class="fieldrequired"><label for="datesp">' . $langs->trans("DateStartPeriod") . '</label></td><td>';
	print $form->select_date($datesp, "datesp", '', '', '', 'add');
	print '</td></tr>';
	
	print '<tr><td class="fieldrequired"><label for="dateep">' . $langs->trans("DateEndPeriod") . '</label></td><td>';
	print $form->select_date($dateep, "dateep", '', '', '', 'add');
	print '</td></tr>';
	
	print '</table>';
	
	dol_fiche_end();
	
	print '<div class="center">';
	print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
}

/* *************************************************************************** */
/*                                                                             */
/* Mode add all contract                                                       */
/*                                                                             */
/* *************************************************************************** */

elseif ($action == 'createall') 
{
		llxheader('', $langs->trans("newrental"), '');
		print '<form name="fiche_loyer" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="addall">';
		
		print '<table class="border" width="100%">';
		
		print "<tr class=\"liste_titre\">";
		
		print '<td align="left">';
		print $langs->trans("NomLoyer");
		print '</td><td align="center">';
		print $langs->trans("Echeance");
		print '</td><td align="center">';
		print $langs->trans("Periode_du");
		print '</td><td align="center">';
		print $langs->trans("Periode_au");
		print '</td><td align="left">';
		print '&nbsp;';
		print '</td>';
		print "</tr>\n";
		
		print '<tr ' . $bc[$var] . ' valign="top">';
		
		/*
		 * Nom du loyer
		 */
		print '<td><input name="name" size="30" value="' . GETPOST('name') . '"</td>';
		
		// Due date
		print '<td align="center">';
		print $form->select_date(! empty($dateech) ? $dateech : '-1', 'ech', 0, 0, 0, 'fiche_loyer', 1);
		print '</td>';
		print '<td align="center">';
		print $form->select_date(! empty($dateperiod) ? $dateperiod : '-1', 'period', 0, 0, 0, 'fiche_loyer', 1);
		print '</td>';
		print '<td align="center">';
		print $form->select_date(! empty($dateperiodend) ? $dateperiodend : '-1', 'periodend', 0, 0, 0, 'fiche_loyer', 1);
		print '</td>';
		
		print '<td align="center"><input type="submit" class="button" value="' . $langs->trans("Add") . '"></td></tr>';
		
		print '</table>';
		
		/*
		 * List agreement
		 */
		$sql = "SELECT c.rowid as reference, loc.nom as nom, l.address  , l.name as local, loc.statut as statut, c.montant_tot as total,";
		$sql .= "c.loyer , c.charges, c.fk_renter as reflocataire, c.fk_property as reflocal, c.preavis as preavis, c.tva, l.fk_owner";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter loc";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
		$sql .= " WHERE preavis = 0 AND loc.rowid = c.fk_renter and l.rowid = c.fk_property  ";
		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			
			$i = 0;
			$total = 0;
			
			print '<br><table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans('Contract') . '</td>';
			print '<td>' . $langs->trans('Property') . '</td>';
			print '<td>' . $langs->trans('Nomlocal') . '</td>';
			print '<td>' . $langs->trans('Renter') . '</td>';
			print '<td>' . $langs->trans('NameRenter') . '</td>';
			print '<td align="right">' . $langs->trans('AmountTC') . '</td>';
			print '<td align="right">' . $langs->trans('Rent') . '</td>';
			print '<td align="right">' . $langs->trans('Charges') . '</td>';
			print '<td align="right">' . $langs->trans('VATIsUsed') . '</td>';
			print '<td align="right">' . $langs->trans('nameowner') . '</td>';
			print '<td align="right">' . $langs->trans('Select') . '</td>';
			print "</tr>\n";
			
			if ($num > 0) {
				$var = True;
				
				while ( $i < $num ) {
					$objp = $db->fetch_object($resql);
					$var = ! $var;
					print '<tr ' . $bc[$var] . '>';
					
					print '<td>' . $objp->reference . '</td>';
					print '<td>' . $objp->reflocal . '</td>';
					print '<td>' . $objp->local . '</td>';
					print '<td>' . $objp->reflocataire . '</td>';
					print '<td>' . $objp->nom . '</td>';
					
					print '<td align="right">' . price($objp->total) . '</td>';
					print '<td align="right">' . price($objp->loyer) . '</td>';
					print '<td align="right">' . price($objp->charges) . '</td>';
					print '<td align="right">' . yn($objp->tva) . '</td>';
					print '<td align="right">' . $objp->fk_owner . '</td>';
					
					// Colonne choix contrat
					print '<td align="center">';
					
					print '<input type="checkbox" name="mesCasesCochees[]" value="' . $objp->reference . '_' . $objp->reflocal . '_' . $objp->reflocataire . '_' . $objp->total . '_' . $objp->loyer . '_' . $objp->charges . '_' . $objp->fk_owner . '"' . ($objp->reflocal ? ' checked="checked"' : "") . '/>';
					print '</td>';
					print '</tr>';
					
					$i ++;
				}
			}
			$var = ! $var;
			
			print "</table>\n";
			$db->free($resql);
		} 

		else {
			dol_print_error($db);
		}
		print '</form>';
}
/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
else
{
	if ($id > 0)
	{
		if ($action == 'edit')
		{
			llxheader('', $langs->trans("Receipt") . ' | ' . $langs->trans("Card"), '');
		
			$receipt = new Immoreceipt($db);
			$result = $receipt->fetch($id);
			
			if ($action == 'delete') {
				// Param url = id de la periode à supprimer - id session
				$ret = $form->form_confirm($_SERVER['PHP_SELF'] . '?id=' . $id, $langs->trans("Delete"), $langs->trans("Delete"), "confirm_delete", '', '', 1);
				if ($ret == 'html')
					print '<br>';
			}
			
			print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
			
			$head = receipt_prepare_head($receipt);
			dol_fiche_head($head, 'card', $langs->trans("ReceiptCard"), 0, 'rent@immobilier');
			
			print '<table class="border" width="100%">';
			
			// Nom du loyer
			print '<tr><td class="titlefield">' . $langs->trans("NomLoyer") . '</td>';
			print '<td><input name="nom" size="20" value="' . $receipt->name . '"</td></tr>';
			
			// Contract
			print '<tr><td>' . $langs->trans("Contract") . '</td>';
			print '<td>' . $receipt->fk_contract . '</td></tr>';
			print '<tr><td>';
			print $langs->trans('VATIsUsed');
			print '</td><td>';
			print yn($receipt->addtva);
			print '</td>';
			print '</tr>';

			// Bien
			print '<tr><td>' . $langs->trans("Property") . ' </td>';
			print '<td>' . $receipt->nomlocal . '</td></tr>';

			// Nom locataire
			print '<tr><td>' . $langs->trans("Renter") . '</td>';
			print '<td>' . $receipt->nomlocataire . '</td></tr>';
			
			// Amount
			print '<tr><td>' . $langs->trans("AmountTC") . '</td>';
			print '<td>' . $receipt->amount_total . '</td></tr>';
			print '<tr><td>' . $langs->trans("Rent") . '</td>';
			print '<td><input name="rent" size="10" value="' . $receipt->rent . '"</td></tr>';
			print '<tr><td>' . $langs->trans("Charges") . '</td>';
			print '<td><input name="charges" size="10" value="' . $receipt->charges . '"</td>';
			print '<tr><td>' . $langs->trans("VAT") . '</td>';
			print '<td>' . $receipt->vat . '</td>';
			$rowspan = 5;
			print '<td rowspan="' . $rowspan . '" valign="top">';
			
			/*
			 * Paiements
			 */
			$sql = "SELECT p.rowid, p.fk_receipt, date_payment as dp, p.amount, p.comment as type, il.amount_total ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "immo_payment as p";
			$sql .= ", " . MAIN_DB_PREFIX . "immo_receipt as il ";
			$sql .= " WHERE p.fk_receipt = " . $id;
			$sql .= " AND p.fk_receipt = il.rowid";
			$sql .= " ORDER BY dp DESC";
			
			$resql = $db->query($sql);
			if ($resql) {
				$num = $db->num_rows($resql);
				$i = 0;
				$total = 0;
				echo '<table class="nobordernopadding" width="100%">';
				print '<tr class="liste_titre">';
				print '<td>' . $langs->trans("Date") . '</td><td>' . $langs->trans("Type") . '</td>';
				print '<td align="right">' . $langs->trans("Amount") . '</td><td></td><td>X</td></tr>';
				
				$var = True;
				while ( $i < $num ) {
					$objp = $db->fetch_object($resql);
					$var = ! $var;
					print "<tr " . $bc[$var] . "><td>";
					print "<a href=" . DOL_URL_ROOT . "/custom/immobilier/receipt/payment/card.php?action=update&id=" . $objp->rowid . "&amp;receipt=" . $id . ">" . img_object($langs->trans("Payment"), "payment") . "</a> ";
					print dol_print_date($db->jdate($objp->dp), 'day') . "</td>\n";
					print "<td>" . $objp->type . "</td>\n";
					print '<td align="right">' . price($objp->amount) . "</td><td>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td>\n";
					
					print '<td>';
					if ($user->admin) {
						print "<a href=\"" . DOL_URL_ROOT . "/custom/immobilier/receipt/payment/card.php?id=". $objp->rowid . "&amp;action=delete&amp;receipt=" . $id . "\">";
						print img_delete();
						print '</a>';
					}
					print '</td>';
					print "</tr>";
					$totalpaye += $objp->amount;
					$i ++;
				}
				
				if ($receipt->paye == 0) {
					print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\"><b>" . price($totalpaye) . "</b></td><td>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";
					print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\" bgcolor=\"#d0d0d0\">" . price($receipt->amount_total) . "</td><td bgcolor=\"#d0d0d0\">&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";
					
					$resteapayer = $receipt->amount_total - $totalpaye;
					
					print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans("RemainderToPay") . " :</td>";
					print "<td align=\"right\" bgcolor=\"#f0f0f0\"><b>" . price($resteapayer) . "</b></td><td bgcolor=\"#f0f0f0\">&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";
				}
				print "</table>";
				$db->free($resql);
			} else {
				dol_print_error($db);
			}
			print "</td>";
			
			print "</tr>";
			
			// Due date
			print '<tr><td>' . $langs->trans("Echeance") . '</td>';
			print '<td align="left">';
			print $form->select_date($receipt->echeance, 'ech', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_du") . '</td>';
			print '<td align="left">';
			print $form->select_date($receipt->date_start, 'period', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_au") . '</td>';
			print '<td align="left">';
			print $form->select_date($receipt->date_end, 'periodend', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Comment") . '</td>';
			print '<td><input name="commentaire" size="70" value="' . $receipt->commentaire . '"</td></tr>';
			
			// Status loyer
			print '<tr><td>statut</td>';
			print '<td align="left" nowrap="nowrap">';
			print $receipt->LibStatut($receipt->paye, 5);
			print "</td></tr>";
			
			print '<tr><td colspan="2">&nbsp;</td></tr>';
			
			print '</table>';
			
			
				// Show email send receipt
			/*if ($action == 'sendreceipt')
			{
				
				print load_fiche_titre($langs->trans("SendReceipt"));

				// Cree l'objet formulaire mail
				include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
				$formmail = new FormMail($db);
				$formmail->fromname = (isset($_POST['fromname'])?$_POST['fromname']:$conf->global->MAIN_MAIL_EMAIL_FROM);
				$formmail->frommail = (isset($_POST['frommail'])?$_POST['frommail']:$conf->global->MAIN_MAIL_EMAIL_FROM);
				$formmail->trackid='sendreceipt';
				$formmail->withfromreadonly=0;
				$formmail->withsubstit=0;
				$formmail->withfrom=1;
				$formmail->witherrorsto=1;
				$formmail->withto=$receipt->emaillocataire;
				$formmail->withtocc=(! empty($_POST['sendtocc'])?$_POST['sendtocc']:1);       // ! empty to keep field if empty
				$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:1);    // ! empty to keep field if empty
				$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:$langs->trans("Test"));
				$formmail->withtopicreadonly=0;
				$formmail->withfile=2;
				$formmail->withbody=$langs->transnoentities("PredefinedMailSendReceipt");
				$formmail->withbodyreadonly=0;
				$formmail->withcancel=1;
				$formmail->withdeliveryreceipt=1;
				$formmail->withfckeditor=($action == 'testhtml'?1:0);
				$formmail->ckeditortoolbar='dolibarr_mailings';
				// Tableau des substitutions
				$formmail->substit=$substitutionarrayfortest;
				// Tableau des parametres complementaires du post
				$formmail->param["action"]='sendreceipt';
				$formmail->param["models"]="body";
				$formmail->param["mailid"]=0;
				$formmail->param["returnurl"]=$_SERVER["PHP_SELF"];

				// Init list of files
				if (GETPOST("mode")=='init')
				{
					$formmail->clear_attached_files();
				}

				print $formmail->get_form(($action == 'testhtml'?'addfilehtml':'addfile'),($action == 'testhtml'?'removefilehtml':'removefile'));

				print '<br>';
			}*/
			
			dol_fiche_end();
			print '<div class="center">';
			print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			print '<input type="submit" class="butActionDelete" name="cancel" value="' . $langs->trans("Cancel") . '">';
			print '</div>';
			
			
		} else {
			
		// Display receipt card
			llxheader('', $langs->trans("Receipt") . ' | ' . $langs->trans("Card"), '');
			
			$receipt = new Immoreceipt($db);
			$result = $receipt->fetch($id);
			
			if ($action == 'delete') {
				// Param url = id de la periode à supprimer - id session
				$ret = $form->form_confirm($_SERVER['PHP_SELF'] . '?id=' . $id, $langs->trans("Delete"), $langs->trans("Delete"), "confirm_delete", '', '', 1);
				if ($ret == 'html')
					print '<br>';
			}
			
			$head = receipt_prepare_head($receipt);
			dol_fiche_head($head, 'card', $langs->trans("Receipt"), 0, 'rent@immobilier');
			
			print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

			$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

			print '<div class="fichecenter">';
			print '<div class="fichehalfleft">';
			print '<div class="underbanner clearboth"></div>';
				
			print '<table class="border centpercent">';

			// Ref
			print '<tr><td class="titlefield">'.$langs->trans("Ref").'</td><td>';
			print $form->showrefnav($receipt, 'id', $linkback, 1, 'id', 'id', '');
			print '</td>';
			print '</tr>';

			// Receipt name
			print '<tr><td>' . $langs->trans("NomLoyer") . '</td>';
			print '<td>' . $receipt->name . '</td></tr>';
			
			// Contract
			print '<tr><td>' . $langs->trans("Contract") . '</td>';
			print '<td>' . $receipt->fk_contract . '</td></tr>';
			
			// VAT
			print '<tr><td>';
			print $langs->trans('VATIsUsed');
			print '</td><td>';
			print yn($receipt->addtva);
			print '</td>';
			print '</tr>';

			// Property
			print '<tr><td>' . $langs->trans("Property") . ' </td>';
			print '<td>' . $receipt->nomlocal . '</td></tr>';

			// Renter
			print '<tr><td>' . $langs->trans("Renter") . '</td>';
			print '<td>' . $receipt->nomlocataire . '</td></tr>';
			
			// Amount
			print '<tr><td>' . $langs->trans("AmountTC") . '</td>';
			print '<td>' . price($receipt->amount_total) . '</td></tr>';
			print '<tr><td>' . $langs->trans("AmountHC") . '</td>';
			print '<td>' . price($receipt->rent) . '</td></tr>';
			print '<tr><td>' . $langs->trans("Charges") . '</td>';
			print '<td>' . price($receipt->charges) . '</td>';
			print '<tr><td>' . $langs->trans("VAT") . '</td>';
			print '<td>' . price($receipt->vat) . '</td>';
			print "</tr>";
			
			// Due date
			print '<tr><td>' . $langs->trans("Echeance") . '</td>';
			print '<td>';
			print dol_print_date($receipt->echeance,"day");
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_du") . '</td>';
			print '<td>';
			print dol_print_date($receipt->date_start,"day");
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_au") . '</td>';
			print '<td>';
			print dol_print_date($receipt->date_end,"day");
			print '</td>';
			print '<tr><td>' . $langs->trans("Comment") . '</td>';
			print '<td>' . $receipt->commentaire . '</td></tr>';
			
			// Status loyer
			print '<tr><td>'.$langs->trans("Status").'</td>';
			print '<td>';
			print $receipt->LibStatut($receipt->paye, 5);
			print '</td></tr>';
			
			print '</table>';

			print '</div>';
			print '<div class="fichehalfright">';
			print '<div class="ficheaddleft">';

			// List of payments
			$sql = "SELECT p.rowid, p.fk_receipt, date_payment as dp, p.amount, p.fk_typepayment, pp.libelle as typepayment_label, il.amount_total ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "immo_payment as p";
			$sql .= ", " . MAIN_DB_PREFIX . "immo_receipt as il ";
			$sql .= ", " . MAIN_DB_PREFIX . "c_paiement as pp";
			$sql .= " WHERE p.fk_receipt = " . $id;
			$sql .= " AND p.fk_receipt = il.rowid";
			$sql .= " AND p.fk_typepayment = pp.id";
			$sql .= " ORDER BY dp DESC";
			
			$resql = $db->query($sql);
			if ($resql) {
				$num = $db->num_rows($resql);
				$i = 0;
				$total = 0;
				print '<table class="nobordernopadding" width="100%">';
				
				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans("RefPayment").'</td>';
				print '<td>'.$langs->trans("Date").'</td>';
				print '<td>'.$langs->trans("Type").'</td>';
				print '<td align="right">'.$langs->trans("Amount").'</td>';
				if ($user->admin) print '<td>&nbsp;</td>';
				print '</tr>';
				
				$var = True;
				while ( $i < $num ) {
					$objp = $db->fetch_object($resql);
					$var = ! $var;
					print "<tr " . $bc[$var] . "><td>";
					print "<a href=" . DOL_URL_ROOT . "/custom/immobilier/receipt/payment/card.php?action=update&amp;id=" . $objp->rowid . "&amp;receipt=" . $id . ">" . img_object($langs->trans("Payment"), "payment") . ' ' . $objp->rowid . "</a></td>";
					print '<td>' . dol_print_date($db->jdate($objp->dp), 'day') . '</td>';
					print '<td>' . $objp->typepayment_label . '</td>';
					print '<td align="right">' . price($objp->amount) . "&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td>\n";
					
					print '<td align="right">';
					if ($user->admin) {
						print "<a href=\"" . DOL_URL_ROOT . "/custom/immobilier/receipt/payment/card.php?id=". $objp->rowid . "&amp;action=delete&amp;receipt=" . $id . "\">";
						print img_delete();
						print '</a>';
					}
					print '</td>';
					print "</tr>";
					$totalpaye += $objp->amount;
					$i ++;
				}
				
				if ($receipt->paye == 0) {
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\"><b>" . price($totalpaye) . "</b></td></tr>\n";
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\">" . price($receipt->amount_total) . "</td></tr>\n";
					
					$remaintopay = $receipt->amount_total - $totalpaye;
					
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("RemainderToPay") . " :</td>";
					print '<td align="right"'.($remaintopay?' class="amountremaintopay"':'').'>'.price($remaintopay)."</td></tr>\n";
				}
				print "</table>";
				$db->free($resql);
			} else {
				dol_print_error($db);
			}

			print '</div>';
			print '</div>';
			print '</div>';
	
			print '<div class="clearboth"></div>';

			// Show email send receipt
			/*if ($action == 'sendreceipt')
			{
				
				print load_fiche_titre($langs->trans("SendReceipt"));

				// Cree l'objet formulaire mail
				include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
				$formmail = new FormMail($db);
				$formmail->fromname = (isset($_POST['fromname'])?$_POST['fromname']:$conf->global->MAIN_MAIL_EMAIL_FROM);
				$formmail->frommail = (isset($_POST['frommail'])?$_POST['frommail']:$conf->global->MAIN_MAIL_EMAIL_FROM);
				$formmail->trackid='sendreceipt';
				$formmail->withfromreadonly=0;
				$formmail->withsubstit=0;
				$formmail->withfrom=1;
				$formmail->witherrorsto=1;
				$formmail->withto=$receipt->emaillocataire;
				$formmail->withtocc=(! empty($_POST['sendtocc'])?$_POST['sendtocc']:1);       // ! empty to keep field if empty
				$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:1);    // ! empty to keep field if empty
				$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:$langs->trans("Test"));
				$formmail->withtopicreadonly=0;
				$formmail->withfile=2;
				$formmail->withbody=$langs->transnoentities("PredefinedMailSendReceipt");
				$formmail->withbodyreadonly=0;
				$formmail->withcancel=1;
				$formmail->withdeliveryreceipt=1;
				$formmail->withfckeditor=($action == 'testhtml'?1:0);
				$formmail->ckeditortoolbar='dolibarr_mailings';
				// Tableau des substitutions
				$formmail->substit=$substitutionarrayfortest;
				// Tableau des parametres complementaires du post
				$formmail->param["action"]='sendreceipt';
				$formmail->param["models"]="body";
				$formmail->param["mailid"]=0;
				$formmail->param["returnurl"]=$_SERVER["PHP_SELF"];

				// Init list of files
				if (GETPOST("mode")=='init')
				{
					$formmail->clear_attached_files();
				}

				print $formmail->get_form(($action == 'testhtml'?'addfilehtml':'addfile'),($action == 'testhtml'?'removefilehtml':'removefile'));

				print '<br>';
			}*/

			dol_fiche_end();

			if (is_file($conf->immobilier->dir_output . '/quittance_' . $id . '.pdf')) {
				print '&nbsp';
				print '<table class="border" width="100%">';
				print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("LinkedDocuments") . '</td></tr>';
				// afficher
				$legende = $langs->trans("Ouvrir");
				print '<tr><td width="200" align="center">' . $langs->trans("Quittance") . '</td><td> ';
				print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=immobilier&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
				print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
				print '</td></tr></table>';
			}
			
			print '</div>';
			
		/*
		 * Actions bar
		 */

		print '<div class="tabsAction">';

		if ($action != 'create' && $action != 'edit')
		{
			
			// Edit
			print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/card.php?id=' . $receipt->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>';

			// Create payment
			if ($receipt->paye == 0 && $user->rights->immobilier->rent->write)
			{
				if ($remaintopay == 0)
				{
					print '<div class="inline-block divButAction"><span class="butActionRefused" title="' . $langs->trans("DisabledBecauseRemainderToPayIsZero") . '">' . $langs->trans('DoPayment') . '</span></div>';
				}
				else
				{
					print '<div class="inline-block divButAction"><a class="butAction" href="'.DOL_URL_ROOT.'/custom/immobilier/receipt/payment/card.php?id=' . $id . '&amp;action=create">' . $langs->trans('DoPayment') . '</a></div>';
				}
			}

			// Classify 'paid'
			if ($receipt->paye == 0 && round($remaintopay) <= 0) {
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=paid&id=' . $id . '">' . $langs->trans('ClassifyPaid') . '</a></div>';
			}
			
			// Delete
			print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/card.php?id=' . $id . '&amp;action=delete">' . $langs->trans("Delete") . '</a></div>';
			
			// Generate receipt
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=quittance&id=' . $id . '">' . $langs->trans('GenererQuittance') . '</a></div>';
			
			// Send receipt
			print '<div class="inline-block divButAction"><a class="butAction" href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/mails.php?id=' . $id . '">' . $langs->trans('SendMail') . '</a></div>';
		}

		print '</div>';

		print '<table width="100%"><tr><td width="50%" valign="top">';

		/*
		 * Documents generes
		 */
		$filename	=	dol_sanitizeFileName($receipt->id);
		$filedir	=	$conf->immobilier->dir_output . "/" . dol_sanitizeFileName($receipt->id);
		$urlsource	=	$_SERVER['PHP_SELF'].'?id='.$receipt->id;
		$genallowed	=	$user->rights->immobilier->rent->write;
		$delallowed	=	$user->rights->immobilier->rent->delete;

		$var=true;

		print '<br>';
		//$formfile->show_documents('immobilier',$filename,$filedir,$urlsource,$genallowed,$delallowed,$receipt->modelpdf);

		print '</td><td>&nbsp;</td>';

		print '</tr></table>';
		}
	}
}
llxFooter();

$db->close();
