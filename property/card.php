<?php
/* Copyright (C) 2013-2015 Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/property/card.php
 * \ingroup immobilier
 * \brief   Card of property
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../core/lib/immobilier.lib.php';
require_once '../class/immoproperty.class.php';
require_once '../class/html.formimmobilier.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

$langs->load("immobilier@immobilier");

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');

$fk_owner=GETPOST('fk_owner','int');
if ($fk_owner==-1) $fk_owner='';
$type_id = GETPOST('type_id', 'int');
if ($type_id==-1) $type_id='';
$fk_property = GETPOST('fk_property', 'int');
if ($fk_property==-1) $fk_property='';

$object = new Immoproperty($db);

// Security check
if (! $user->rights->immobilier->property->read)
	accessforbidden();

/*
 * Actions
 */
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->immobilier->property->delete) {
	$object = new Immoproperty($db);
	$object->id = $id;
	$result = $object->delete($user);

	if ($result > 0) {
		Header("Location: index.php");
		exit();
	} else {
		setEventMessages(null,$object->errors, 'errors');
	}
}
 
if ($action == 'add' && $user->rights->immobilier->property->write) {

	$error = 0;

	if (! $cancel) {

		$name = GETPOST('name', 'alpha');
		if (empty($name)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('NameProperty')), 'errors');
			$error ++;
		}
		if (empty($type_id)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('TypeProperty')), 'errors');
			$error ++;
		}
		if (empty($fk_owner)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('Owner')), 'errors');
			$error ++;
		}

		if (empty($error)) {
			$object->fk_type_property = $type_id;
			
			$object->fk_owner = $fk_owner;
			$object->statut = 1;
			$object->entity=$conf->entity;

			$tmparray=getCountry(GETPOST('country_id','int'),'all',$db,$langs,0);
			if (! empty($tmparray['id']))
			{
				$object->country_id   =$tmparray['id'];
				$object->country_code =$tmparray['code'];
				$object->country_label=$tmparray['label'];
			}

			$object->fk_property 	= $fk_property;
			$object->name 			= GETPOST('name', 'alpha');
			$object->address 		= GETPOST('address', 'alpha');
			$object->building 		= GETPOST('building', 'alpha');
			$object->staircase 		= GETPOST('staircase', 'alpha');
			$object->floor 			= GETPOST('floor', 'alpha');
			$object->numberofdoor	= GETPOST('numberofdoor', 'alpha');
			$object->area			= GETPOST('area', 'alpha');
			$object->numberofpieces	= GETPOST('numberofpieces', 'alpha');
			$object->zip 			= GETPOST('zipcode', 'alpha');
			$object->town			= GETPOST('town', 'alpha');
			$object->fk_pays		= $object->country_id;
			$object->note_public 	= GETPOST('note_public', 'alpha');
			$object->note_private 	= GETPOST('note_private', 'alpha');
			$object->fk_user_author	= $user->id;

			$id = $object->create($user);
            if ($id > 0)
            {
                header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
                exit;
            }
            else
            {
	            setEventMessages(null,$object->errors, 'errors');
                $action='create';
            }
		} else {
			$action='create';
		}
	} else {
        header("Location: index.php");
        exit;
	}
}

if ($action == 'update' && $user->rights->immobilier->property->write)
{
	$error = 0;

	if (! $cancel) {

		$name = GETPOST('name', 'alpha');
		if (empty($name)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('NameProperty')), 'errors');
			$error ++;
		}
		if (empty($type_id)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('TypeProperty')), 'errors');
			$error ++;
		}
		if (empty($fk_owner)) {
			setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('Owner')), 'errors');
			$error ++;
		}

		if (empty($error)) {
			$object->fk_type_property = $type_id;
			$object->fk_owner 		= $fk_owner;
			$object->statut 		= 1;

			$object->fk_property 	= $fk_property;
			$object->name 			= GETPOST('name', 'alpha');
			$object->address 		= GETPOST('address', 'alpha');
			$object->building 		= GETPOST('building', 'alpha');
			$object->staircase 		= GETPOST('staircase', 'alpha');
			$object->floor 			= GETPOST('floor', 'alpha');
			$object->numberofdoor	= GETPOST('numberofdoor', 'alpha');
			$object->area			= GETPOST('area', 'alpha');
			$object->numberofpieces	= GETPOST('numberofpieces', 'alpha');
			$object->zip 			= GETPOST('zipcode', 'alpha');
			$object->town			= GETPOST('town', 'alpha');
			$object->fk_pays		= GETPOST('country_id', 'int');
			$object->note_public 	= GETPOST('note_public', 'alpha');
			$object->note_private 	= GETPOST('note_private', 'alpha');
			$object->id 			= $id;
			$object->fk_user_modif	= $user->id;

			$id = $object->update($user);

            if ($id > 0)
            {
                header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $object->id);
                exit;
            }
            else
            {
	            setEventMessages($object->error, $object->errors, 'errors');
            }
		} else {
			$action='update';
			print $langs->trans('ErrorFieldRequired');
		}
	} else {
        header("Location: index.php");
        exit;
	}
}

