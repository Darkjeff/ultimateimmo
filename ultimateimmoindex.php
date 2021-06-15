<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2018 	   Philippe GRAND 		<philippe.grand@atoo-net.com>
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
 *	\file       htdocs/ultimateimmo/template/ultimateimmoindex.php
 *	\ingroup    ultimateimmo
 *	\brief      Home page of ultimateimmo top menu
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immoowner_type.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');

$langs->loadLangs(array("ultimateimmo@ultimateimmo"));

$action=GETPOST('action', 'alpha');
$id = GETPOST('id','int')?GETPOST('id','int'):GETPOST('rowid','int');


// Securite acces client
if (! $user->rights->ultimateimmo->read) accessforbidden();
$socid=GETPOST('socid','int');
if (isset($user->societe_id) && $user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

$max=5;
$now=dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$staticowner=new ImmoOwner($db);
$statictype=new ImmoOwner_Type($db);
$immopropertystatic=new ImmoProperty($db);

llxHeader("",$langs->trans("UltimateimmoArea"));

print load_fiche_titre($langs->trans("UltimateimmoArea"),'','ultimateimmo.png@ultimateimmo');

$Immoowners=array();
$OwnerToValidate=array();
$OwnersValidated=array();
$OwnerUpToDate=array();
$OwnersResiliated=array();

$ImmoownerType=array();

// Liste les proprietaires
$sql = "SELECT t.rowid, t.label,";
$sql.= " d.status, count(d.rowid) as somme";
$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immoowner_type as t";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ultimateimmo_immoowner as d";
$sql.= " ON t.rowid = d.fk_immoowner_type";
$sql.= " AND d.entity IN (".getEntity('immoowner').")";
$sql.= " WHERE t.entity IN (".getEntity('immoowner_type').")";
$sql.= " GROUP BY t.rowid, t.label, d.status";

dol_syslog("index.php::select nb of immoowners by type", LOG_DEBUG);
$result = $db->query($sql);
if ($result)
{
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num)
	{
		$objp = $db->fetch_object($result);

		$ownertype=new ImmoOwner_Type($db);
		$ownertype->id=$objp->rowid;
		$ownertype->label=$objp->label;
		$ImmoownerType[$objp->rowid]=$ownertype;

		if ($objp->status == -1) { $OwnerToValidate[$objp->rowid]=$objp->somme; }
		if ($objp->status == 1)  { $OwnersValidated[$objp->rowid]=$objp->somme; }
		if ($objp->status == 0)  { $OwnersResiliated[$objp->rowid]=$objp->somme; }

		$i++;
	}
	$db->free($result);
}

$now=dol_now();

// current rule: uptodate = the end date is in future whatever is type
// old rule: uptodate = if type does not need payment, that end date is null, if type need payment that end date is in future)
$sql = "SELECT count(*) as somme , d.fk_immoowner_type";
$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immoowner as d, ".MAIN_DB_PREFIX."ultimateimmo_immoowner_type as t";
$sql.= " WHERE d.entity IN (".getEntity('immoowner').")";
//$sql.= " AND d.statut = 1 AND ((t.subscription = 0 AND d.datefin IS NULL) OR d.datefin >= '".$db->idate($now)."')";
$sql.= " AND d.status = 1 AND d.datefin >= '".$db->idate($now)."'";
$sql.= " AND t.rowid = d.fk_immoowner_type";
$sql.= " GROUP BY d.fk_immoowner_type";

dol_syslog("index.php::select nb of uptodate owners by type", LOG_DEBUG);
$result = $db->query($sql);
if ($result)
{
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num)
	{
		$objp = $db->fetch_object($result);
		$OwnerUpToDate[$objp->fk_immoowner_type]=$objp->somme;
		$i++;
	}
	$db->free();
}

print '<div class="fichecenter"><div class="fichethirdleft">';


/*
 * Statistics
 */

