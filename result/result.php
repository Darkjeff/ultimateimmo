<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018-2019 Philippe GRAND 		<philippe.grand@atoo-net.com>
 * Copyright (C) 2020      Thomas OURSEL         <contact@ogest.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */

/**
 * \file htdocs/compta/ventilation/index.php
 * \ingroup compta
 * \brief Page accueil ventilation
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


// Class
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");


// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "bills", "other"));

// Filter
$year = $_GET ["year"];
if ($year == 0)
{
	$year_current = strftime ( "%Y", time () );
	$year_start = $year_current;
}
else
{
	$year_current = $year;
	$year_start = $year;
}

/*
 * View
 */
llxHeader ( '', 'Immobilier - Resultat' );

$textprevyear = '<a href="'.dol_buildpath('/ultimateimmo/result/result.php', 1).'?year='.($year_current - 1).'">'.img_previous().'</a>';
$textnextyear = '<a href="'.dol_buildpath('/ultimateimmo/result/result.php', 1).'?year='.($year_current + 1).'">'.img_next().'</a>';

print_fiche_titre ( $langs->trans("Encaissement")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;
$months_list = [];
for($month_num = 1; $month_num <= 12 ; $month_num++)
{
	$months_list[$month_num] = date('F', mktime(0, 0, 0, $month_num, 10));
}

print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td></tr>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Resultat Immo").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)='.$month_num.' then lp.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE lp.date_payment >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.fk_property = ip.rowid AND ib.fk_property = ip.fk_property ";

$sql .= " GROUP BY  ib.label";

$resql = $db->query ( $sql );
if ($resql)
{
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num )
	{
		$row = $db->fetch_row ( $resql );
		$total = 0;

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		foreach( $months_list as $month_num => $month_name )
		{
			print '<td align="right">' . $row [$month_num] . '</td>';
			$total += $row [$month_num];
		}
		print '<td align="right"><b>' . $total . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}

print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td></tr>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Paiement Charge locataire").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)='.$month_num.' then ir.chargesamount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ir.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ir.fk_property = ip.rowid AND ib.fk_property = ip.fk_property ";

$sql .= " GROUP BY  ib.label";

$resql = $db->query ( $sql );
if ($resql)
{
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num )
	{
		$row = $db->fetch_row ( $resql );
		$total = 0;

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		foreach( $months_list as $month_num => $month_name )
		{
			print '<td align="right">' . $row [$month_num] . '</td>';
			$total += $row [$month_num];
		}
		print '<td align="right"><b>' . $total . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

$value_array=array();

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)='.$month_num.' then lp.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE lp.date_payment >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.fk_property = ip.rowid AND ip.fk_property = ib.fk_property";
$sql .= " GROUP BY ib.label";
$sql .= " ORDER BY ib.label";
$resqlencaissement = $db->query ( $sql );

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)='.$month_num.' then ir.chargesamount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ir.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ir.fk_property = ip.rowid AND ip.fk_property = ib.fk_property";

$sql .= " GROUP BY ib.label";

