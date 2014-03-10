<?php
/* Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       place/building/floors.php
 *		\ingroup    place
 *		\brief      This file is to manage building floors
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res) die("Include of main fails");

// Change this following line to use the correct relative path from htdocs
require_once '../class/building.class.php';
require_once '../lib/place.lib.php';

// Load traductions files requiredby by page
$langs->load("place@place");
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$fk_place	= GETPOST('fk_place','int');
$ref		= GETPOST('ref','alpha');
$lat		= GETPOST('lat','alpha');
$lng		= GETPOST('lng','alpha');

$mode		= GETPOST('mode','alpha');

if( ! $user->rights->place->read)
	accessforbidden();

$object=new Building($db);



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'update_floors' && ! $_POST["cancel"]  && $user->rights->place->write )
{

	$res = $object->fetch($id);

	$floor_ref	= GETPOST('floor_ref');
	$floor_pos		= GETPOST('floor_pos');


	// Assemblage du tableau des enfants
	$i=0;
	while(isset($floor_ref[$i]) && !empty($floor_ref[$i]))
	{
		$floors[$i]['ref'] 			= $db->escape($floor_ref[$i]);
		$floors[$i]['pos'] 			= $db->escape($floor_pos[$i]);
		$floors[$i]['fk_building'] 	= $db->escape($id);

		$i++;
	}
	// Datas are added in object and saved with trigger after object creation
	$object->floors = $floors;

	$result = $object->insertFloors($user);
	if ( $result > 0)
	{
		setEventMessage($langs->trans('FloorsUpdated'));
	}
	else
	{
		$action="add_floors";
		$mode='edit';
	}
}
/*
 * Delete child
*/
else if ( $action == 'delete_floor' && ! $_POST["cancel"]  && $user->rights->place->delete)
{
	$id = GETPOST('id');
	$id_floor = GETPOST('id_floor');

	$result = $object->deleteFloor($id_floor);
	if ($result > 0)
	{
		setEventMessage($langs->trans('FloorDeletedWithSuccess'));
		Header("Location: floors.php?action=show_floor_form&id=".$id);
		exit();
	}
	else
		dol_print_error($db);

}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans('FloorManagment'),'','','','',array('/place/js/place.js.php'));

$form=new Form($db);


if($object->fetch($id) > 0 )
{

	$head=placePrepareHead($object->place);
	dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"),0,'place@place');

	$ret = $object->place->printInfoTable();

	print '</div>';

	//Second tabs list for building
	$head=buildingPrepareHead($object);
	dol_fiche_head($head, 'floors', $langs->trans("BuildingSingular"),1,'building@place');




	/*---------------------------------------
	 * View building info
	*/
	$ret_html = $object->printShortInfoTable();


	/*
	 * Floors managment
	 */

	print '<br />';
	print_fiche_titre($langs->trans('FloorManagment'),'','floor_32.png@place');

	if($action == 'show_floor_form')
	{

		$out .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="add_building">';
		$out .='<input type="hidden" name="action" value="update_floors" />';
		$out .='<input type="hidden" name="id" value="'.$id.'" />';
		$out .= $object->show_floor_form($id,$user->rights->place->delete);

		$out .='<div style="text-align: center" >
				<input type="submit"  class="button" name="" value="'.$langs->trans('Save').'" />
				<input type="submit"  class="button" name="cancel" value="'.$langs->trans('Cancel').'" />
		</div>';

		$out .= '</form>';

	}
	else {

		$out .= $object->show_floor_list($id);

	}

	print $out;


	/*
	 * Boutons actions
	*/
	print '<div class="tabsAction">';

	if ($action != "show_floor_form" )
	{

		// Add floor
		if($user->rights->place->write)
		{
			print '<div class="inline-block divButAction">';
			print '<a href="floors.php?id='.$id.'&amp;action=show_floor_form" class="butAction">'.$langs->trans('FloorEdition').'</a>';
			print '</div>';

			print '<div class="inline-block divButAction">';
			print '<a href="../room/add.php?fk_building='.$id.'&amp;action=show_floor_form" class="butAction">'.$langs->trans('AddRoom').'</a>';
			print '</div>';
		}
	}
	print '</div>';

}




// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();


// Example 3 : List of data
if ($action == 'list')
{
    $sql = "SELECT";
    $sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.label,";
		$sql.= " t.fk_place,";
		$sql.= " t.description,";
		$sql.= " t.lat,";
		$sql.= " t.lng,";
		$sql.= " t.note_public,";
		$sql.= " t.note_private,";
		$sql.= " t.fk_user_creat,";
		$sql.= " t.tms";


    $sql.= " FROM ".MAIN_DB_PREFIX."place_building as t";
    $sql.= " WHERE field3 = 'xxx'";
    $sql.= " ORDER BY field1 ASC";

    print '<table class="noborder">'."\n";
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans('field1'),$_SERVER['PHP_SELF'],'t.field1','',$param,'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans('field2'),$_SERVER['PHP_SELF'],'t.field2','',$param,'',$sortfield,$sortorder);
    print '</tr>';

    dol_syslog($script_file." sql=".$sql, LOG_DEBUG);
    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                {
                    // You can use here results
                    print '<tr><td>';
                    print $obj->field1;
                    print $obj->field2;
                    print '</td></tr>';
                }
                $i++;
            }
        }
    }
    else
    {
        $error++;
        dol_print_error($db);
    }

    print '</table>'."\n";
}



// End of page
llxFooter();
$db->close();
?>
