<?php
/* Module to manage locations, buildings, floors and rooms into Dolibarr ERP/CRM
 * Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       building/list.php
 *		\ingroup    place
 *		\brief      Page to list building objects
 */


$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res) die("Include of main fails");

require DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require '../class/place.class.php';
require '../class/building.class.php';
require '../lib/place.lib.php';


// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');

$filter = array();
$param='';
$sortorder	= GETPOST('sortorder','alpha');
$sortfield	= GETPOST('sortfield','alpha');
$page		= GETPOST('page','int');


if (empty($sortorder)) $sortorder="DESC";
if (empty($sortfield)) $sortfield="t.rowid";
if (empty($arch)) $arch = 0;

if ($page == -1) {
	$page = 0 ;
}

$limit = ($conf->global->size_liste_limit>0?$conf->global->size_liste_limit:25);
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

if( ! $user->rights->place->read)
	accessforbidden();

/***************************************************
 * VIEW
*
* Put here all code to build page
****************************************************/

$pagetitle=$langs->trans('BuildingList');
llxHeader('',$pagetitle,'');



$form=new Form($db);
$object = new Building($db);



if($id > 0)
{
	$object->fk_place = $id;
	if($object->fetch_place() > 0)
	{
		$head=placePrepareHead($object->place);
		dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"),0,'place@place');

		$ret = $object->place->printInfoTable();
		print '</div>';
		$filter = array('t.fk_place' => $id);

	}

	$param.='&amp;id='.$id;

}
// Load object list
$ret = $object->fetch_all($sortorder, $sortfield, $limit, $offset);
if($ret == -1) {
	dol_print_error($db,$object->error);
	exit;
}

print_fiche_titre($pagetitle,'','building_32.png@place');


if(!$ret) {
	print '<div class="warning">'.$langs->trans('NoPlaceInDatabase').'</div>';
}
else
{
	print '<table class="noborder" width="100%">'."\n";
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans('BuildingFormLabel_ref'),$_SERVER['PHP_SELF'],'t.ref','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Description'),$_SERVER['PHP_SELF'],'t.description','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Edit'));
	print '</tr>';

	foreach ($object->lines as $building)
	{
		print '<tr><td>';
		$object->ref = $building->ref;
		$object->id = $building->id;
		print $object->getNomUrl(1,'building@place');
		print '</td>';

		print '<td>';
		print $building->description;
		print '</td>';

		print '<td>';
		//print '<a href="fiche.php?id='.$building->id.'">'.$langs->trans('SeeOrEdit').'</a>';
		print '</td></tr>';
	}

	print '</table>';

}


// Action Bar
print '<div class="tabsAction">';

// Add place
print '<div class="inline-block divButAction">';
print '<a href="../add.php?action=add_building'.($id?'&amp;id='.$id:'').'" class="butAction">'.$langs->trans('AddBuilding').'</a>';
print '</div>';


print '</div>';
