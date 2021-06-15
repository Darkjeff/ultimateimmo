<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2020 Philippe GRAND  <philippe.grand@atoo-net.com>
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
 *   	\file       immorent_card.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to create/edit/view immorent
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
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/accountancy/class/accountingjournal.class.php';
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/lib/immorent.lib.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/lib/immorenter.lib.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo","other"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object=new ImmoRenter($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->ultimateimmo->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('immorentcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('immorent');
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
if ($user->societe_id > 0) access_forbidden();
if ($user->societe_id > 0) $socid = $user->societe_id;
$result = restrictedArea($user, 'ultimateimmo', $id);

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
	$backurlforlist = dol_buildpath('/ultimateimmo/rent/immorent_list.php',1);

	// Actions cancel, add, update or delete
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Actions to send emails
	$trigger_name='MYOBJECT_SENTBYMAIL';
	$autocopy='MAIN_MAIL_AUTOCOPY_MYOBJECT_TO';
	$trackid='immorent'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 */

$form=new Form($db);
$formfile=new FormFile($db);

$title=$langs->trans("ImmoRenter") . " - " . $langs->trans("ImmoRents");
$help_url='';
//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $title, $help_url);

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("MenuImmoRent"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
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
		if ($val['label'] == 'BirthCountry') 
		{			
			// We set country_id, country_code and country for the selected country
			$object->country_id=GETPOST('country_id','int')?GETPOST('country_id','int'):$object->country_id;
			if ($object->country_id)
			{
				$tmparray=$object->getCountry($object->country_id,'all');
				$object->country_code=$tmparray['code'];
				$object->country=$tmparray['label'];
			}
			// Country
			print $form->select_country((GETPOST('country_id')!=''?GETPOST('country_id'):$object->country_id));	
		}
		else
		{
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOSTISSET($key)?GETPOST($key, 'int'):$object->$key;
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOSTISSET($key)?GETPOST($key,'none'):$object->$key;
			else $value = GETPOSTISSET($key)?GETPOST($key, 'alpha'):$object->$key;
		//var_dump($val.' '.$key.' '.$value);
		print $object->showInputField($val, $key, $value, '', '', '', 0);
		}
		print '</td>';
		print '</tr>';
	}

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

	$head = immorenterPrepareHead($object);
	dol_fiche_head($head, 'immorents', $langs->trans("ImmoRenter"), -1, 'user');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoRent'), $langs->trans('ConfirmDeleteImmoRent'), 'confirm_delete', '', 0, 1);
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
	$linkback = '<a href="' .dol_buildpath('/ultimateimmo/rent/immorent_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref='<div class="refidno">';
	$morehtmlref.='</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Common attributes
	$keyforbreak='note_private';
	
	$staticImmoproperty=new ImmoProperty($db);
	$staticImmoproperty->fetch($object->fk_property);
	print '<tr><td';
	print ' class="titlefield';
	print '<tr><td width="25%">'.$langs->trans('Address').'</td><td>';
	print $staticImmoproperty->address;
	print '<tr><td width="25%">'.$langs->trans('Zip').'</td><td>';
	print $staticImmoproperty->zip;
	print '<tr><td width="25%">'.$langs->trans('Town').'</td><td>';
	print $staticImmoproperty->town;
	print '</td>';
	print '</tr>';
	
	foreach($object->fields as $key => $val)
	{
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field
		if (in_array($key, array('ref','status'))) continue;	// Ref and status are already in dol_banner

		$value=$object->$key;
		
		print '<tr><td';
		print ' class="titlefield';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '"';
		print '>'.$langs->trans($val['label']).'</td>';
		print '<td>';		
		print $object->showOutputField($val, $key, $value, '', '', '', 0);
		//print dol_escape_htmltag($object->$key, 1, 1);
		print '</td>';
		print '</tr>';

		if (! empty($keyforbreak) && $key == $keyforbreak) break;						// key used for break on second column
	}
	print '</table>';
	print '</div>';
	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">';

	$alreadyoutput = 1;
	foreach($object->fields as $key => $val)
	{
		if ($alreadyoutput)
		{
			if (! empty($keyforbreak) && $key == $keyforbreak) $alreadyoutput = 0;		// key used for break on second column
			continue;
		}

		if (abs($val['visible']) != 1) continue;	// Discard such field from form
		if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field
		if (in_array($key, array('ref','status'))) continue;	// Ref and status are already in dol_banner

		$value=$object->$key;
		if ($object->country_id)
		{
			include_once(DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php');
			$tmparray=getCountry($object->country_id,'all');
			$object->country_code=$tmparray['code'];
			$object->country=$tmparray['label'];
		}

		print '<tr><td';
		print ' class="titlefield';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['label'] == 'BirthCountry') 
		{ 
			print '<tr><td width="25%">'.$langs->trans('Country').'</td><td>';
			print $object->country;
		}
		else
		{
			if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
			print '"';
			print '>'.$langs->trans($val['label']).'</td>';
			print '<td>';
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}
		//print dol_escape_htmltag($object->$key, 1, 1);
		print '</td>';
		print '</tr>';
	}

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
           // print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

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
	
	 /*
     * List of rents
     */
    if ($action != 'create')
    {
        $sql = "SELECT rr.rowid, rr.firstname, rr.lastname, rr.societe,";
        $sql.= " c.rowid as crowid, c.totalamount,";
        $sql.= " c.date_creation as datec,";
        $sql.= " c.date_start as dateh,";
        $sql.= " c.date_end as datef,";
        $sql.= " c.fk_bank,";
        $sql.= " b.rowid as bid,";
        $sql.= " ba.rowid as baid, ba.label, ba.bank, ba.ref, ba.account_number, ba.fk_accountancy_journal, ba.number";
        $sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immorenter as rr, ".MAIN_DB_PREFIX."ultimateimmo_immorent as c";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON c.fk_bank = b.rowid";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account as ba ON b.fk_account = ba.rowid";
        $sql.= " WHERE rr.rowid = c.fk_renter AND rr.rowid=".$id;
		$sql.= $db->order($sortfield, $sortorder);
		
        $result = $db->query($sql);
        if ($result)
        {
            $locationstatic=new ImmoRent($db);
			$locationstatic->fetch($id);

            $num = $db->num_rows($result);
            $i = 0;

            print '<table class="noborder" width="100%">'."\n";

            print '<tr class="liste_titre">';
            print_liste_field_titre('Ref',$_SERVER["PHP_SELF"],'c.rowid','',$param,'',$sortfield,$sortorder);
            print '<td align="center">'.$langs->trans("DateCreation").'</td>';
            print '<td align="center">'.$langs->trans("DateStart").'</td>';
            print '<td align="center">'.$langs->trans("DateEnd").'</td>';
            print '<td align="right">'.$langs->trans("Amount").'</td>';
            if (! empty($conf->banque->enabled))
            {
                print '<td align="right">'.$langs->trans("Account").'</td>';
            }
            print "</tr>\n";

            $accountstatic=new Account($db);

            while ($i < $num)
            {
                $objp = $db->fetch_object($result);
               
                $locationstatic->id=$objp->crowid;
				$locationstatic->ref=$objp->crowid;

                print '<tr class="oddeven">';
                print '<td>'.$locationstatic->getNomUrl(1).'</td>';
                print '<td align="center">'.dol_print_date($db->jdate($objp->datec),'dayhour')."</td>\n";
                print '<td align="center">'.dol_print_date($db->jdate($objp->dateh),'day')."</td>\n";
                print '<td align="center">'.dol_print_date($db->jdate($objp->datef),'day')."</td>\n";
                print '<td align="right">'.price($objp->totalamount).'</td>';
				
				if (! empty($conf->banque->enabled))
				{
					print '<td align="right">';
					if ($objp->bid)
					{
						$accountstatic->label=$objp->label;
						$accountstatic->id=$objp->baid;
						$accountstatic->number=$objp->number;
						$accountstatic->account_number=$objp->account_number;

						if (! empty($conf->accounting->enabled))
						{
							$accountingjournal = new AccountingJournal($db);
							$accountingjournal->fetch($objp->fk_accountancy_journal);

							$accountstatic->accountancy_journal = $accountingjournal->getNomUrl(0,1,1,'',1);
						}

                        $accountstatic->ref=$objp->ref;
                        print $accountstatic->getNomUrl(1);
                    }
                    else
                    {
                        print '&nbsp;';
                    }
                    print '</td>';
                }
                print "</tr>";
                $i++;
            }
            print "</table>";
        }
        else
        {
            dol_print_error($db);
        }
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
	    $relativepath = '/rent/' . dol_sanitizeFileName($object->ref).'/';	
	    $filedir = $conf->ultimateimmo->dir_output . $relativepath;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->ultimateimmo->read;	// If you can read, you can build the PDF to read content
	    $delallowed = $user->rights->ultimateimmo->write;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, 0, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);

	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array('immorent'));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/ultimateimmo/rent/immorent_info.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, 'immorent', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

	    print '</div></div></div>';
	}*/

	//Select mail models is same action as presend
	
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail='immorent';
	$defaulttopic='InformationMessage';
	$diroutput = $conf->ultimateimmo->dir_output.'/rent';
	$trackid = 'immo'.$object->id;

	//include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
	 
}


// End of page
llxFooter();
$db->close();