/*
 * View
 */
llxHeader('', $langs->trans("Property"));

$form = new Form($db);
$formcompany = new FormCompany($db);
$formimmo = new FormImmobilier($db);

// Create
if ($action == 'create' && $user->rights->immobilier->property->write) {
	print load_fiche_titre($langs->trans("NewProperty"));

	print '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="create">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
	print '<input type="hidden" name="action" value="add">' . "\n";

	dol_fiche_head('');

	print '<table class="border" width="100%">';
	print '<tbody>';

	// Name | Ref
	print '<tr>';
	print '<td width="25%">'.fieldLabel('NameProperty','nameproperty',1).'</td>';
	print '<td>';
	print '<input name="name" id="nameproperty" size="32" value="' . GETPOST('name') . '">';
	print '</td>';
	print '</tr>';

	// Type property
	print '<tr>';
	print '<td>'.fieldLabel('TypeProperty','type_id',1).'</td><td>';
	print $formimmo->select_type_property(GETPOST('type_id'),'type_id','t.active=1');
	if ($user->admin)
		print info_admin(" " . $langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
	print "</td></tr>";

	// Owner
	print '<tr>';
	print '<td>'.fieldLabel('Owner','fk_owner',1).'</td>';
	print '<td>';
	print $form->select_thirdparty_list(GETPOST('fk_owner'),'fk_owner');
	print '</td>';
	print '</tr>';
	
	// Property parent
	if ($object->type_id!='6') {
		print '<tr>';
		print '<td>'.fieldLabel('PropertyParent','fk_property',0).'</td>';
		print '<td>';
		print $formimmo->select_property(GETPOST('fk_property'),'fk_property',1, array(),'fk_type_property=6');
		print '</td>';
		print '</tr>';
	}
	
	// Address
	print '<tr>';
	print '<td>'.fieldLabel('Address','address',0).'</td>';
	print '<td>';
	print '<input name="address" id="address" size="32" value="' . GETPOST('address') . '">';
	print '</td>';
	print '</tr>';

	// Building
	print '<tr>';
	print '<td>'.fieldLabel('Building','building',0).'</td>';
	print '<td>';
	print '<input name="building" id="building" size="32" value="' . GETPOST('building') . '">';
	print '</td>';
	print '</tr>';

	// Staircase
	print '<tr>';
	print '<td>'.fieldLabel('Staircase','staircase',0).'</td>';
	print '<td>';
	print '<input name="staircase" id="staircase" size="32" value="' . GETPOST('staircase') . '">';
	print '</td>';
	print '</tr>';

	// Floor
	print '<tr>';
	print '<td>'.fieldLabel('Floor','floor',0).'</td>';
	print '<td>';
	print '<input name="floor" id="floor" size="32" value="' . GETPOST('floor') . '">';
	print '</td>';
	print '</tr>';

	// Number of Door
	print '<tr>';
	print '<td>'.fieldLabel('NumberOfDoor','numberofdoor',0).'</td>';
	print '<td>';
	print '<input name="numberofdoor" id="numberofdoor" size="32" value="' . GETPOST('numberofdoor') . '">';
	print '</td>';
	print '</tr>';

	// Area
	print '<tr>';
	print '<td>'.fieldLabel('Area','area',0).'</td>';
	print '<td>';
	print '<input name="area" id="area" size="5" value="' . GETPOST('area') . '">';
	print ' ' . $langs->trans("m2");
	print '</td>';
	print '</tr>';

	// Number of pieces
	print '<tr>';
	print '<td>'.fieldLabel('NumberOfPieces','numberofpieces',0).'</td>';
	print '<td>';
	print '<input name="numberofpieces" id="numberofpieces" size="5" value="' . GETPOST('numberofpieces') . '">';
	print '</td>';
	print '</tr>';

	// Zipcode
	print '<tr>';
	print '<td>'.fieldLabel('Zip','zipcode',0).'</td>';
	print '<td>';
	print $formcompany->select_ziptown(GETPOST('zipcode', 'alpha'), 'zipcode', array (
			'town',
			'selectcountry_id' 
	), 6);
	print '</td>';
	print '</tr>';
	
	// Town
	print '<tr>';
	print '<td>'.fieldLabel('Town','town',0).'</td>';
	print '<td>';
	print $formcompany->select_ziptown(GETPOST('town', 'alpha'), 'town', array (
			'zipcode',
			'selectcountry_id' 
	));
	print '</td>';
	print '</tr>';

	// Country
	print '<tr>';
	print '<td>'.fieldLabel('Country','selectcountry_id',0).'</td>';
	print '<td class="maxwidthonsmartphone">';
	print $form->select_country($mysoc->country_id,'country_id');
		if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
	print '</td>';
	print '</tr>';
	
	// Public note
	print '<tr>';
	print '<td class="border" valign="top">'.fieldLabel('NotePublic','note_public',0).'</td>';
	print '<td valign="top" colspan="2">';

	$doleditor = new DolEditor('note_public', GETPOST('note_public'), '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, 70);
	print $doleditor->Create(1);
	print '</td></tr>';

	// Private note
	if (empty($user->societe_id)) {
		print '<tr>';
		print '<td class="border" valign="top">'.fieldLabel('NotePrivate','note_private',0).'</td>';
		print '<td valign="top" colspan="2">';

		$doleditor = new DolEditor('note_private', GETPOST('note_private'), '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, 70);
		print $doleditor->Create(1);
		print '</td></tr>';
	}

	print '</tbody>';
	print '</table>';

	dol_fiche_end();

	print '<div align="center">';
	print '<input type="submit" value="'.$langs->trans("AddProperty").'" name="bouton" class="button" />';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="'.$langs->trans("Cancel").'" class="button" onclick="history.go(-1)" />';
	print '</div>';

	print '</form>';
}
else
{
	if($id > 0)
	{
		if ($action == 'edit')
		{
			$result = $object->fetch($id);
			if ($result < 0) {
				setEventMessages(null, $object->errors, 'errors');
			}

			print "<form name='update' action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="id" value="'.$id.'">';

			$head = property_prepare_head($object);
			dol_fiche_head($head, 'card', $langs->trans("PropertyCard"), 0, 'building@immobilier');

			$linkback = '<a href="'.DOL_URL_ROOT.'/immobilier/property/index.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

			print '<table class="border" width="100%">';

			// Name | Ref
			print '<tr>';
			print '<td width="25%">'.fieldLabel('NameProperty','nameproperty',1).'</td>';
			print '<td>';
			print '<input name="name" id="nameproperty" size="32" value="' . $object->name . '">';
			print '</td>';
			print '</tr>';
	
			// Type property
			print '<tr>';
			print '<td>'.fieldLabel('TypeProperty','type_id',1).'</td><td>';
			print $formimmo->select_type_property($object->fk_type_property,'type_id','t.active=1');
			if ($user->admin)
				print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
			print "</td></tr>";
			
			// Owner
			print '<tr>';
			print '<td>'.fieldLabel('Owner','fk_owner',1).'</td>';
			print '<td>';
			print $form->select_thirdparty_list($object->fk_owner,'fk_owner');
			print '</td>';
			print '</tr>';
			
			// Property
			if ($object->type_id!='6') {
				print '<tr>';
				print '<td>'.fieldLabel('PropertyParent','fk_property',0).'</td>';
				print '<td>';
				print $formimmo->select_property($object->fk_property,'fk_property',1, array(),'fk_type_property=6');
				print '</td>';
				print '</tr>';
			}
	
			// Address
			print '<tr>';
			print '<td>'.fieldLabel('Address','address',0).'</td>';
			print '<td>';
			print '<input name="address" id="address" size="32" value="' . $object->address . '">';
			print '</td>';
			print '</tr>';

			// Building
			print '<tr>';
			print '<td>'.fieldLabel('Building','building',0).'</td>';
			print '<td>';
			print '<input name="building" id="building" size="32" value="' . $object->building . '">';
			print '</td>';
			print '</tr>';

			// Staircase
			print '<tr>';
			print '<td>'.fieldLabel('Staircase','staircase',0).'</td>';
			print '<td>';
			print '<input name="staircase" id="staircase" size="32" value="' . $object->staircase . '">';
			print '</td>';
			print '</tr>';

			// Floor
			print '<tr>';
			print '<td>'.fieldLabel('Floor','floor',0).'</td>';
			print '<td>';
			print '<input name="floor" id="floor" size="32" value="' . $object->floor . '">';
			print '</td>';
			print '</tr>';

			// Number of Door
			print '<tr>';
			print '<td>'.fieldLabel('NumberOfDoor','numberofdoor',0).'</td>';
			print '<td>';
			print '<input name="numberofdoor" id="numberofdoor" size="32" value="' . $object->numberofdoor . '">';
			print '</td>';
			print '</tr>';

			// Area
			print '<tr>';
			print '<td>'.fieldLabel('Area','area',0).'</td>';
			print '<td>';
			print '<input name="area" id="area" size="5" value="' . $object->area . '">';
			print ' ' . $langs->trans("m2");
			print '</td>';
			print '</tr>';

			// Number of pieces
			print '<tr>';
			print '<td>'.fieldLabel('NumberOfPieces','numberofpieces',0).'</td>';
			print '<td>';
			print '<input name="numberofpieces" id="numberofpieces" size="5" value="' . $object->numberofpieces . '">';
			print '</td>';
			print '</tr>';

			// Zipcode / Town
			print '<tr><td>'.fieldLabel('Zip','zipcode',0).'</td><td>';
			print $formcompany->select_ziptown($object->zip, 'zipcode', array (
					'town',
					'selectcountry_id' 
			), 6) . '</tr>';
			print '<tr><td>'.fieldLabel('Town','town',0).'</td><td>';
			print $formcompany->select_ziptown($object->town, 'town', array (
					'zipcode',
					'selectcountry_id' 
			)) . '</td></tr>';

			// Country
			print '<tr>';
			print '<td>'.fieldLabel('Country','selectcountry_id',0).'</td>';
			print '<td class="maxwidthonsmartphone">';
			print $form->select_country($object->fk_pays,'country_id');
				if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
			print '</td>';
			print '</tr>';

			// Public note
			print '<tr>';
			print '<td class="border" valign="top">'.fieldLabel('NotePublic','note_public',0).'</td>';
			print '<td valign="top" colspan="2">';

			$doleditor = new DolEditor('note_public', $object->note_public, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, 70);
			print $doleditor->Create(1);
			print '</td></tr>';

			// Private note
			if (empty($user->societe_id)) {
				print '<tr>';
				print '<td class="border" valign="top">'.fieldLabel('NotePrivate','note_private',0).'</td>';
				print '<td valign="top" colspan="2">';

				$doleditor = new DolEditor('note_private', $object->note_private, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, 70);
				print $doleditor->Create(1);
				print '</td></tr>';
			}

			print '</table>';
			dol_fiche_end();

			print '<div class="center">';
			print '<input type="submit" value="'.$langs->trans("Modify").'" name="bouton" class="button">';
			print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="'.$langs->trans("Cancel").'" class="button" onclick="history.go(-1)" />';
			print '</div>';

			print '</form>';
		} else {
			// Display property card

			$object = new Immoproperty($db);
			$result = $object->fetch($id);

			if ($result < 0) {
				setEventMessages(null, $object->errors, 'errors');
			}

			if ($result) {

				// View mode
				$head = property_prepare_head($object);
				dol_fiche_head($head, 'card', $langs->trans("PropertyCard"), 0, 'building@immobilier');

				/*
				 * Confirm delete
				 */
				if ($action == 'delete') {
					$ret = $form->form_confirm($_SERVER['PHP_SELF'] . "?id=" . $id, $langs->trans("DeleteProperty"), $langs->trans("ConfirmDeleteProperty"), "confirm_delete", '', '', 1);
					if ($ret == 'html')
						print '<br>';
				}

				print '<table class="border" width="100%">';

				$linkback = '<a href="./index.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

				print '<tr>';
				print '<td width="25%">'.$langs->trans("NameProperty").'</td>';
				print '<td colspan="2">';
            	print $form->showrefnav($object, 'rowid', $linkback, 1, 'rowid', 'name', '');
            	print '</td></tr>';

				print '<tr>';
				print '<td>'.$langs->trans("TypeProperty").'</td>';
				print '<td colspan="2">'.$object->type_label.'</td>';
				print '</tr>';
				
				// Owner
				print '<tr>';
				print '<td>'.$langs->trans("Owner") . '</td>';
				print '<td>';
				print $object->getNomUrlOwner(1);
				print '</td>';
				print '</tr>';
				
				if ($object->type_id!='6') {
					print '<tr>';
					print '<td><label for="fk_property">' . $langs->trans("Property") . '</label></td>';
					print '<td>';
					$propertystat=new Immoproperty($db);
					if (!empty($object->fk_property)) {
						$result=$propertystat->fetch($object->fk_property);
						if ($result<0) {
							setEventMessages(null,$propertystat->errors,'errors');
						}
						print $propertystat->getNomUrl(1);
					}
					print '</td>';
					print '</tr>';
				}

				print '<tr>';
				print '<td>'.$langs->trans("Address").'</td>';
				print '<td colspan="2">'.$object->address.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Building").'</td>';
				print '<td colspan="2">'.$object->building.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Staircase").'</td>';
				print '<td colspan="2">'.$object->staircase.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Floor").'</td>';
				print '<td colspan="2">'.$object->floor.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("NumberOfDoor").'</td>';
				print '<td colspan="2">'.$object->numberofdoor.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Area").'</td>';
				print '<td colspan="2">'.$object->area.' '.$langs->trans("m2").'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("NumberOfPieces").'</td>';
				print '<td colspan="2">'.$object->numberofpieces.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Zipcode").'</td>';
				print '<td colspan="2">'.$object->zip.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Town").'</td>';
				print '<td colspan="2">'.$object->town.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("Country").'</td>';
				print '<td colspan="2">'.getCountry($object->fk_pays,1).'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("NotePublic").'</td>';
				print '<td colspan="2">'.$object->note_public.'</td>';
				print '</tr>';

				print '<tr>';
				print '<td>'.$langs->trans("NotePrivate").'</td>';
				print '<td colspan="2">'.$object->note_private.'</td>';
				print '</tr>';

				print '</table>';

				print '</form>';

				dol_fiche_end();
			} // end edit or not edit
		}	// end of if result
	} //fin si id > 0
}

/*
 * Actions bar
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit')
{
	/* Si l'état est "activé" ou "désactivé"
	 *	ET user à droit "creer/supprimer"
	 * 	Afficher : "Modifier" / "Supprimer"
	 */
	if ($user->rights->immobilier->property->write)
	{
		// Modify
		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$object->id.'">'.$langs->trans('Modify').'</a>';

		// Delete
		if ($user->rights->immobilier->property->delete)
		{
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?action=delete&id='.$object->id.'">'.$langs->trans('Delete').'</a>';
		}
	}
}

print '</div>';

if ($action != 'create' && $action != 'edit')
{
	$address = $object->address . ' ' . $object->zip . ' ' . $object->town . ' ' . getCountry($object->fk_pays,0);

	if (! empty($address))
	{
		// Detect if we use https
		$sforhttps=(((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != 'on') && (empty($_SERVER["SERVER_PORT"])||$_SERVER["SERVER_PORT"]!=443))?'':'s');

		$jsgmapapi='http://maps.google.com/maps/api/js';
		if ($sforhttps) $jsgmapapi=preg_replace('/^http:/','https:',$jsgmapapi);
	?>
	<script type="text/javascript" src="<?php echo $jsgmapapi; ?>?sensor=true"></script>

	<script type="text/javascript">
	  var geocoder;
	  var map;
	  var marker;

	  // GMaps v3 API
	  function initialize() {
		var latlng = new google.maps.LatLng(0, 0);
		var myOptions = {
		  zoom: <?php echo ($conf->global->GOOGLE_GMAPS_ZOOM_LEVEL >= 1 && $conf->global->GOOGLE_GMAPS_ZOOM_LEVEL <= 10)?$conf->global->GOOGLE_GMAPS_ZOOM_LEVEL:8; ?>,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.HYBRID  // ROADMAP, SATELLITE, HYBRID, TERRAIN
		}
		map = new google.maps.Map(document.getElementById("map"), myOptions);
		geocoder = new google.maps.Geocoder();
		}

	  function codeAddress() {
		var address = '<?php print dol_escape_js(dol_string_nospecial($address,', ',array("\r\n","\n","\r"))); ?>';
		geocoder.geocode( { 'address': address}, function(results, status) {
		  if (status == google.maps.GeocoderStatus.OK) {
			map.setCenter(results[0].geometry.location);
			marker = new google.maps.Marker({
				map: map,
				position: results[0].geometry.location
			});

			var infowindow = new google.maps.InfoWindow({ content: '<div style="width:250px; height:80px;"><?php echo dol_escape_js($object->name); ?><br><?php echo dol_escape_js(dol_string_nospecial($address,'<br>',array("\r\n","\n","\r"))).(empty($url)?'':'<br><a href="'.$url.'">'.$url.'</a>'); ?></div>' });

			google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(map,marker);
			});


		  } else {
			  if (status == google.maps.GeocoderStatus.ZERO_RESULTS) alert('<?php echo dol_escape_js($langs->transnoentitiesnoconv("GoogleMapsAddressNotFound")); ?>');
			  else alert('Error '+status);
		  }
		});
	  }

	  $(document).ready(function(){
			initialize();
			codeAddress();
		}
	  );
	</script>

	<br>
	<div align="center">
	<div id="map" class="divmap" style="width: 90%; height: 500px;" ></div>
	</div>
	<?php
	}
}

llxFooter();
$db->close();