if ($conf->use_javascript_ajax)
{
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder nohover" width="100%">';
    print '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Statistics").'</th></tr>';
    print '<tr><td align="center" colspan="2">';

    $SommeA=0;
    $SommeB=0;
    $SommeC=0;
    $SommeD=0;
    $total=0;
    $dataval=array();
    $datalabels=array();
    $i=0;
    foreach ($ImmoownerType as $key => $ownertype)
    {
        $datalabels[]=array($i,$ownertype->getNomUrl(0,dol_size(16)));
        $dataval['draft'][]=array($i,isset($OwnerToValidate[$key])?$OwnerToValidate[$key]:0);
        $dataval['notuptodate'][]=array($i,isset($OwnersValidated[$key])?$OwnersValidated[$key]-(isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0):0);
        $dataval['uptodate'][]=array($i,isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0);
        $dataval['resiliated'][]=array($i,isset($OwnersResiliated[$key])?$OwnersResiliated[$key]:0);
        $SommeA+=isset($OwnerToValidate[$key])?$OwnerToValidate[$key]:0;
        $SommeB+=isset($OwnersValidated[$key])?$OwnersValidated[$key]-(isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0):0;
        $SommeC+=isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0;
        $SommeD+=isset($OwnersResiliated[$key])?$OwnersResiliated[$key]:0;
        $i++;
    }
    $total = $SommeA + $SommeB + $SommeC + $SommeD;
    $dataseries=array();
    $dataseries[]=array($langs->trans("MenuOwnersNotUpToDate"), round($SommeB));
    $dataseries[]=array($langs->trans("MenuOwnersUpToDate"), round($SommeC));
    $dataseries[]=array($langs->trans("OwnersStatusResiliated"), round($SommeD));
    $dataseries[]=array($langs->trans("OwnersStatusToValid"), round($SommeA));

    include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
    $dolgraph = new DolGraph();
    $dolgraph->SetData($dataseries);
    $dolgraph->setShowLegend(1);
    $dolgraph->setShowPercent(1);
    $dolgraph->SetType(array('pie'));
    $dolgraph->setWidth('100%');
    $dolgraph->draw('idgraphstatus');
    print $dolgraph->show($total?0:1);

    print '</td></tr>';
    print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td align="right">';
    print $SommeA+$SommeB+$SommeC+$SommeD;
    print '</td></tr>';
    print '</table>';
    print '</div>';
}

print '<br>';

// List of rent by year
$Total=array();
$Number=array();
$tot=0;
$numb=0;

$sql = "SELECT c.notice, c.date_start as datestart";
$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immoowner as d, ".MAIN_DB_PREFIX."ultimateimmo_immoproperty as c";
$sql.= " WHERE d.entity IN (".getEntity('immoowner').")";
$sql.= " AND d.rowid = c.fk_soc";
if(isset($date_select) && $date_select != '')
{
    $sql .= " AND c.datestart LIKE '".$date_select."%'";
}
$result = $db->query($sql);
if ($result)
{
    $num = $db->num_rows($result);
    $i = 0;
    while ($i < $num)
    {
        $objp = $db->fetch_object($result);
        $year=dol_print_date($db->jdate($objp->datestart),"%Y");
        $Total[$year]=(isset($Total[$year])?$Total[$year]:0)+$objp->notice;
        $Number[$year]=(isset($Number[$year])?$Number[$year]:0)+1;
        $tot+=$objp->notice;
        $numb+=1;
        $i++;
    }
}

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<th>'.$langs->trans("Subscriptions").'</th>';
print '<th align="right">'.$langs->trans("Number").'</th>';
print '<th align="right">'.$langs->trans("AmountTotal").'</th>';
print '<th align="right">'.$langs->trans("AmountAverage").'</th>';
print "</tr>\n";

krsort($Total);
foreach ($Total as $key=>$value)
{
    print '<tr class="oddeven">';
    print '<td><a href=\"'.dol_buildpath('/ultimateimmo/property/immoproperty_list.php',1).'?date_select=$key\">$key</a></td>';
    print "<td align=\"right\">".$Number[$key]."</td>";
    print "<td align=\"right\">".price($value)."</td>";
    print "<td align=\"right\">".price(price2num($value/$Number[$key],'MT'))."</td>";
    print "</tr>\n";
}

// Total
print '<tr class="liste_total">';
print '<td>'.$langs->trans("Total").'</td>';
print "<td align=\"right\">".$numb."</td>";
print '<td align="right">'.price($tot)."</td>";
print "<td align=\"right\">".price(price2num($numb>0?($tot/$numb):0,'MT'))."</td>";
print "</tr>\n";
print "</table></div>";
print "<br>\n";


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


/*
 * Last modified owners
 */
$max=5;

