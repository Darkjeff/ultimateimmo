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

require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');
dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/lib/immorent.lib.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'myobjectcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

// Initialize technical objects
$object = new ImmoRent($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->ultimateimmo->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('immorentcard', 'globalcard'));     // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val)
{
    if (GETPOST('search_'.$key,'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Include fetch and fetch_thirdparty but not fetch_optionals

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'mymodule', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

$permissiontoread = $user->rights->ultimateimmo->rent->read;
$permissiontoadd = $user->rights->ultimateimmo->rent->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->ultimateimmo->rent->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->ultimateimmo->rent->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->ultimateimmo->rent->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->ultimateimmo->multidir_output[isset($object->entity) ? $object->entity : 1];


/*
 * Actions
 *
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error = 0;
	
	/**
	 * Action generate bail
	 */
	if ($action == 'bail_vide') 
	{
		// Define output language
		$outputlangs = $langs;
		
		$file = 'bail_vide_' . $id . '.pdf';
		
		$result = bail_vide_pdf_create($db, $id, '', 'bail_vide', $outputlangs, $file);
		
		if ($result > 0) 
		{
			Header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id);
			exit();
		} 
		else 
		{
			setEventMessages($langs->trans("ErrorFieldRequired"), null, 'errors');
		}
	}

	$permissiontoadd = $user->rights->ultimateimmo->write;
	$permissiontodelete = $user->rights->ultimateimmo->delete;
	$backurlforlist = dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
    	if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
    		if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
    		else $backtopage = dol_buildpath('/ultimateimmo/rent/immorent_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
    	}
    }
    $triggermodname = 'ULTIMATEIMMO_IMMORENT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update or delete
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';
	
	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';
	
	// Build doc
	if ($action == 'builddoc' && $permissiontoadd)
	{
		// Save last template used to generate document
		if (GETPOST('model')) $object->setDocModel($user, GETPOST('model', 'alpha'));

		$outputlangs = $langs;
		if (GETPOST('lang_id', 'aZ09'))
		{
			$outputlangs = new Translate("",$conf);
			$outputlangs->setDefaultLang(GETPOST('lang_id', 'aZ09'));
		}
		$result = $object->generateDocument($object->model_pdf, $outputlangs);
		if ($result <= 0)
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action='';
		}
	}

	// Actions to send emails
	$triggersendname = 'IMMORENT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_IMMORENT_TO';
	$trackid = 'immorent'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader('', $langs->trans("ImmoRents"), '');

// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->transnoentitiesnoconv("MenuNewImmoRent"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

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
	print load_fiche_titre($langs->trans("ImmoRents"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

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
    $res = $object->fetch_optionals();

	$head = immorentPrepareHead($object);
	dol_fiche_head($head, 'card', $langs->trans("ImmoRents"), -1, 'ultimateimmo@ultimateimmo');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoRent'), $langs->trans('ConfirmDeleteImmoRent'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') 
	{
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneImmoRent', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
	    $formquestion = array();
	    /*
	        $formquestion = array(
	            // 'text' => $langs->trans("ConfirmClone"),
	            // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
	            // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
	            // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1)));*/
	    
	    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/ultimateimmo/rent/immorent_list.php',1) . '?restore_lastsearch_values=0' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';
	
	$morehtmlref='<div class="refidno">';
	// Ref renter
	$staticImmorenter = new ImmoRenter($db);
	$staticImmorenter->fetch($object->fk_renter);
	$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $staticImmorenter->ref, $object, $permissiontoadd, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $staticImmorenter->ref, $object, $permissiontoadd, 'string', '', null, null, '', 1);
	// Thirdparty
	$company = new Societe($db);
	if ($object->fk_soc)
	{
		$result = $company->fetch($object->fk_soc);
	}
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $company->getNomUrl(1, 'renter');	
	if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $staticImmorenter->fk_soc > 0) $morehtmlref.=' (<a href="'.dol_buildpath('/ultimateimmo/rent/immorent_list.php',1).'?socid='.$staticImmorenter->fk_soc.'&search_fk_soc='.urlencode($staticImmorenter->fk_soc).'">'.$langs->trans("OtherRents").'</a>)';
	$morehtmlref.='</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Common attributes
	$keyforbreak='rentamount';
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	dol_fiche_end();

	/*
	 * Lines
	 */

	if (!empty($object->table_element_line))
	{
    	// Show object lines
    	$result = $object->getLinesArray();

    	print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
    	<input type="hidden" name="token" value="' . $_SESSION ['newtoken'].'">
    	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
    	<input type="hidden" name="mode" value="">
    	<input type="hidden" name="id" value="' . $object->id.'">
    	';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) 
		{
    	    include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
    	}

    	print '<div class="div-table-responsive-no-min">';
    	if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
    	{
    	    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    	}

    	if (!empty($object->lines))
    	{
    		$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
    	}

    	// Form to add new line
    	if ($object->status == 0 && $permissiontoadd && $action != 'selectlines')
    	{
    	    if ($action != 'editline')
    	    {
    	        // Add products/services form
    	        $object->formAddObjectLine(1, $mysoc, $soc);

    	        $parameters = array();
    	        $reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    	    }
    	}

    	if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
    	{
    	    print '</table>';
    	}
    	print '</div>';

    	print "</form>\n";
	}
	
	/*if (is_file($conf->ultimateimmo->dir_output . '/rent/bail_vide' . $id . '.pdf'))
	{
		print '&nbsp';
		print '<table class="border" width="100%">';
		print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("LinkedDocuments") . '</td></tr>';
		// afficher
		$legende = $langs->trans("Ouvrir");
		print '<tr><td width="200" class="center">' . $langs->trans("EmptyHousing") . '</td><td> ';
		print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=ultimateimmo&file=rent/bail_vide' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
		print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" class="absmiddle" hspace="2px" ></a>';
		print '</td></tr></table>';
	}*/

	print '</div>';


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') 
	{
    	print '<div class="tabsAction">'."\n";
    	$parameters = array();
    	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
    	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

    	if (empty($reshook))
    	{
    	    // Send
            print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

    		if ($permissiontoadd)
    		{
    			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
    		}
    		else
    		{
    			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
    		}

    		// Clone
    		if ($permissiontoadd)
    		{
    			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&socid='.$object->socid.'&action=clone&object=myobject">'.$langs->trans("ToClone").'</a>'."\n";
    		}

    		if ($permissiontodelete)
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
	if (GETPOST('modelselected')) 
	{
	    $action = 'presend';
	}

	if ($action != 'presend')
	{
	    print '<div class="fichecenter"><div class="fichehalfleft">';
	    print '<a name="builddoc"></a>'; // ancre

	    // Documents
	    $relativepath = '/rent/' . dol_sanitizeFileName($object->ref).'/';	
	    $filedir = $conf->ultimateimmo->dir_output . $relativepath;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $permissiontoread;	// If you can read, you can build the PDF to read content
	    $delallowed = $permissiontodelete;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 48, 0, '', '', '', $soc->default_lang, '', $object);

	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array('immorent'));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/ultimateimmo/rent/immorent_agenda.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, $object->element, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

	    print '</div></div></div>';
	}

	//Select mail models is same action as presend
	
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'immorent';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->ultimateimmo->dir_output.'/rent';
	$trackid = 'immo'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
	 
}

// End of page
llxFooter();
$db->close();
