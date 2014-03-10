<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   \file       htdocs/product/index.php
 *   \ingroup    product
 *   \brief      Page accueil des produits et services
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';
require_once (DOL_DOCUMENT_ROOT."/gestimmo/class/logement.class.php");
// Security check
if (!$user->rights->produit->lire && !$user->rights->service->lire) accessforbidden();


/*bubou%
 * View
 */

llxHeader("","",$langs->trans("Biens en locations"));

print_fiche_titre($langs->trans("Biens pour locations"));

print '<table border="0" width="100%">';

print '<tr><td valign="top" width="30%">';

/*
 * Zone recherche biens
 */
print '<form method="post" action="'.DOL_URL_ROOT.'/gestimmo/biens/liste.php"">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<table class="noborder nohover" width="100%">';
print "<tr class=\"liste_titre\">\n";
print '<td colspan="3">'.$langs->trans("Search").'</td></tr>';
print "<tr ".$bc[false]."><td>";
print $langs->trans("Ref").' :</td><td><input class="flat" type="text" size="20" name="sf_ref"></td><td><input type="submit" value="'.$langs->trans("Search").'" class="button"></td></tr>';
print "<tr ".$bc[false]."><td>";
print $langs->trans("Label").' :</td><td><input class="flat" type="text" size="20" name="snom"></td><td><input class="button" type="submit" value="'.$langs->trans("Search").'"></td></tr>';
print "</table></form><br>\n";

/*
 * Nombre de biens
 */
$prodser = array();
$sql = "SELECT count(*), p.rowid";
$sql.= " FROM ".MAIN_DB_PREFIX."logement as p";
//$sql.= " WHERE p.entity IN (".getEntity('product', 1).")";
$sql.= " GROUP BY p.rowid";
$resql=$db->query($sql);
if ($resql)
{
  $num = $db->num_rows($resql);
  $i = 0;
  while ($i < $num)
    {
      $row = $db->fetch_row($resql);
      $prodser[$row[1]] = $row[0];
      $i++;
    }
  $db->free($resql);
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("Statistics").'</td></tr>';
//if (! empty($conf->product->enabled))
{
    print "<tr $bc[0]>";
    print '<td><a href="liste.php?type=0">'.$langs->trans("Location années").'</a></td><td>'.round($num).'</td>';
    print "</tr>";
}
/*
if (! empty($conf->service->enabled))
{
    print "<tr $bc[1]>";
    print '<td><a href="liste.php?type=1">'.$langs->trans("location estivals").'</a></td><td>'.round($prodser[1]).'</td>';
    print "</tr>";
}
 */
print '</table>';

print '</td><td valign="top" width="70%">';


/*
 * Derniers bien enregistrer 
 */
$sql = "SELECT p.rowid,p.ref, p.nb_piece, p.loyer, p.adresse, p.town";
$sql.= " FROM ".MAIN_DB_PREFIX."logement as p ";
//$sql.= " WHERE p.entity IN (".getEntity('product', 1).")";
//$sql.= " AND p.fk_product_type <> 1";
$sql.= " ORDER BY p.datec DESC ";
$sql.= $db->plimit(15, 0);

$resql = $db->query($sql);

if ($resql)
{
  $num = $db->num_rows($resql);

  $i = 0;

  if ($num > 0)
    {
      print '<table class="noborder" width="100%">';

      print '<tr class="liste_titre"><td colspan="5">'.$langs->trans("dernier biens").'</td></tr>';

      $var=True;
      while ($i < $num)
	{
	  $objp = $db->fetch_object($resql);
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  print "<td><a href=\"fiche.php?id=$objp->rowid\">";
	  if ($objp->fk_product_type==1) print img_object($langs->trans("ShowService"),"service");
	  else
           print img_object($langs->trans("Showbiens"),"product");
	  print "</a> <a href=\"fiche.php?id=$objp->rowid\">$objp->ref</a></td>\n";
	  print "<td>$objp->nb_piece pieces</td>";
      $loyer=price2num($objp->loyer,'MU');
      print "<td>$loyer €</td>";
      print "<td>$objp->town</td>";
	  print "<td>";
	  //if ($objp->fk_product_type==1) print $langs->trans('ShowService');
	  //else print $langs->trans('ShowProduct');
	  print "</td></tr>\n";
	  $i++;
	} 
      $db->free($resql);

      print "</table>";
    }
    
}
else
{
  dol_print_error();
}
// todo derniere location
print '<table class="noborder" width="100%">';
print '<tr> </tr>';
print '<tr class="liste_titre"><td colspan="5">'.$langs->trans("derniere location").'</td></tr>';
print '</td></tr></table>';

$db->close();

llxFooter();
?>