$sql = "SELECT a.rowid, a.status, a.lastname, a.firstname, a.fk_soc,";
$sql.= " a.tms as datem, a.date_creation,";
$sql.= " ta.rowid as typeid, ta.label";
$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immoowner as a, ".MAIN_DB_PREFIX."ultimateimmo_immoowner_type as ta";
$sql.= " WHERE a.entity IN (".getEntity('immoowner').")";
$sql.= " AND a.fk_immoowner_type = ta.rowid";
$sql.= $db->order("a.tms","DESC");
$sql.= $db->plimit($max, 0);

$resql=$db->query($sql);
if ($resql)
{
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<th colspan="4">'.$langs->trans("LastOwnersModified",$max).'</th></tr>';

	$num = $db->num_rows($resql);
	if ($num)
	{
		$i = 0;
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			print '<tr class="oddeven">';
			$staticowner->id=$obj->rowid;
			$staticowner->lastname=$obj->lastname;
			$staticowner->firstname=$obj->firstname;
			if (! empty($obj->fk_soc))
			{
				$staticowner->fk_soc = $obj->fk_soc;
				$staticowner->fetch_thirdparty();
				$staticowner->name=$staticowner->thirdparty->name;
			}
			else
			{
				$staticowner->name=$obj->company;
			}
			$staticowner->ref=$staticowner->getFullName($langs);
			$statictype->id=$obj->typeid;
			$statictype->label=$obj->label;
			print '<td>'.$staticowner->getNomUrl(1,32).'</td>';
			print '<td>'.$statictype->getNomUrl(1,32).'</td>';
			print '<td>'.dol_print_date($db->jdate($obj->datem),'dayhour').'</td>';
			print '<td align="right">'.$staticowner->LibStatut($obj->status,($obj->phone_mobile=='yes'?1:0),$db->jdate($obj->date_end_subscription),5).'</td>';
			print '</tr>';
			$i++;
		}
	}
	print "</table></div>";
	print "<br>";
}
else
{
	dol_print_error($db);
}

// Summary of renters by type
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<th>'.$langs->trans("OwnersTypes").'</th>';
print '<th align=right>'.$langs->trans("OwnersStatusToValid").'</th>';
print '<th align=right>'.$langs->trans("MenuOwnersNotUpToDate").'</th>';
print '<th align=right>'.$langs->trans("MenuOwnersUpToDate").'</th>';
print '<th align=right>'.$langs->trans("OwnersStatusResiliated").'</th>';
print "</tr>\n";

foreach ($ImmoownerType as $key => $ownertype)
{
	print '<tr class="oddeven">';
	print '<td>'.$ownertype->getNomUrl(1, dol_size(32)).'</td>';
	print '<td align="right">'.(isset($OwnerToValidate[$key]) && $OwnerToValidate[$key] > 0?$OwnerToValidate[$key]:'').' '.$staticowner->LibStatut(-1,$ownertype->label,0,3).'</td>';
	print '<td align="right">'.(isset($OwnersValidated[$key]) && ($OwnersValidated[$key]-(isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0) > 0) ? $OwnersValidated[$key]-(isset($OwnerUpToDate[$key])?$OwnerUpToDate[$key]:0):'').' '.$staticowner->LibStatut(1,$ownertype->label,0,3).'</td>';
	print '<td align="right">'.(isset($OwnerUpToDate[$key]) && $OwnerUpToDate[$key] > 0 ? $OwnerUpToDate[$key]:'').' '.$staticowner->LibStatut(1,$ownertype->label,$now,3).'</td>';
	print '<td align="right">'.(isset($OwnersResiliated[$key]) && $OwnersResiliated[$key]> 0 ?$OwnersResiliated[$key]:'').' '.$staticowner->LibStatut(0,$ownertype->label,0,3).'</td>';
	print "</tr>\n";
}
print '<tr class="liste_total">';
print '<td class="liste_total">'.$langs->trans("Total").'</td>';
print '<td class="liste_total" align="right">'.$SommeA.' '.$staticowner->LibStatut(-1,$ownertype->label,0,3).'</td>';
print '<td class="liste_total" align="right">'.$SommeB.' '.$staticowner->LibStatut(1,$ownertype->label,0,3).'</td>';
print '<td class="liste_total" align="right">'.$SommeC.' '.$staticowner->LibStatut(1,$ownertype->label,$now,3).'</td>';
print '<td class="liste_total" align="right">'.$SommeD.' '.$staticowner->LibStatut(0,$ownertype->label,0,3).'</td>';
print '</tr>';

print "</table>\n";
print "</div>";

print '</div></div></div>';


llxFooter();
$db->close();