$resqlpaiement = $db->query ( $sql );
if ($resqlpaiement && $resqlencaissement)
{
	$i = 0;
	$num = max($db->num_rows ( $resqlpaiement ), $db->num_rows ( $resqlencaissement ));

	while ( $i < $num )
	{
		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++) {
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Loyer brut encaissé").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val)
{

	$total=0;
	print '<tr class="oddeven"><td>' . $key. '</td>';
	foreach( $months_list as $month_num => $month_name )
	{
		print '<td align="right">' . $val [$month_num] . '</td>';
		$total += $val [$month_num];
	}
	print '<td align="right"><b>' . $total . '</b></td>';
	print '</tr>';
	$i ++;
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Charges Déductibles").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ip.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)='.$month_num.' then ic.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip ON ic.fk_property = ip.rowid";
//$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ic.date_start >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND it.famille = 'Charge déductible' ";

$sql .= " GROUP BY  ip.label";


$resql = $db->query ( $sql );
if ($resql)
{
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num )
	{
		$row = $db->fetch_row ( $resql );
		$total = 0;

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		foreach( $months_list as $month_num => $month_name )
		{
			print '<td align="right">' . $row [$month_num] . '</td>';
			$total += $row [$month_num];
		}
		print '<td align="right"><b>' . $total . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}

print "</table>\n";

$value_array=array();

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)='.$month_num.' then lp.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE lp.date_payment >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.fk_property = ip.rowid AND ip.fk_property = ib.fk_property";

$sql .= " GROUP BY  ib.label";

$resqlencaissement = $db->query ( $sql );

$sql = "SELECT ib.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)='.$month_num.' then ir.chargesamount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ir.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ir.fk_property = ip.rowid AND ip.fk_property = ib.fk_property ";
$sql .= " GROUP BY ib.label";

$resqlpaiement = $db->query ( $sql );


$sql = "SELECT ip.fk_property AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)='.$month_num.' then ic.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
//$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ic.date_start >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.fk_cost_type = it.rowid ";
$sql .= "  AND it.famille = 'Charge déductible' ";
$sql .= "  AND ic.fk_property = ip.rowid  ";

$sql .= " GROUP BY  ip.fk_property";

$resqlcharged = $db->query ( $sql );

if ($resqlpaiement && $resqlencaissement && $resqlcharged )
{
	$i = 0;
	$num = max($db->num_rows ( $resqlpaiement ), $db->num_rows ( $resqlencaissement ), $db->num_rows ( $resqlcharged ));

	while ( $i < $num )
	{
		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );
		$rowcharged = $db->fetch_row ( $resqlcharged );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++)
		{
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j] - $rowcharged [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
	$db->free ( $resqlcharged );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}


print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Revenu Fiscal").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val)
{
	$total=0;
	print '<tr class="oddeven"><td>' . $key. '</td>';
	foreach( $months_list as $month_num => $month_name )
	{
		print '<td align="right">' . $val [$month_num] . '</td>';
		$total += $val [$month_num];
	}
	print '<td align="right"><b>' . $total . '</b></td>';
	print '</tr>';
	$i ++;
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';


print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Charges non Déductibles").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ip.label AS nom_immeuble";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)='.$month_num.' then ic.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip ON ic.fk_property = ip.rowid";
//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ic.fk_property = ib.fk_property";
$sql .= " WHERE ic.date_start >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND it.famille = 'Charge non déductible' ";
$sql .= " GROUP BY  ip.label";


$resql = $db->query ( $sql );
if ($resql)
{
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num )
	{
		$row = $db->fetch_row ( $resql );
		$total = 0;

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		foreach( $months_list as $month_num => $month_name )
		{
			print '<td align="right">' . $row [$month_num] . '</td>';
			$total += $row [$month_num];
		}
		print '<td align="right"><b>' . $total . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}

print "</table>\n";


$value_array=array();

$sql = "SELECT 'Total' AS Total";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)='.$month_num.' then lp.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE lp.date_payment >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.fk_property = ip.rowid AND ip.fk_property = ib.fk_property";



$resqlencaissement = $db->query ( $sql );

$sql = "SELECT 'Total' AS Total";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)='.$month_num.' then ir.chargesamount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ir.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ir.fk_property = ip.rowid AND ip.fk_property = ib.fk_property ";


$resqlpaiement = $db->query ( $sql );


$sql = "SELECT 'Total' AS Total";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)='.$month_num.' then ic.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
//$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ic.date_start >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.fk_cost_type = it.rowid ";
$sql .= "  AND it.famille = 'Charge déductible' ";
$sql .= "  AND ic.fk_property = ip.rowid ";


$resqlcharged = $db->query ( $sql );

$sql = "SELECT 'Total' AS Total";
foreach( $months_list as $month_num => $month_name )
{
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)='.$month_num.' then ic.amount else 0 end),2) AS month_'.$month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ib";
$sql .= " WHERE ic.date_start >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.fk_cost_type = it.rowid ";
$sql .= "  AND it.famille != 'Charge non déductible' ";
$sql .= "  AND ic.fk_property = ip.rowid AND ip.fk_property = ib.fk_property";

$resqlchargend = $db->query ( $sql );

if ($resqlpaiement && $resqlencaissement && $resqlcharged && $resqlchargend  )
{
	$i = 0;
	$num = max($db->num_rows ( $resqlpaiement ), $db->num_rows ( $resqlencaissement ), $db->num_rows ( $resqlcharged ), $db->num_rows ( $resqlchargend ));

	while ( $i < $num )
	{
		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );
		$rowcharged = $db->fetch_row ( $resqlcharged );
		$rowchargend = $db->fetch_row ( $resqlchargend );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++)
		{
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j] - $rowcharged [$j] - $rowchargend [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
	$db->free ( $resqlcharged );
	$db->free ( $resqlchargend );
}
else
{
	print $db->lasterror (); // affiche la derniere erreur sql
}
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">'.$langs->trans("Revenu Net").'</td>';
foreach( $months_list as $month_name )
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

foreach( $value_array as $key=>$val)
{
	print '<tr class="oddeven"><td>' . $key. '</td>';
	foreach( $months_list as $month_num => $month_name )
	{
		print '<td align="right">' . $val [$month_num] . '</td>';
	}
	print '<td align="right"><b>' . $total . '</b></td>';
	print '</tr>';
	$i ++;
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

print '</td></tr></table>';

// End of page
llxFooter();
$db->close();

?>
