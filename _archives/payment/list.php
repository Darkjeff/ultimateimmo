<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015      Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file    immobilier/receipt/list.php
 * \ingroup immobilier
 * \brief   List of rent
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
dol_include_once ( "/immobilier/class/receipt.class.php" );
dol_include_once ( "/immobilier/class/immorenter.class.php" );
dol_include_once ( "/immobilier/class/immoproperty.class.php" );

// Securite acces client
if ($user->societe_id > 0)
accessforbidden ();

// filtre
$sortfield = GETPOST ( "sortfield", 'alpha' );
$sortorder = GETPOST ( "sortorder", 'alpha' );
$page = GETPOST ( "page" );
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
if (! $sortfield)
	$sortfield = "lo.echeance";
if (! $sortorder)
	$sortorder = "DESC";
$offset = $limit * $page;

$filtre = GETPOST ( "filtre" );

// view
$form = new Form ( $db );
$loyer_static = new Receipt ( $db );
$form_loyer = new Receipt ( $db );

$action = GETPOST ( 'action', 'alpha' );

// validateRent

// write income

if ($action == 'validaterent') {
	
	$error = 0;
	
	$db->begin ();
	
	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt as lo ";
	$sql1 .= " SET lo.paiepartiel=";
	$sql1 .= "(SELECT SUM(p.montant)";
	$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_paie as p";
	$sql1 .= " WHERE lo.rowid = p.loyer_id";
	$sql1 .= " GROUP BY p.loyer_id )";
	
	//dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
	$resql1 = $db->query ( $sql1 );
	if (! $resql1) {
		$error ++;
		setEventMessage ( $db->lasterror (), 'errors' );
	} else {
		
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
		$sql1 .= " SET paye=1";
		$sql1 .= " WHERE amount_total=paiepartiel";
		
	//	dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
		$resql1 = $db->query ( $sql1 );
		if (! $resql1) {
			$error ++;
			setEventMessage ( $db->lasterror (), 'errors' );
		}
		
		if (! $error) {
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
			$sql1 .= " SET balance=amount_total-paiepartiel";
			
		//	dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
			$resql1 = $db->query ( $sql1 );
			if (! $resql1) {
				$error ++;
				setEventMessage ( $db->lasterror (), 'errors' );
			}
			
			if (! $error) {		
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_contrat as ic";
			$sql1 .= " SET ic.encours=";
			$sql1 .= "(SELECT SUM(il.solde)";
			$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
	    $sql1 .= " WHERE ic.rowid = il.fk_contrat";
	    $sql1 .= " GROUP BY il.fk_contrat )";
	    
	    	if (! $error) {		
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_renter as ilo";
			$sql1 .= " SET ilo.solde=";
			$sql1 .= "(SELECT SUM(il.solde)";
			$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
	    $sql1 .= " WHERE ilo.rowid = il.fk_renter";
	    $sql1 .= " GROUP BY il.locataire_id )";
			
		//	dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
			$resql1 = $db->query ( $sql1 );
			if (! $resql1) {
				$error ++;
				setEventMessage ( $db->lasterror (), 'errors' );
			}
						
				$db->commit ();
				
				setEventMessage ( 'Loyer mis a jour avec succes', 'mesgs' );
			} else {
				$db->rollback ();
				setEventMessage ( $db->lasterror (), 'errors' );
			}
		} else {
			$db->rollback ();
			setEventMessage ( $db->lasterror (), 'errors' );
		} 
}
}
}

llxHeader ( "", "", 'Immobilier' );

/*
 * Receipt
 */

$sql = "SELECT lo.rowid as reference, lo.fk_contract as idcontrat, lo.fk_property as idlocal , lo.name as loyer, lo.fk_renter as idlocataire, lo.amount_total as montant_tot, lo.echeance as echeance, lo.statut as statut, lo.paiepartiel as income, lo.paye,";
$sql .= " lc.rowid as lcid, lc.nom as nomlocataire, ll.rowid as llid, ll.name as nomlocal";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as lo";
$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as lc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll";
$sql .= " WHERE lo.fk_renter = lc.rowid AND lo.fk_property = ll.rowid";
if (GETPOST ( "search_label" ))
	$sql .= " AND lo.nom LIKE '%" . GETPOST ( "search_label" ) . "%'";
