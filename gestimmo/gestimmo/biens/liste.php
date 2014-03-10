<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010	   Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2012      Christophe Battarel   <christophe.battarel@altairis.fr>
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
 *		\file       htdocs/fourn/product/liste.php
 *		\ingroup    produit
 *		\brief      Page liste des produits ou services
 */

require '../../main.inc.php';
//require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/societe/class/societe.class.php');
//require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.class.php';
dol_include_once('/gestimmo/class/biens_immo.class.php');
dol_include_once('/gestimmo/class/mandat.class.php');
dol_include_once('/gestimmo/class/logement.class.php');
$langs->load("suppliers");
// todo securité
//if (!$user->rights->produit->lire && !$user->rights->service->lire) accessforbidden();

$sref=isset($_GET["sref"])?$_GET["sref"]:$_POST["sref"];
$sRefSupplier=isset($_GET["srefsupplier"])?$_GET["srefsupplier"]:$_POST["srefsupplier"];
$smandat=isset($_GET["smandat"])?$_GET["smandat"]:$_POST["smandat"];
$type=isset($_GET["type"])?$_GET["type"]:$_POST["type"];

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page = $_GET["page"];
if ($page < 0) {
$page = 0 ; }

$limit = $conf->liste_limit;
$offset = $limit * $page ;

if (! $sortfield) $sortfield="p.ref";
if (! $sortorder) $sortorder="DESC";

if (! empty($_POST["button_removefilter"]))
{
	$sref="";
	$sRefSupplier="";
	$snom="";
}



if (isset($_REQUEST['catid']))
{
	$catid = $_REQUEST['catid'];
}



/*
* Mode Liste
*
*/

$mandatstatic = new Mandat($db);
$companystatic = new Societe($db);
$biensstatic = new Logement($db);
$title=$langs->trans("Biens");


$sql = "SELECT p.rowid, p.nb_piece,p.adresse, p.descriptif, p.loyer,p.fk_mandat,p.ref,p.town";
$sql.= ", ppf.rowid , ppf.ref_interne";
//$sql.= " s.rowid as fk_mandat, s.ref_interne";
$sql.= " FROM ".MAIN_DB_PREFIX."logement as p";
if ($catid) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."categorie_product as cp ON cp.fk_product = p.rowid";
$sql.=" LEFT JOIN " .MAIN_DB_PREFIX."mandat as ppf ON p.fk_mandat=ppf.rowid";
//$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON ppf.fk_soc = s.rowid";
$sql.= " WHERE  p.entity IN (".getEntity('gestimmo', 1).")";
//

if ($_POST["mode"] == 'search')
{
	$sql .= " AND (p.ref LIKE '%".$_POST["sall"]."%'";
	$sql .= " OR p.label LIKE '%".$_POST["sall"]."%')";
	if ($sRefSupplier)
	{
		$sql .= " AND ppf.ref_interne LIKE '%".$sRefSupplier."%'";
	}
}
else
{
	//if ($_GET["type"] || $_POST["type"])
	//{
	//	$sql .= " AND p.fk_product_type = ".(isset($_GET["type"])?$_GET["type"]:$_POST["type"]);
	//}
	if ($sref)
	{
		$sql .= " AND p.ref LIKE '%".$sref."%'";
	}
//	if ($sRefSupplier)
//	{
	//	$sql .= " AND ppf.ref_fourn LIKE '%".$sRefSupplier."%'";
	//}
//	if ($snom)
	//{
	//	$sql .= " AND p.label LIKE '%".$snom."%'";
	//}
	if($catid)
	{
		$sql .= " AND cp.fk_categorie = ".$catid;
	}
}
 
 
if ($fourn_id > 0)
{
	$sql .= " AND ppf.fk_soc = ".$fourn_id;
}
$sql .= " ORDER BY ".$sortfield." ".$sortorder;
$sql .= $db->plimit($limit + 1, $offset);


