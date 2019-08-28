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
 *   	\file       immopayment_card.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to create/edit/view immopayment
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

include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/lib/immopayment.lib.php');
if (! empty($conf->banque->enabled)) require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo","other", "contracts", "bills"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$socid 		= GETPOST('socid', 'int');

// Initialize technical objects
$object=new ImmoPayment($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->ultimateimmo->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('immopaymentcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('immopayment');
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
foreach($object->fields as $key => $val)
{
    if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action='view';

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$result = restrictedArea($user, 'ultimateimmo', $id);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals



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
	$permissiontodelete = $user->rights->ultimateimmo->delete;
	$backurlforlist = dol_buildpath('/ultimateimmo/payment/immopayment_list.php',1);

	// Actions cancel, add, update or delete
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='MYOBJECT_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_MYOBJECT_TO';
	$trackid='immopayment'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}

if ($action == 'addall') {
	$datepaie = @dol_mktime(0, 0, 0, GETPOST("paiemonth"), GETPOST("paieday"), GETPOST("paieyear"));
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'createall';
	} else {
	$datapost = $_POST;
		foreach ( $datapost as $key => $value ) {
			if (strpos($key, 'receipt_') !== false) {
			
				$tmp_array = explode('_', $key);
				
				if (count($tmp_array) > 0) {
					$reference = $tmp_array[1];
					$amount= GETPOST('incomeprice_'.$reference);
				
					if (! empty($reference) && !empty($amount)) {
						$paie = new Immopayment($db);

						$paie->fk_rent			= GETPOST('fk_rent_'.$reference);
						$paie->fk_property		= GETPOST('fk_property_'.$reference);
						$paie->fk_renter		= GETPOST('fk_renter_'.$reference);
						$paie->amount			= price2num($amount);
						$paie->note_public		= GETPOST('note_public');
						$paie->date_payment		= $datepaie;
						$paie->fk_receipt		= GETPOST('receipt_'.$reference);
						$paie->fk_bank			= GETPOST("accountid");
						$paie->fk_mode_reglement	= GETPOST("fk_mode_reglement");
						$paie->num_payment		= GETPOST("num_payment");
						$paie->fk_owner			= $user->id;
						
						$result = $paie->create ($user);

						if ($result<0) {
							setEventMessages($paie->error, null, 'errors');
						}
					}
				}
			}
		}		
	}
}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$thirdpartystatic = new Societe($db);

$result=$object->fetch($id, $ref);
if ($result < 0)
{
	dol_print_error($db, 'Payement '.$id.' not found in database');
	exit;
}

llxHeader('',$langs->trans("ImmoPayment"),'');

// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("ImmoPayment")));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

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

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("ImmoPayment"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	dol_fiche_head();

	print '<table class="border centpercent">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
    $res = $object->fetch_optionals($object->id, $extralabels);

	$head = immopaymentPrepareHead($object);
	
	dol_fiche_head($head, 'card', $langs->trans("ImmoPayment"), -1, 'immopayment@ultimateimmo');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoPayment'), $langs->trans('ConfirmDeleteImmoPayment'), 'confirm_delete', '', 0, 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
	    $formquestion=array();
	    /*
	        $formquestion = array(
	            // 'text' => $langs->trans("ConfirmClone"),
	            // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
	            // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
	            // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1)));
	    }*/
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	if (! $formconfirm) {
	    $parameters = array('lineid' => $lineid);
	    $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	    if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
	    elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/ultimateimmo/payment/immopayment_list.php', 1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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
	            $morehtmlref.='<a href="'.DOL_URL_ROOT.'/projet/card.php?id=' . $object->fk_project . '" title="' . $langs->trans('ShowProject') . '">';
	            $morehtmlref.=$proj->ref;
	            $morehtmlref.='</a>';
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

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	dol_fiche_end();


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
    	print '<div class="tabsAction">'."\n";
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

    	if (empty($reshook))
    	{
    	    // Send
            print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

    		if ($user->rights->ultimateimmo->write)
    		{
    			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
    		}
    		else
    		{
    			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
    		}

    		/*
    		if ($user->rights->ultimateimmo->create)
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
    			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
    		}
    	}
    	print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
	    $action = 'presend';
	}

	/*if ($action != 'presend')
	{
	    print '<div class="fichecenter"><div class="fichehalfleft">';
	    print '<a name="builddoc"></a>'; // ancre

	    // Documents
	    $relativepath = '/payment/' . dol_sanitizeFileName($object->ref).'/';
	    $filedir = $conf->ultimateimmo->dir_output . $relativepath;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->ultimateimmo->read;	// If you can read, you can build the PDF to read content
	    $delallowed = $user->rights->ultimateimmo->write;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, 0, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);

	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array('immopayment'));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_info.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, 'immopayment', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

	    print '</div></div></div>';
	}*/

	//Select mail models is same action as presend
	 if (GETPOST('modelselected')) $action = 'presend';

	 // Presend form
	 $modelmail='immopayment';
	 $defaulttopic='InformationMessage';
	 $diroutput = $conf->ultimateimmo->dir_output.'/payment';
	 $trackid = 'immo'.$object->id;

	 include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';

}


/* *************************************************************************** */
/*                                                                             */
/* Mode add all payments                                                       */
/*                                                                             */
/* *************************************************************************** */

if ($action == 'createall') {

	print '<form name="fiche_payment" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addall">';
	
	print '<table class="border" width="100%">';

	print "<tr class=\"liste_titre\">";
	
	print '<td class="left">';
	print $langs->trans("DatePayment");
	print '</td><td class="left">';
	print $langs->trans("Comment");
	print '</td><td class="left">';
	print $langs->trans("PaymentMode");
	print '</td><td class="left">';
	print $langs->trans("BankAccount");
	print '</td><td class="left">';
	print $langs->trans("Numero");
	print '</td>';
	print "</tr>\n";
	
	print '<tr class="oddeven" valign="top">';
	
	// Due date
	
	print '<td class="center">';
	print $form->select_date(! empty($datepaie) ? $datepaie : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	// note_public
	print '<td><input name="note_public" size="30" value="' . GETPOST('note_public') . '"</td>';
	
	// Payment mode
	print '<td class="center">';
	print $form->select_types_paiements(isset($_POST["fk_mode_reglement"])?$_POST["fk_mode_reglement"]:$paie->fk_mode_reglement, "fk_mode_reglement");
	print '</td>';
	
	// AccountToCredit
	print '<td class="center">';
	print $form->select_comptes(isset($_POST["accountid"])?$_POST["accountid"]:$paie->accountid, "accountid", 0, '',1);  // Show open bank account list
	print '</td>';

	// num_payment
	print '<td><input name="num_payment" size="30" value="' . GETPOST('num_payment') . '"</td>';
	
	
	print "</tr>\n";
}
	
	/*
	 * List receipt
	 */
	/*$sql = "SELECT rec.rowid as reference, rec.label as receiptname, loc.ref as nom, l.address  , l.label as local, loc.status as statut, rec.total_amount as total, rec.paiepartiel, rec.balance ,  rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_rent as refcontract , c.status";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt rec";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as l";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorent as c";
	$sql .= " WHERE rec.paye = 0 AND loc.rowid = rec.fk_renter AND l.rowid = rec.fk_property AND  c.rowid = rec.fk_rent and c.status =1 ";
	if ($user->id != 1) {
		$sql .= " AND rec.owner_id=" . $user->id;
	}*/
	
	$sql = 'SELECT f.rowid as facid, f.ref, f.label, f.total_amount, f.paye, f.fk_statut, pf.amount, s.nom as name, s.rowid as socid';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'ultimateimmo_payment_receipt as pf,'.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt as f,'.MAIN_DB_PREFIX.'societe as s';
	$sql.= ' WHERE pf.fk_receipt = f.rowid';
	$sql.= ' AND f.fk_soc = s.rowid';
	$sql.= ' AND f.entity IN ('.getEntity($object->element).')';
	$sql.= ' AND pf.fk_paiement = '.$object->id;
	//var_dump($object);exit;
	$resql=$db->query($sql);
	
	if ($resql)
	{
		$num = $db->num_rows($resql);

		$i = 0;
		$total = 0;

		$moreforfilter='';

		print '<br>';

		print '<div class="div-table-responsive">';
		print '<table class="noborder" width="100%">';

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans('Bill').'</td>';
		print '<td>'.$langs->trans('Company').'</td>';
		if($conf->global->MULTICOMPANY_INVOICE_SHARING_ENABLED )print '<td>'.$langs->trans('Entity').'</td>';
		print '<td class="right">'.$langs->trans('ExpectedToPay').'</td>';
		print '<td class="right">'.$langs->trans('PayedByThisPayment').'</td>';
		print '<td class="right">'.$langs->trans('RemainderToPay').'</td>';
		print '<td class="right">'.$langs->trans('Status').'</td>';
		print "</tr>\n";

		if ($num > 0)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);

				$thirdpartystatic->fetch($objp->socid);

				$receipt=new ImmoReceipt($db);
				$receipt->fetch($objp->facid);
				
				$paiement = $receipt->getSommePaiement();
				$creditnotes=$receipt->getSumCreditNotesUsed();
				$deposits=$receipt->getSumDepositsUsed();
				$alreadypayed=price2num($paiement + $creditnotes + $deposits, 'MT');
				$remaintopay=price2num($receipt->total_ttc - $paiement - $creditnotes - $deposits, 'MT');

				print '<tr class="oddeven">';

				// receipt
				print '<td>';
				print $receipt->getNomUrl(1);
				print "</td>\n";

				// Third party
				print '<td>';
				print $thirdpartystatic->getNomUrl(1);
				print '</td>';

				// Expected to pay
				if($conf->global->MULTICOMPANY_INVOICE_SHARING_ENABLED ){
					print '<td>';
					$mc->getInfo($objp->entity);
					print $mc->label;
					print '</td>';
				}
				// Expected to pay
				print '<td class="right">'.price($objp->total_ttc).'</td>';

				// Amount payed
				print '<td class="right">'.price($objp->amount).'</td>';

				// Remain to pay
				print '<td class="right">'.price($remaintopay).'</td>';

				// Status
				print '<td class="right">'.$invoice->getLibStatut(5, $alreadypayed).'</td>';

				print "</tr>\n";
				if ($objp->paye == 1)	// If at least one invoice is paid, disable delete
				{
					$disable_delete = 1;
					$title_button = dol_escape_htmltag($langs->transnoentitiesnoconv("CantRemovePaymentWithOneInvoicePaid"));
				}
				$total = $total + $objp->amount;
				$i++;
			}
		}
		print "</table>\n";
		print '</div>';

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
	
	/*$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		
		$i = 0;
		$total = 0;
		
		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('NameReceipt') . '</td>';
		print '<td>' . $langs->trans('nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td class="right">' . $langs->trans('montant_tot') . '</td>';
		print '<td class="right">' . $langs->trans('payed') . '</td>';
		print '<td class="right">' . $langs->trans('due') . '</td>';
		print '<td class="right">' . $langs->trans('income') . '</td>';
		print "</tr>\n";
		
		if ($num > 0) {
			
			while ( $i < $num ) {
				$objp = $db->fetch_object($resql);
				
				print '<tr class="oddeven">';			
				print '<td>' . $objp->receiptname . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->nom . '</td>';
				
				print '<td class="right">' . price($objp->total) . '</td>';
				print '<td class="right">' . price($objp->paiepartiel) . '</td>';
				print '<td class="right">' . price($objp->balance) . '</td>';
				
					print '<input type="hidden" name="fk_contract_' . $objp->reference . '" size="10" value="' . $objp->refcontract . '">';
					print '<input type="hidden" name="fk_property_' . $objp->reference . '" size="10" value="' . $objp->reflocal . '">';
					print '<input type="hidden" name="fk_renter_' . $objp->reference . '" size="10" value="' . $objp->reflocataire . '">';
					print '<input type="hidden" name="receipt_' . $objp->reference . '" size="10" value="' . $objp->reference . '">';
				
				// Colonne imput income
				print '<td class="right">';
				print '<input type="text" name="incomeprice_' . $objp->reference . '" id="incomeprice_' . $objp->reference . '" size="6" value="" class="flat">';
				print '</td>';				
				print '</tr>';
				
				$i ++;
			}
		}
		
		print "</table>\n";
		$db->free($resql);
	} 

	else {
		dol_print_error($db);
	}*/
	print '<div class="tabsAction">' . "\n";
	print '<div class="inline-block divButAction"><input type="submit"  name="button_addallpaiement" id="button_addallpaiement" class="butAction" value="' . $langs->trans("Payed") . '" /></div>';
	print '</div>';
	print '</form>';
	
//}


// End of page
llxFooter();
$db->close();
