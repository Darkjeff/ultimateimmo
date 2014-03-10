<?php
/* Copyright (C) 2007-2010	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013		Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       dev/Placebuildings/Placebuilding_page.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *					Initialy built by build_class_from_table on 2013-08-04 18:47
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

if( ! $user->rights->place->read)
	accessforbidden();

$object=new Building($db);

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/
if ($action == 'update' && ! $_POST["cancel"]  && $user->rights->place->write )
{
	$error=0;

	if (empty($ref))
	{
		$error++;
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")).'</div>';
	}

	if (! $error)
	{
		$object->fetch($id);

		$object->ref          		= $ref;
		$object->lat          		= $lat;
		$object->lng          		= $lng;
		$object->description  		= $_POST["description"];
		$object->note_public       	= $_POST["note_public"];
		$object->note_private       = $_POST["note_private"];

		$result=$object->update($user);
		if ($result > 0)
		{
			Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
		else
		{
			$mesg='<div class="error">'.$object->error.'</div>';

			$action='edit';
		}
	}
	else
	{
		$action='edit';
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('','Buildings','');

$form=new Form($db);


if($object->fetch($id) > 0)
{

	$head=placePrepareHead($object->place);
	dol_fiche_head($head, 'buildings', $langs->trans("PlaceSingular"),0,'place@place');

	$ret = $object->place->printInfoTable();

	print '</div>';
	//Second tabs list for building
	$head=buildingPrepareHead($object);
	dol_fiche_head($head, 'building', $langs->trans("BuildingSingular"),1,'building@place');

	if ($action == 'edit' )
	{

		if(!$user->rights->place->write)
			accessforbidden('',0);

		/*---------------------------------------
		 * Edit object
		*/
		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="20%">'.$langs->trans("BuildingFormLabel_ref").'</td>';
		print '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';


		// Description
		print '<tr><td valign="top">'.$langs->trans("BuildingFormLabel_description").'</td>';
		print '<td>';
		print '<textarea name="description" cols="80" rows="'.ROWS_3.'">'.($_POST['description'] ? GETPOST('description','alpha') : $object->description).'</textarea>';
		print '</td></tr>';

		// Lat
		print '<tr><td width="20%">'.$langs->trans("Latitude").'</td>';
		print '<td><input size="12" name="lat" value="'.(GETPOST('lat') ? GETPOST('lat') : $object->lat).'"></td></tr>';

		// Long
		print '<tr><td width="20%">'.$langs->trans("Longitude").'</td>';
		print '<td><input size="12" name="lng" value="'.(GETPOST('lng') ? GETPOST('lng') : $object->lng).'"></td></tr>';

		// Public note
		print '<tr><td valign="top">'.$langs->trans("NotePublic").'</td>';
		print '<td>';
		print '<textarea name="note_public" cols="80" rows="'.ROWS_3.'">'.($_POST['note_public'] ? GETPOST('note_public','alpha') : $object->note_public)."</textarea><br>";
		print "</td></tr>";

		// Private note
		if (! $user->societe_id)
		{
			print '<tr><td valign="top">'.$langs->trans("NotePrivate").'</td>';
			print '<td>';
			print '<textarea name="note_private" cols="80" rows="'.ROWS_3.'">'.($_POST['note_private'] ? GETPOST('note_private') : $object->note_private)."</textarea><br>";
			print "</td></tr>";
		}

		print '<tr><td align="center" colspan="2">';
		print '<input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; ';
		print '<input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
		print '</table>';
		print '</form>';
	}
	else
	{


		/*---------------------------------------
		 * View object
		*/
		$ret_html = $object->printInfoTable();
	}

	print '</div>';

	/*
	 * Boutons actions
	*/
	print '<div class="tabsAction">';

	if ($action != "edit" )
	{

		// Edit building
		if($user->rights->place->write)
		{
			print '<div class="inline-block divButAction">';
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
			print '</div>';
		}

		// Floor managment
		if($user->rights->place->write)
		{
			print '<div class="inline-block divButAction">';
			print '<a href="floors.php?id='.$id.'" class="butAction">'.$langs->trans('FloorManagment').'</a>';
			print '</div>';
		}
	}

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