dol_syslog("proprio: sql=".$sql);

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);

	$i = 0;

	if ($num == 1 && ( isset($_POST["sall"]) ||  $sref ) )
	{
		$objp = $db->fetch_object($resql);
		header("Location: fiche.php?id=".$objp->rowid);
		exit;
	}

	
	 $texte = $langs->trans("Liste des biens ");

	llxHeader("","",$texte);


	$param="&sref=$sref&snom=$snom".(isset($type)?"&amp;type=$type":"");
	print_barre_liste($texte, $page, "liste.php", $param, $sortfield, $sortorder,'',$num);


	if (isset($catid))
	{
		print "<div id='ways'>";
		$c = new Categorie($db, $catid);
		$ways = $c->print_all_ways(' &gt; ','fourn/product/liste.php');
		print " &gt; ".$ways[0]."<br>\n";
		print "</div><br>";
	}


	print '<table class="liste" width="100%">';

	// Lignes des titres
	print "<tr class=\"liste_titre\">";
	print_liste_field_titre($langs->trans("Ref"),"liste.php", "p.ref",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Mandat"),"liste.php", "ppf.ref_interne",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Ville"),"liste.php", "p.town",$param,"","",$sortfield,$sortorder);
	print '<td class="liste_titre" align="left">'.$langs->trans("Type").'</td>';
	//print_liste_field_titre($langs->trans("Supplier"),"liste.php", "pf.fk_soc",$param,"","",$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("BuyingPrice"),"liste.php", "ppf.price",$param,"",'align="right"',$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("QtyMin"),"liste.php", "ppf.qty",$param,"",'align="right"',$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("UnitPrice"),"liste.php", "ppf.unitprice",$param,"",'align="right"',$sortfield,$sortorder);
	print "</tr>\n";

	// Lignes des champs de filtre
	print '<form action="liste.php" method="post">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="type" value="'.$type.'">';
	print '<tr class="liste_titre">';
	print '<td class="liste_titre">';
	print '<input class="flat" type="text" name="sref" value="'.$sref.'" size="12">';
	print '</td>';
	print '<td class="liste_titre">';
	print '<input class="flat" type="text" name="srefsupplier" value="'.$sRefSupplier.'" size="12">';
	print '</td>';
	print '<td class="liste_titre">';
	print '<input class="flat" type="text" name="snom" value="'.$snom.'">';
	print '</td>';
	print '<td class="liste_titre" colspan="4" align="right">';
	print '<input type="image" class="liste_titre" value="button_search" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" value="button_removefilter" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print '</td>';
	print '</tr>';
	print '</form>';

	$oldid = '';
	$var=True;
	while ($i < min($num,$limit))
	{  $titre=" ";
		$objp = $db->fetch_object($resql);
		$var=!$var;
		print "<tr $bc[$var]>";

		//print '<td>';
		$biensstatic->id=$objp->rowid;
		$biensstatic->ref=$objp->adresse;
		$biensstatic->type=$objp->loyer;
        $bienstatic->fk_mandat=$objp->fk_mandat;
		//print $biensstatic->getNomUrl(1,'supplier');
		//Print $objp->rowid;
		//print '<a href="fiche.php?id='.$objp->rowid.'">';
        //print img_object($langs->trans("ShowContract"),"contract").' '.(isset($objp->rowid) ? $objp->rowid : $obj->cid) .'</a>';
		//print '</td>';
        
      print "<td> <a href=\"fiche.php?id=$objp->rowid\">$objp->ref</a></td>";
     //   print '<td>'.$objp->ref.'</td>'."\n";
        $supplier=new Mandat($db);
                  $supplier->fetch($objp->fk_mandat);
                 
               if ($objp->fk_mandat > 0 ) $titre=$supplier->getmandatUrl(1).$titre.$supplier->ref_interne." </br>du :  ".dol_print_date($supplier->date_contrat);
               else $titre="Pas de Mandat de location";
       print '<td>'.$titre.'</td>';
		print '<td>'.$objp->adresse.'</td>';
        print '<td>'.$objp->town.'</td>';

		print '<td> Type '.$objp->nb_piece.'</td>'."\n";

		//$companystatic->nom=$objp->nom;
		//$companystatic->id=$objp->socid;
		//print '<td>'.$companystatic->getNomUrl(1,'supplier').'</td>';

		//print '<td align="right">'.price($objp->loyer).'</td>';

	//	print '<td align="right">'.$objp->qty.'</td>';

		//print '<td align="right">'.price($objp->unitprice).'</td>';

		print "</tr>\n";
		$i++;
	}
	$db->free($resql);

	print "</table>";


}
else
{
	dol_print_error($db);
}


$db->close();

llxFooter();
?>