if (GETPOST ( "search_locataire" ))
	$sql .= " AND lc.nom LIKE '%" . GETPOST ( "search_locataire" ) . "%'";
if (GETPOST ( "search_local" ))
	$sql .= " AND ll.nom LIKE '%" . GETPOST ( "search_local" ) . "%'";
if ($user->id != 1) {
	$sql .= " AND lo.proprietaire_id=".$user->id;
}	
	
$sql .= " ORDER BY lo.echeance DESC " . $db->plimit ( $limit + 1, $offset );

// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql=" . $sql, LOG_DEBUG );
$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	$var = true;
	
	$param = '';
	
	print_barre_liste($langs->trans("Receipt"), $page, $_SERVER["PHP_SELF"],$param,$sortfield,$sortorder,'',$num,$nbtotalofrecords);
	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
	
	print '<table class="noborder" width="100%">';
	print "<tr class=\"liste_titre\">";
	print_liste_field_titre ( $langs->trans ( "Ref" ),$_SERVER["PHP_SELF"], "reference", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Locataire" ),$_SERVER["PHP_SELF"], "idlocataire", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Local" ),$_SERVER["PHP_SELF"], "idlocal", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Loyer" ),$_SERVER["PHP_SELF"], "nomlocal", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Echeance" ),$_SERVER["PHP_SELF"], "echeance", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Montant total" ),$_SERVER["PHP_SELF"], "montant_tot", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "re&ccedilu" ),$_SERVER["PHP_SELF"], "income", "", $param, "", $sortfield, $sortorder );

	print_liste_field_titre ( $langs->trans ( "Paiement" ),$_SERVER["PHP_SELF"], "statut", "", $param, "", $sortfield, $sortorder );
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	
	print '<tr class="liste_titre">';
	print '<td class="liste_titre">&nbsp;</td>';
	// nom locataire
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_locataire" value="' . GETPOST ( "search_locataire" ) . '"></td>';
	// nom local
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_local" value="' . GETPOST ( "search_local" ) . '"></td>';
	
	// nom loyer
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_label" value="' . GETPOST ( "search_label" ) . '"></td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre">&nbsp;</td>';

	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" name="button_search" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
	print '</td>';
	print "</tr>\n";
	
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		
		// ref
		print '<td width="60">';
		$loyer_static->id = $objp->reference;
		$loyer_static->nom = $objp->reference;
		$loyer_static->ref = $objp->reference;
		print $loyer_static->getNomUrl ( 1, '20' );
		print '</td>';
		
		print '<td>' . stripslashes ( nl2br ( $objp->nomlocataire ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->nomlocal ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->loyer ) ) . '</td>';
		
		// due date
		
		print '<td width="110" align="left">' . dol_print_date ( $db->jdate ( $objp->echeance ), 'day' ) . '</td>';
		
		// amount
		
		print '<td align="left" width="100">' . price ( $objp->montant_tot ) . '</td>';
		print '<td align="left" width="100">' . price ( $objp->income ) . '</td>';
	
		
		// Affiche statut de la facture
		print '<td align="right" nowrap="nowrap">';
		print $loyer_static->LibStatut ( $objp->paye, 5 );
		print "</td>";
		
		print '<td align="right"><a href="./card.php?action=update&id=' . $objp->reference . '">';
		print img_edit ();
		print '</a></td>';
		
		print "</tr>";
		$i ++;
	}
	print "</table>";
	
	print '<div align="right">';
	print '<a class="butAction" href="./card.php?action=create">'.$langs->trans("Addnewrent").'</a>';
	print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=validaterent">' . $langs->trans ( "ValidateRent" ) . '</a>';
	print '</div>';

	print '</form>';
} else {
	print $db->error ();
}

llxFooter();

$db->close();
