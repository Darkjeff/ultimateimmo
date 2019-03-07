<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019 Philippe GRAND  <philippe.grand@atoo-net.com>
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
 *   	\file       immoreceipt_card.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to create/edit/view immoreceipt
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');
include_once(DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/lib/immoreceipt.lib.php');
dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_immobilier.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');

// Load translation files required by the page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other", "compta", "bills", "contracts"));

// Get parameters
$id			= GETPOST('id', 'int');
$rowid 		= GETPOST('rowid', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'immoreceiptcard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object=new ImmoReceipt($db);
$immorent=new ImmoRent($db);

$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->ultimateimmo->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('immoreceiptcard','globalcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options=$extrafields->getOptionalsFromPost($object->table_element,'','search_');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$isdraft = (($object->statut == ImmoReceipt::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'ultimateimmo', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

/*
 * 	Classify paid
 */
if ($action == 'paid') 
{
	$receipt = new ImmoReceipt($db);
	$receipt->fetch($id);
	$result = $receipt->set_paid($user);
	Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
}

/*
 *	Delete rental
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	$receipt = new ImmoReceipt($db);
	$receipt->fetch($id);
	$result = $receipt->delete($user);
	if ($result > 0)
	{
		header("Location:" .dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1));
		exit();
	}
	else
	{
		$mesg = '<div class="error">' . $receipt->error . '</div>';
	}
}

/*
 * Action generate quitance
 */
if ($action == 'quittance') 
{
	// Define output language
	$outputlangs = $langs;
	
	$file = 'quittance_' . $id . '.pdf';
	
	$result = ultimateimmo_pdf_create($db, $id, '', 'quittance', $outputlangs, $file);
	
	if ($result > 0) 
	{
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
		exit();
	} 
	else 
	{
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
	
	$result = ultimateimmo_pdf_create($db, $id, '', 'chargeloc', $outputlangs, $file);
	
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
if ($action == 'add' && ! $cancel) 
{
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
	
	if (empty($dateech)) 
	{
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("DateDue")), 'errors');
		$action = 'create';
	} 
	elseif (empty($dateperiod)) 
	{
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Period")) . '</div>';
		$action = 'create';
	} 
	elseif (empty($dateperiodend)) 
	{
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Periodend")) . '</div>';
		$action = 'create';
	} 
	else 
	{
		
		$mesLignesCochees = GETPOST('mesCasesCochees');
		
		foreach ( $mesLignesCochees as $maLigneCochee ) {
			
			$receipt = new ImmoReceipt($db);
			
			$maLigneCourante = split("_", $maLigneCochee);
			$monId = $maLigneCourante[0];
			$monLocal = $maLigneCourante[1];
			$monLocataire = $maLigneCourante[2];
			$monMontant = $maLigneCourante[3];
			$monLoyer = $maLigneCourante[4];
			$monCharges = $maLigneCourante[5];
			$monTVA = $maLigneCourante[6];
			
			// main info loyer
			$receipt->label = GETPOST('label', 'alpha');
			$receipt->date_echeance = $dateech;
			$receipt->date_start = $dateperiod;
			$receipt->date_end = $dateperiodend;
			
			// main info contrat
			$receipt->fk_rent = $monId;
			$receipt->fk_property = $monLocal;
			$receipt->fk_renter = $monLocataire;
			$receipt->fk_owner = $user->id;
			
			if ($monTVA == Oui) 
			{
				$receipt->total_amount = $monMontant * 1.2;
				$receipt->vat_amount = $monMontant * 0.2;
			}
			else 
			{
				$receipt->total_amount = $monMontant;
				$receipt->vat_amount = 0;
			}
			
			$receipt->rentamount = $monLoyer;
			$receipt->chargesamount = $monCharges;
			$receipt->status=0;
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
		Header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php',1));
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
	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($id);
	
	$receipt->label 			= GETPOST('label');
	if ($receipt->vat_tx != 0)
	{
		$receipt->total_amount 	= (GETPOST('rentamount') + GETPOST('chargesamount'))*1.2;
	}
	else
	{
		$receipt->total_amount 	= GETPOST('rentamount') + GETPOST('chargesamount');
	}
	$receipt->rentamount 		= GETPOST('rentamount');
	$receipt->chargesamount 	= GETPOST('chargesamount');
	if ($receipt->vat_tx != 0)
	{
		$receipt->vat_amount 	= (GETPOST('rentamount') + GETPOST('chargesamount'))*0.2;
	}
	else
	{
		$receipt->vat_amount 	= 0;
	}

	$receipt->date_echeance 		= $dateech;
	$receipt->note_public 	= GETPOST('note_public');
	$receipt->status 		= GETPOST('status');
	$receipt->date_start 	= $dateperiod;
	$receipt->date_end 		= $dateperiodend;
	
	$result = $receipt->update($user);
	Header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php',1).'?id=' . $receipt->id);
	if ($id > 0)
	{
		$mesg='<div class="ok">'.$langs->trans("SocialContributionAdded").'</div>';
	}
	else
	{
		$mesg = '<div class="error">' . $receipt->error . '</div>';
	}
}

/*
 * Actions
 *
 * Put here all code to do according to value of "action" parameter
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error=0;

	$permissiontoadd = $user->rights->ultimateimmo->write;
	$permissiontodelete = $user->rights->ultimateimmo->delete || ($permissiontoadd && $object->status == 0);
    $backurlforlist = dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php',1);
	if (empty($backtopage)) {
	    if (empty($id)) $backtopage = $backurlforlist;
	    else $backtopage = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php',1).($id > 0 ? $id : '__ID__');
    	}
	$triggermodname = 'ULTIMATEIMMO_IMMORECEIPT_MODIFY';	// Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, delete or clone
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='IMMORECEIPT_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_IMMORECEIPT_TO';
	$trackid='immoreceipt'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form=new Form($db);
$formfile=new FormFile($db);

llxHeader('','ImmoReceipt','');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->transnoentitiesnoconv("MenuNewImmoReceipt"));
	
	$year_current = strftime("%Y", dol_now());
	$pastmonth = strftime("%m", dol_now());
	$pastmonthyear = $year_current;
	if ($pastmonth == 0)
	{
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

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent">'."\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');
	
	foreach($object->fields as $key => $val)
	{
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1) continue;
		
		if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

		print '<tr id="field_'.$key.'">';
		print '<td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '"';
		print '>';
		print $langs->trans($val['label']);
		print '</td>';
		print '<td>';
		
		if ($val['label'] == 'Ref')
		{
			// Reference
			if (! empty($modCodeReceipt->code_auto)) {
				$tmpcode=$langs->trans("Draft");
			} else {
				$tmpcode='<input name="ref" class="maxwidth100" maxlength="128" value="'.dol_escape_htmltag($ref?$ref:$tmpcode).'">';
			}
			print $tmpcode;
		}
		elseif ($val['label'] == 'DateCreation')
		{
			// DateCreation
			print $form->select_date(($object->date_creation ? $object->date_creation : -1), "date_creation",0,0,0,"",1,1,1);
		}
		elseif ($val['label'] == 'DateStart')
		{
			// date_start
			print $form->select_date(($object->date_start ? $object->date_start : -1), "date_start",0,0,0,"",1,1,1);
		}
		elseif ($val['label'] == 'DateEnd')
		{
			// date_end
			print $form->select_date(($object->date_end ? $object->date_end : -1), "date_end",0,0,0,"",1,1,1);
		}
		elseif ($val['label'] == 'Echeance')
		{
			// Echeance
			print $form->select_date($object->date_echeance, "date_echeance",0,0,0,"",1,1,1);
		}
		else
		{
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
			else $value = GETPOST($key, 'alpha');
			print $object->showInputField($val, $key, $value, '', '', '', 0);
		}
		print '</td>';
		print '</tr>';

	}

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}
/* *************************************************************************** */
/*                                                                             */
/* Mode add all contracts                                                       */
/*                                                                             */
/* *************************************************************************** */

elseif ($action == 'createall') 
{
		//llxheader('', $langs->trans("newrental"), '');
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
		print '<td><input name="label" size="30" value="' . GETPOST('label') . '"</td>';
		
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
		$sql = "SELECT c.rowid as reference, loc.lastname as nom, l.address  , l.label as local, loc.status as statut, c.totalamount as total, c.rentamount , c.chargesamount, c.fk_renter as reflocataire, c.fk_property as reflocal, c.preavis as preavis, c.vat";
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immorenter loc";
		$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorent as c";
		$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as l";
		$sql .= " WHERE preavis = 0 AND loc.rowid = c.fk_renter and l.rowid = c.fk_property  ";
		if ($user->id != 1) {
			$sql .= " AND c.fk_owner=" . $user->id;
		}
		$resql = $db->query($sql);
		if ($resql)
	{
		$num = $db->num_rows($resql);

		$i = 0;
		$total = 0;

		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('Contract') . '</td>';
		print '<td>' . $langs->trans('Property') . '</td>';
		print '<td>' . $langs->trans('Nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td>' . $langs->trans('RenterName') . '</td>';
		print '<td align="right">' . $langs->trans('TotalAmount') . '</td>';
		print '<td align="right">' . $langs->trans('RentAmount') . '</td>';
		print '<td align="right">' . $langs->trans('ChargesAmount') . '</td>';
		print '<td align="right">' . $langs->trans('VATIsUsed') . '</td>';
		print '<td align="right">' . $langs->trans('OwnerName') . '</td>';
		print '<td align="right">' . $langs->trans('Select') . '</td>';
		print "</tr>\n";

		if ($num > 0)
		{
			while ( $i < $num )
			{
				$objp = $db->fetch_object($resql);
				$immorent->ref=$objp->reference;
				$immorent->id=$objp->reference;
				print '<tr class="oddeven">';
				if ($objp->reference > 0)
				{
					print '<td>' . $immorent->getNomUrl(0) . '</td>';
				}
				print '<td>' . $objp->reflocal . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->reflocataire . '</td>';
				print '<td>' . $objp->nom . '</td>';

				print '<td align="right">' . price($objp->total) . '</td>';
				print '<td align="right">' . price($objp->rentamount) . '</td>';
				print '<td align="right">' . price($objp->chargesamount) . '</td>';
				print '<td align="right">' . yn($objp->vat) . '</td>';
				print '<td align="right">' . $objp->fk_owner . '</td>';

				// Colonne choix contrat
				print '<td align="center">';

				print '<input type="checkbox" name="mesCasesCochees[]" value="' . $objp->reference . '_' . $objp->reflocal . '_' . $objp->reflocataire . '_' . $objp->total . '_' . $objp->rentamount . '_' . $objp->chargesamount . '_' . $objp->fk_owner . '"' . ($objp->reflocal ? ' checked="checked"' : "") . '/>';
				print '</td>';
				print '</tr>';

				$i ++;
			}
		}

		print "</table>\n";
		$db->free($resql);
	}

	else
	{
		dol_print_error($db);
	}
	print '</form>';
}
// Part to edit record
else
{
	// Part to edit record
	if (($id || $ref) && $action == 'edit')
	{
		print load_fiche_titre($langs->trans("newrental", $langs->transnoentitiesnoconv("ImmoReceipt")));

		$receipt = new ImmoReceipt($db);
		$result = $receipt->fetch($id);

		if ($action == 'delete')
		{
			// Param url = id de la periode à supprimer - id session
			$ret = $form->form_confirm($_SERVER['PHP_SELF'] . '?id=' . $id, $langs->trans("Delete"), $langs->trans("Delete"), "confirm_delete", '', '', 1);
			if ($ret == 'html')
				print '<br>';
		}

		print '<form name="fiche_loyer" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		dol_fiche_head();

		print '<table class="border centpercent">'."\n";

		// Common attributes
		$object->fields = dol_sort_array($object->fields, 'position');

		foreach($object->fields as $key => $val)
		{
			// Discard if extrafield is a hidden field on form
			if (abs($val['visible']) != 1) continue;

			if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

			print '<tr><td';
			print ' class="titlefieldcreate';
			if ($val['notnull'] > 0) print ' fieldrequired';
			if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
			print '"';
			print '>'.$langs->trans($val['label']).'</td>';
			print '<td>';

			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOSTISSET($key)?GETPOST($key, 'int'):$object->$key;
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOSTISSET($key)?GETPOST($key,'none'):$object->$key;
			else $value = GETPOSTISSET($key)?GETPOST($key, 'alpha'):$object->$key;
			//var_dump($val.' '.$key.' '.$value);
			print $object->showInputField($val, $key, $value, '', '', '', 0);
			print '</td>';
			print '</tr>';
		}

			/*
			 * Paiements
			 */
			$sql = "SELECT p.rowid, p.fk_receipt, p.date_payment as dp, p.amount, pp.libelle as type, il.total_amount ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
			$sql .= ", " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as il ";
			$sql .= ", " . MAIN_DB_PREFIX . "c_paiement as pp";
			$sql .= " WHERE p.fk_receipt = " . $id;
			$sql .= " AND p.fk_receipt = il.rowid";
			$sql .= " AND type = pp.id";
			$sql .= " ORDER BY dp DESC";

			$resql = $db->query($sql);
			if ($resql)
			{
				$num = $db->num_rows($resql);
				$i = 0;
				$total = 0;
				echo '<table class="nobordernopadding" width="100%">';
				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans("RefPayment").'</td>';
				print '<td>'.$langs->trans("Date").'</td>';
				print '<td>'.$langs->trans("Type").'</td>';
				print '<td align="right">'.$langs->trans("Amount").'</td>';
				if ($user->admin) print '<td>&nbsp;</td>';
				print '</tr>';

				while ( $i < $num )
				{
					$objp = $db->fetch_object($resql);
					print '<tr class="oddeven"><td>';
					print '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_card.php',1).'?action=update&amp;id='.$objp->rowid."&amp;receipt=".$id.'">' . img_object($langs->trans("Payment"), "payment"). ' ' .$objp->rowid.'</a></td>';
					print '<td>'.dol_print_date($db->jdate($objp->dp), 'day').'</td>';
					print '<td>'.$objp->type.'</td>';
					print '<td align="right">' . price($objp->amount)."&nbsp;".$langs->trans("Currency".$conf->currency)."</td>\n";

					print '<td>';
					if ($user->admin)
					{
						print '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_card.php',1).'?id='.$objp->rowid. "&amp;action=delete&amp;receipt=".$id.'">';
						print img_delete();
						print '</a>';
					}
					print '</td>';
					print "</tr>";
					$totalpaye += $objp->amount;
					$i ++;
				}

				if ($object->status == 0)
				{
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\"><b>" . price($totalpaye) . "</b></td><td>&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\" bgcolor=\"#d0d0d0\">" . price($object->total_amount) . "</td><td bgcolor=\"#d0d0d0\">&nbsp;" . $langs->trans("Currency" . $conf->currency) . "</td></tr>\n";

					$remaintopay = $object->total_amount - $totalpaye;

					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("RemainderToPay") . " :</td>";
					print '<td align="right"'.($remaintopay?' class="amountremaintopay"':'').'>'.price($remaintopay)."</td></tr>\n";
				}

				print "</table>";
				$db->free($resql);
			}
			else
			{
				dol_print_error($db);
			}
			print "</td>";

			print "</tr>";

			// Due date
			print '<tr><td>' . $langs->trans("Echeance") . '</td>';
			print '<td align="left">';
			print $form->select_date($object->date_echeance, 'ech', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_du") . '</td>';
			print '<td align="left">';
			print $form->select_date($object->date_start, 'period', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Periode_au") . '</td>';
			print '<td align="left">';
			print $form->select_date($object->date_end, 'periodend', 0, 0, 0, 'fiche_loyer', 1);
			print '</td>';
			print '<tr><td>' . $langs->trans("Comment") . '</td>';
			print '<td><input name="commentaire" size="70" value="' . $object->note_public . '"</td></tr>';

			// Status loyer
			print '<tr><td>statut</td>';
			print '<td align="left" nowrap="nowrap">';
			print $object->LibStatut($object->status, 5);
			print "</td></tr>";

			print '<tr><td colspan="2">&nbsp;</td></tr>';

		// Other attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

		print '</table>';

		dol_fiche_end();

		print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
		print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
		print '</div>';

		print '</form>';
	}
	else
	{
		// Part to show record
		if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
		{
			$res = $object->fetch_optionals();

			$head = immoreceiptPrepareHead($object);
			dol_fiche_head($head, 'card', $langs->trans("ImmoReceipt"), -1, 'immoreceipt@ultimateimmo');

			$formconfirm = '';

			// Confirmation to delete
			if ($action == 'delete')
			{
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoReceipt'), $langs->trans('ConfirmDeleteImmoReceipt'), 'confirm_delete', '', 0, 1);
			}

			// Clone confirmation
			if ($action == 'clone') {
				// Create an array for form
				$formquestion = array();
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloneImmoReceipt'), $langs->trans('ConfirmCloneImmoReceipt', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
			}

			// Confirmation of action xxxx
			if ($action == 'xxx')
			{
				$formquestion=array();
				/*
				$forcecombo=0;
				if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
				$formquestion = array(
					// 'text' => $langs->trans("ConfirmClone"),
					// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
					// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
					// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
				);
				*/
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
			}

			// Call Hook formConfirm
			$parameters = array('lineid' => $lineid);
			$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
			elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;

			// Print form confirm
			print $formconfirm;


			// Object card
			// ------------------------------------------------------------
			$linkback = '<a href="' .dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

			$morehtmlref='<div class="refidno">';
			/*
			// Ref bis
			$morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->ultimateimmo->creer, 'string', '', 0, 1);
			$morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->ultimateimmo->creer, 'string', '', null, null, '', 1);
			// Thirdparty
			$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
			// Project
			if (! empty($conf->projet->enabled))
			{
				$langs->load("projects");
				$morehtmlref.='<br>'.$langs->trans('Project') . ' ';
				if ($user->rights->ultimateimmo->write)
				{
					if ($action != 'classify')
						$morehtmlref.='<a href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
					if ($action == 'classify') {
						//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
						$morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
						$morehtmlref.='<input type="hidden" name="action" value="classin">';
						$morehtmlref.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
						$morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', 0, 0, 1, 0, 1, 0, 0, '', 1);
						$morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
						$morehtmlref.='</form>';
					} else {
						$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
					}
				} else {
					if (! empty($object->fk_project)) {
						$proj = new Project($db);
						$proj->fetch($object->fk_project);
						$morehtmlref.=$proj->getNomUrl();
					} else {
						$morehtmlref.='';
					}
				}
			}
			*/
			$morehtmlref.='</div>';


			dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


			print '<div class="fichecenter">';
			print '<div class="fichehalfleft">';
			print '<div class="underbanner clearboth"></div>';
			print '<table class="border centpercent">'."\n";

			// Common attributes
			$keyforbreak='note_private';
			include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';
			// List of payments
			$sql = "SELECT p.rowid, p.fk_receipt, p.date_payment as dp, p.amount, p.fk_mode_reglement, pp.libelle as type, il.total_amount ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
			$sql .= ", " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as il ";
			$sql .= ", " . MAIN_DB_PREFIX . "c_paiement as pp";
			$sql .= " WHERE p.fk_receipt = " . $id;
			$sql .= " AND p.fk_receipt = il.rowid";
			$sql .= " AND type = pp.id";
			$sql .= " ORDER BY dp DESC";

			$resql = $db->query($sql);
			if ($resql)
			{
				$num = $db->num_rows($resql);

				$i = 0;
				$total = 0;
				print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans("RefPayment").'</td>';
				print '<td>'.$langs->trans("Date").'</td>';
				print '<td>'.$langs->trans("Type").'</td>';
				print '<td align="right">'.$langs->trans("Amount").'</td>';
				if ($user->admin) print '<td>&nbsp;</td>';
				print '</tr>';

				while ( $i <= $num )
				{
					$objp = $db->fetch_object($resql);

					print '<tr class="oddeven"><td>';
					print '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_card.php',1).'?action=update&amp;id='.$objp->rowid."&amp;receipt=".$id.'">' . img_object($langs->trans("Payment"), "payment"). ' ' .$objp->rowid.'</a></td>';
					print '<td>'.dol_print_date($db->jdate($objp->dp), 'day').'</td>';
					print '<td>'.$objp->type.'</td>';
					print '<td align="right">' . price($objp->amount)."&nbsp;".$langs->trans("Currency".$conf->currency)."</td>\n";

					print '<td align="right">';
					if ($user->admin) {
						print '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_card.php',1).'?id='.$objp->rowid. "&amp;action=delete&amp;receipt=".$id.'">';
						print img_delete();
						print '</a>';
					}
					print '</td>';
					print '</tr>';
					$totalpaye += $objp->amount;

					$i ++;
				}

				if ($object->status == 0)
				{
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\"><b>" . price($totalpaye) . "</b></td></tr>\n";
					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\">" . price($object->total_amount) . "</td></tr>\n";

					$remaintopay = $object->total_amount - $totalpaye;

					print "<tr><td colspan=\"3\" align=\"right\">" . $langs->trans("RemainderToPay") . " :</td>";
					print '<td align="right"'.($remaintopay?' class="amountremaintopay"':'').'>'.price($remaintopay)."</td></tr>\n";
				}
				print "</table>";
				$db->free($resql);
			}
			else
			{
				dol_print_error($db);
			}

			// Other attributes
			include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

			print '</table>';
			print '</div>';
			print '</div>';

			print '<div class="clearboth"></div><br>';

			dol_fiche_end();
			
			if (is_file($conf->ultimateimmo->dir_output . '/receipt/quittance_' . $id . '.pdf'))
			{
				print '&nbsp';
				print '<table class="border" width="100%">';
				print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("LinkedDocuments") . '</td></tr>';
				// afficher
				$legende = $langs->trans("Ouvrir");
				print '<tr><td width="200" align="center">' . $langs->trans("Quittance") . '</td><td> ';
				print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=ultimateimmo&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
				print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
				print '</td></tr></table>';
			}

			print '</div>';


			// Buttons for actions
			if ($action != 'presend' && $action != 'editline') 
			{
				print '<div class="tabsAction">'."\n";
				$parameters=array();
				$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

				if (empty($reshook))
				{
					// Send
					print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

					// Modify
					if ($user->rights->ultimateimmo->write)
					{
						print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
					}
					else
					{
						print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
					}
					
					// Create payment
					if ($receipt->status == 0 && $user->rights->ultimateimmo->rent->write)
					{
						if ($remaintopay == 0)
						{
							print '<div class="inline-block divButAction"><span class="butActionRefused" title="' . $langs->trans("DisabledBecauseRemainderToPayIsZero") . '">' . $langs->trans('DoPayment') . '</span></div>';
						}
						else
						{
							print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=create">' . $langs->trans('DoPayment') . '</a></div>';
						}
					}
					
					// Classify 'paid'
					if ($receipt->status == 0 && round($remaintopay) <= 0) 
					{
						print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=paid&id='.$id.'">'.$langs->trans('ClassifyPaid').'</a></div>';
					}

					// Clone
					if ($user->rights->ultimateimmo->write)
					{
						print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;socid=' . $object->socid . '&amp;action=clone&amp;object=order">' . $langs->trans("ToClone") . '</a></div>';
					}

					/*
					if ($user->rights->ultimateimmo->write)
					{
						if ($object->status == 1)
						{
							print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=disable">'.$langs->trans("Disable").'</a>'."\n";
						}
						else
						{
							print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=enable">'.$langs->trans("Enable").'</a>'."\n";
						}
					}
					*/

					if ($user->rights->ultimateimmo->delete)
					{
						print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
					}
					else
					{
						print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
					}
				}
				print '</div>'."\n";
			}


			// Select mail models is same action as presend
			if (GETPOST('modelselected')) {
				$action = 'presend';
			}

			if ($action != 'presend')
			{
				print '<div class="fichecenter"><div class="fichehalfleft">';
				print '<a name="builddoc"></a>'; // ancre

				// Documents
				/*$objref = dol_sanitizeFileName($object->ref);
				$relativepath = $comref . '/' . $comref . '.pdf';
				$filedir = $conf->ultimateimmo->dir_output . '/' . $objref;
				$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
				$genallowed = $user->rights->ultimateimmo->read;	// If you can read, you can build the PDF to read content
				$delallowed = $user->rights->ultimateimmo->create;	// If you can create/edit, you can remove a file on card
				print $formfile->showdocuments('ultimateimmo', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
				*/

				// Show links to link elements
				$linktoelem = $form->showLinkToObjectBlock($object, null, array('immoreceipt'));
				$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


				print '</div><div class="fichehalfright"><div class="ficheaddleft">';

				$MAXEVENT = 10;

				$morehtmlright = '<a href="'.dol_buildpath('/ultimateimmo/receipt/immoreceipt_info.php', 1).'?id='.$object->id.'">';
				$morehtmlright.= $langs->trans("SeeAll");
				$morehtmlright.= '</a>';

				// List of actions on element
				include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
				$formactions = new FormActions($db);
				$somethingshown = $formactions->showactions($object, 'immoreceipt', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

				print '</div></div></div>';
			}

			//Select mail models is same action as presend
			/*
			 if (GETPOST('modelselected')) $action = 'presend';

			 // Presend form
			 $modelmail='inventory';
			 $defaulttopic='InformationMessage';
			 $diroutput = $conf->product->dir_output.'/inventory';
			 $trackid = 'stockinv'.$object->id;

			 include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
			 */
		}
	}
}

// End of page
llxFooter();
$db->close();
