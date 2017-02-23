<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015      Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 * \file immobilier/receipt/list.php
 * \ingroup immobilier
 * \brief List of rent
 */

	
// Class
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/class/renter.class.php");
dol_include_once("/immobilier/class/immoproperty.class.php");
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once '../class/html.formimmobilier.class.php';
dol_include_once('/immobilier/class/immorent.class.php');
require_once '../class/immoproperty.class.php';

// Langs

$action = GETPOST('action', 'alpha');
$mesg = '';
$action = GETPOST('action');
$massaction=GETPOST('massaction','alpha');
$cancel = GETPOST('cancel');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');
$search_renter = trim(GETPOST('search_renter'));
$search_property = trim(GETPOST('search_property'));
$search_rent = trim(GETPOST('search_rent'));
// Security check
if ($user->societe_id > 0) accessforbidden();
// Load variable for pagination	
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield)
	$sortfield = "t.echeance";
if (! $sortorder)
	$sortorder = "DESC";
$arrayfields=array(
	't.rowid'=>array('label'=>$langs->trans("Reference"), 'checked'=>1),
    'lc.nom'=>array('label'=>$langs->trans("Renter"), 'checked'=>1),
    'll.name'=>array('label'=>$langs->trans("Property"), 'checked'=>1),
	't.name'=>array('label'=>$langs->trans("Receipt"), 'checked'=>1),
    't.echeance'=>array('label'=>$langs->trans("Echeance"), 'checked'=>1),
    't.amount_total'=>array('label'=>$langs->trans("amount_total"), 'checked'=>1),
    't.paiepartiel'=>array('label'=>$langs->trans("income"), 'checked'=>1),
    //'t.charges'=>array('label'=>$langs->trans("charges"), 'checked'=>0),
    //'t.vat'=>array('label'=>$langs->trans("vat"), 'checked'=>0),
    't.paye'=>array('label'=>$langs->trans("paye"), 'checked'=>1),
	'soc.nom'=>array('label'=>$langs->trans("Owner"), 'checked'=>1)
);
/*
 * Actions
 */

if (GETPOST('cancel')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction')) { $massaction=''; }
$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if (empty($reshook))
{
    if (! empty($cancel)) $action = '';
    
    include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';
    
    if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") ||GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
    {
    $search_renter = "";
	$search_property = "";
	$search_rent = "";
	$search_array_options=array();
    }
    }

if ($action == 'validaterent') {
	
	$error = 0;
	
	$db->begin();
	
	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt as lo ";
	$sql1 .= " SET lo.paiepartiel=";
	$sql1 .= "(SELECT SUM(p.amount)";
	$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_payment as p";
	$sql1 .= " WHERE lo.rowid = p.fk_receipt";
	$sql1 .= " GROUP BY p.fk_receipt )";
	
	// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		setEventMessage($db->lasterror(), 'errors');
	} else {
		
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
		$sql1 .= " SET paye=1";
		$sql1 .= " WHERE amount_total=paiepartiel";
		
		// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
		$resql1 = $db->query($sql1);
		if (! $resql1) {
			$error ++;
			setEventMessage($db->lasterror(), 'errors');
		}
		
		if (! $error) {
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
			$sql1 .= " SET balance=amount_total-paiepartiel";
			
			// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
			$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
			
			if (! $error) {
				$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_contrat as ic";
				$sql1 .= " SET ic.encours=";
				$sql1 .= "(SELECT SUM(il.balance)";
				$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
				$sql1 .= " WHERE ic.rowid = il.fk_contract";
				$sql1 .= " GROUP BY il.fk_contract )";
				
				$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
				
				$db->commit();
				
				setEventMessage('Loyer mis a jour avec succes', 'mesgs');
			}
		} else {
			$db->rollback();
			setEventMessage($db->lasterror(), 'errors');
		}
	}
}

if ($action == 'delete') {
$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans('DeleteReceipt'), $langs->trans('ConfirmDeleteReceipt'), 'confirm_delete', '', 0, 1);
	print $formconfirm;
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
 * View
 */

$form=new Form($db);
$object = new Immoreceipt($db);
//$form_loyer = new Immoreceipt($db);

llxHeader('', $langs->trans("Receipt"));



	$sql = "SELECT t.rowid as receipt_id, t.fk_contract, t.fk_property, t.name , t.fk_renter, t.amount_total as amount_total, t.rent as rent, t.balance,";
	$sql .= " t.paiepartiel as paiepartiel, t.charges, t.vat, t.echeance as echeance, t.commentaire, t.statut as receipt_statut, t.date_rent,";
	$sql .= " t.date_start, t.date_end, t.fk_owner, t.paye as paye, lc.rowid as renter_id, lc.nom as nomlocataire, lc.prenom as prenomlocataire,";
	$sql .= " ll.name as nomlocal, ll.rowid as property_id, soc.rowid as soc_id, soc.nom as owner_name";
	$sql .= ' FROM llx_immo_receipt as t';
	$sql .= ' INNER JOIN llx_immo_renter as lc ON t.fk_renter = lc.rowid';
	$sql .= ' INNER JOIN llx_immo_property as ll ON t.fk_property = ll.rowid';
	$sql .= ' INNER JOIN llx_societe as soc ON soc.rowid = t.fk_owner';
	if ($search_renter)			$sql .= natural_search("lc.nom", $search_renter);
	if ($search_property)		$sql .= natural_search("ll.name", $search_property);
	if ($search_rent)			$sql .= natural_search("t.name", $search_rent);
	$sql .= $db->order($sortfield, $sortorder);
// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
}	
$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
    $params='';
    
    if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
	if ($search_renter) $params.= '&amp;search_renter='.urlencode($search_renter);
	if ($search_property) $params.= '&amp;search_property='.urlencode($search_property);
	if ($search_rent) $params.= '&amp;search_rent='.urlencode($search_rent);
    if ($optioncss) $param.='&optioncss='.$optioncss;
	print_barre_liste($langs->trans('ListReceipt'), $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_receipt');
	
	$i = 0;
    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
    
	print '<br><br>';
	
	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
    $selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
	
    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))	print_liste_field_titre($arrayfields['t.rowid']['label'], $_SERVER["PHP_SELF"],"t.rowid","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['lc.nom']['checked']))			print_liste_field_titre($arrayfields['lc.nom']['label'], $_SERVER["PHP_SELF"],"lc.nom","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['ll.name']['checked']))	print_liste_field_titre($arrayfields['ll.name']['label'], $_SERVER["PHP_SELF"],"ll.name", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['t.name']['checked']))		print_liste_field_titre($arrayfields['t.name']['label'],$_SERVER["PHP_SELF"],'t.name','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.echeance']['checked']))		print_liste_field_titre($arrayfields['t.echeance']['label'],$_SERVER["PHP_SELF"],'t.echeance','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.amount_total']['checked']))			print_liste_field_titre($arrayfields['t.amount_total']['label'],$_SERVER["PHP_SELF"],'t.amount_total','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.paiepartiel']['checked']))			print_liste_field_titre($arrayfields['t.paiepartiel']['label'],$_SERVER["PHP_SELF"],'t.paiepartiel','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.paye']['checked']))			print_liste_field_titre($arrayfields['t.paye']['label'],$_SERVER["PHP_SELF"],'t.paye','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['soc.nom']['checked']))			print_liste_field_titre($arrayfields['soc.nom']['label'],$_SERVER["PHP_SELF"],'soc.nom','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";
	// Line for search fields
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['lc.nom']['checked']))			print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_renter" value="' .$search_renter. '"></td>';
	if (! empty($arrayfields['ll.name']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_property" value="' .$search_property. '"></td>';
	if (! empty($arrayfields['t.name']['checked']))			print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_rent" value="' .$search_rent. '"></td>';
	if (! empty($arrayfields['t.echeance']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.amount_total']['checked']))	print  '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.paiepartiel']['checked']))	print  '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.paye']['checked']))			print  '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['soc.nom']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	print '<td align="right" colspan="2" class="liste_titre">';
	$searchpicto=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpicto;
	print '</td>';
	print '</tr>';
	

	
	$var = false;
	
	$receiptstatic = new Immoreceipt($db);
	
	$thirdparty_static = new Societe($db);
	
	 $contrat = new Rent($db);
	 
	 $propertystatic = new Immoproperty($db);
	
	while ( $i < min($num, $limit) ) 
	{
		$obj = $db->fetch_object($resql);
			
			$receiptstatic->id = $obj->receipt_id;
			$receiptstatic->name = $obj->name;
			
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			print '<td>' . $receiptstatic->getNomUrl(1) . '</td>';
			//need to debug in order to show pdf
			if (is_file($conf->immobilier->dir_output . '/quittance_' . $id . '.pdf')) {
			print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=immobilier&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
			print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
			}
			
			print '<td align="left" style="' . $code_statut . '">';
			print '<a href="../renter/card.php?id=' . $obj->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($obj->nomlocataire) . ' ' . ucfirst($obj->nomlocataire) . '</a>';		
			print '</td>';
			
			$propertystatic->id = $obj->property_id;
			$propertystatic->name = stripslashes(nl2br($obj->nomlocal));
			print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			print '<td>' . stripslashes(nl2br($obj->name)) . '</td>';
	// due date
		
		print '<td>' . dol_print_date($obj->echeance, 'day') . '</td>';
		
		// amount
		
		print '<td align="left" width="100">' . price($obj->amount_total) . '</td>';
		print '<td align="left" width="100">' . price($obj->paiepartiel) . '</td>';
		
		// Affiche statut de la facture
		print '<td align="right" nowrap="nowrap">';
		print $receiptstatic->LibStatut($obj->paye, 5);
		print "</td>";
		
		$thirdparty_static->id=$obj->fk_owner;
		$thirdparty_static->name=$obj->owner_name;
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
			
			
			
			print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $obj->id . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>' . "\n";
			
			print "</tr>\n";
			
			$i ++;
		}
	print "</table>";
	print "</div>";
	print '</form>';
} else {
	dol_print_error($db);
}

llxFooter();
$db->close();