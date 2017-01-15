<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015-2016	Alexandre Spangaro  <aspangaro.dolibarr@gmail.com>
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
 * \file		immobilier/renter/card.php
 * \ingroup 	Immobilier
 * \brief		card of renter
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/immorenter.class.php');
require_once ('../class/html.formimmobilier.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
require_once ('../core/lib/immobilier.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');


$langs->load("other");

// Security check
if (! $user->rights->immobilier->renter->read)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$arch = GETPOST('arch', 'int');
$url_back = GETPOST('url_back', 'alpha');
$session_id = GETPOST('session_id', 'int');
$importfrom = GETPOST('importfrom', 'alpha');

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->immobilier->renter->write) {
	$renter = new Renter($db);
	$result = $renter->remove($id);
	
	if ($result > 0) {
		Header("Location: list.php");
		exit();
	} else {
		if (strpos($renter->error, 'agefodd_session_stagiaire_ibfk_2')) {
			$renter->error = $langs->trans("AgfErrorTraineeInSession");
		}
		setEventMessage($renter->error, 'errors');
	}
}

/*
 * Action update (fiche rens stagiaire)
*/
if ($action == 'update' && $user->rights->immobilier->renter->write) {
	if (! $_POST["cancel"]) {
		$renter = new Renter($db);
		
		$result = $renter->fetch($id);
		if ($result > 0) {
			setEventMessage($renter->error, 'errors');
		}
		
		$fk_socpeople = GETPOST('fk_socpeople', 'int');
		
		$renter->nom = GETPOST('nom', 'alpha');
		$renter->prenom = GETPOST('prenom', 'alpha');
		$renter->civilite = GETPOST('civility_id', 'alpha');
		$renter->socid = GETPOST('societe', 'int');
		$renter->fk_owner = GETPOST('owner_id', 'int');
		$renter->fonction = GETPOST('fonction', 'alpha');
		$renter->tel1 = GETPOST('tel1', 'alpha');
		$renter->tel2 = GETPOST('tel2', 'alpha');
		$renter->mail = GETPOST('mail', 'alpha');
		$renter->note = GETPOST('note', 'alpha');
		$renter->date_birth = dol_mktime(0, 0, 0, GETPOST('datebirthmonth', 'int'), GETPOST('datebirthday', 'int'), GETPOST('datebirthyear', 'int'));
		if (! empty($fk_socpeople))
			$renter->fk_socpeople = $fk_socpeople;
		$renter->place_birth = GETPOST('place_birth', 'alpha');
		$result = $renter->update($user);
		
		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
			exit();
		} else {
			setEventMessage($renter->error, 'errors');
		}
	} else {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
		exit();
	}
}

/*
 * Action create (fiche formation)
*/

if ($action == 'create_confirm' && $user->rights->immobilier->renter->write) {
	if (! $_POST["cancel"]) {
		$error = 0;
		
		$renter = new Renter($db);
		
		if ($importfrom == 'create') {
			
			$name = GETPOST('nom', 'alpha');
			$firstname = GETPOST('prenom', 'alpha');
			$civility_id = GETPOST('civility_id', 'alpha');
			$socid = GETPOST('societe', 'int');
			
			if (empty($name) || empty($firstname)) {
				setEventMessage($langs->trans('AgfNameRequiredForParticipant'), 'errors');
				$error ++;
			}
			if (empty($civility_id)) {
				setEventMessage($langs->trans('AgfCiviliteMandatory'), 'errors');
				$error ++;
			}
			if (empty($socid)) {
				setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('ThirdParty')), 'errors');
				$error ++;
			}
			
			// Test trainee already exists or not
			if (! $error) {
				$result = $renter->searchByLastNameFirstNameSoc($name, $firstname, GETPOST('societe', 'int'));
				if ($result > 0) {
					setEventMessage($langs->trans('AgfTraineeAlreadyExists'), 'errors');
					$error ++;
				} elseif ($result < 0) {
					setEventMessage($renter->error, 'errors');
					$error ++;
				}
			}
			if (! $error) {
				$create_thirdparty = GETPOST('create_thirdparty', 'int');
				$create_contact = GETPOST('create_contact', 'int');
				
				$socid = GETPOST('societe', 'int');
				$owner_id = GETPOST('owner_id', 'int');
				$fonction = GETPOST('fonction', 'alpha');
				$tel1 = GETPOST('tel1', 'alpha');
				$tel2 = GETPOST('tel2', 'alpha');
				$mail = GETPOST('mail', 'alpha');
				$note = GETPOST('note', 'alpha');
				$societe_name = GETPOST('societe_name');
				$address = GETPOST('adresse', 'alpha');
				$zip = GETPOST('zipcode', 'alpha');
				$town = GETPOST('town', 'alpha');
				$stagiaire_type = GETPOST('stagiaire_type', 'int');
				
				$date_birth = dol_mktime(0, 0, 0, GETPOST('datebirthmonth', 'int'), GETPOST('datebirthday', 'int'), GETPOST('datebirthyear', 'int'));
				$place_birth = GETPOST('place_birth', 'alpha');
				
				$renter->nom = $name;
				$renter->prenom = $firstname;
				$renter->civilite = $civility_id;
				$renter->socid = $socid;
				$renter->fk_owner = $owner_id;
				$renter->fonction = $fonction;
				$renter->tel1 = $tel1;
				$renter->tel2 = $tel2;
				$renter->mail = $mail;
				$renter->note = $note;
				$renter->date_birth = $date_birth;
				$renter->place_birth = $place_birth;
				$renter->statut = 1;
				
				// Création tiers demandé
				if ($create_thirdparty > 0) {
					$socstatic = new Societe($db);
					
					$socstatic->name = $societe_name;
					$socstatic->phone = $tel1;
					$socstatic->email = $mail;
					$socstatic->address = $address;
					$socstatic->zip = $zip;
					$socstatic->town = $town;
					$socstatic->client = 1;
					$socstatic->code_client = - 1;
					
					$result = $socstatic->create($user);
					
					if (! $result >= 0) {
						$error = $socstatic->error;
						$errors = $socstatic->errors;
					}
					
					$renter->socid = $socstatic->id;
				}
				
				// Création du contact si demandé
				if ($create_contact > 0) {
					
					$contact = new Contact($db);
					
					$contact->civility_id = $civility_id;
					$contact->lastname = $name;
					$contact->firstname = $firstname;
					$contact->address = $address;
					$contact->zip = $zip;
					$contact->town = $$town;
					$contact->state_id = $state_id;
					$contact->country_id = $objectcountry_id;
					$contact->socid = ($socstatic->id > 0 ? $socstatic->id : $socid); // fk_soc
					$contact->statut = 1;
					$contact->email = $mail;
					$contact->phone_pro = $tel1;
					$contact->phone_mobile = $tel2;
					$contact->poste = $fonction;
					$contact->priv = 0;
					$contact->birthday = $date_birth;
					
					$result = $contact->create($user);
					if (! $result >= 0) {
						$error = $contact->error;
						$errors = $contact->errors;
					}
					$renter->fk_socpeople = $contact->id;
				}
				
				$result = $renter->create($user);
			}
		} elseif ($importfrom == 'contact') {
			
			// traitement de l'import d'un contact
			$contact = new Contact($db);
			$contactid = GETPOST('contact', 'int');
			if (! empty($contactid)) {
				$result = $contact->fetch($contactid);
				if ($result < 0) {
					setEventMessage($contact->error, 'errors');
				}
				
				$renter->nom = $contact->lastname;
				$renter->prenom = $contact->firstname;
				$renter->civilite = $contact->civility_id;
				$renter->socid = $contact->socid;
				$renter->fonction = $contact->poste;
				$renter->tel1 = $contact->phone_pro;
				$renter->tel2 = $contact->phone_mobile;
				$renter->mail = $contact->email;
				$renter->note = $contact->note;
				$renter->fk_socpeople = $contact->id;
				$renter->date_birth = $contact->birthday;
				
				$result = $renter->create($user);
			} else {
				$result = - 1;
				$renter->error = 'Select a contact';
			}
		}
		
		if ($result > 0) {
			
			// Inscrire dans la session
			if ($session_id > 0) {
				
				$fk_soc_requester=GETPOST('fk_soc_requester', 'int');
				if ($fk_soc_requester<0) {
					$fk_soc_requester=0;
				}
				$fk_soc_link=GETPOST('fk_soc_link', 'int');
				if ($fk_soc_link<0) {
					$fk_soc_link=0;
				}
				
				$sessionstat = new Agefodd_session_stagiaire($db);
				$sessionstat->fk_session_agefodd = GETPOST('session_id', 'int');
				$sessionstat->fk_stagiaire = $renter->id;
				$sessionstat->fk_agefodd_stagiaire_type = GETPOST('stagiaire_type', 'int');
				$sessionstat->fk_soc_link = $fk_soc_link;
				$sessionstat->status_in_session = GETPOST('status_in_session', 'int');
				$sessionstat->fk_soc_requester = $fk_soc_requester;
				$result = $sessionstat->create($user);
				
				if ($result > 0) {
					setEventMessage($langs->trans('SuccessCreateStagInSession'), 'mesgs');
					$url_back = dol_buildpath('/agefodd/session/subscribers.php', 1) . '?id=' . $session_id;
				} else {
					setEventMessage($sessionstat->error, 'errors');
				}
			}
			
			$saveandstay = GETPOST('saveandstay');
			if (! empty($saveandstay)) {
				Header("Location: " . $_SERVER['HTTP_REFERER']);
			} else {
				if (strlen($url_back) > 0) {
					Header("Location: " . $url_back);
				} else {
					Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $renter->id);
				}
			}
			exit();
		} else {
			setEventMessage($renter->error, 'errors');
		}
		
		$action = 'create';
	} else {
		Header("Location: list.php");
		exit();
	}
}

/*
 * View
*/
$title = ($action == 'nfcontact' || $action == 'create' ? $langs->trans("NewRenter") : $langs->trans("RenterCard").' | '.$langs->trans("Card"));
llxHeader('', $title);

$form = new Form($db);
$formcompany = new FormCompany($db);
$formimmo = new FormImmobilier($db);
$object = new Renter($db);

/*
 * Action create
 */
if ($action == 'create' && $user->rights->immobilier->property->write) {
	
	print "\n" . '<script type="text/javascript">
		$(document).ready(function () {
			$("input[type=radio][name=create_thirdparty]").change(function() {
				if($(this).val()==1) {
					$(".create_thirdparty_block").show();
					$(".select_thirdparty_block").hide();
				}else {
					$(".create_thirdparty_block").hide();
					$(".select_thirdparty_block").show();
				}
			});
			
			$("select[name=importfrom]").change(function() {
				if($(this).val()=="contact") {
					$("#fromcontact").show();
					$("#fromblanck").hide();
				}else {
					$("#fromcontact").hide();
					$("#fromblanck").show();
				}
			});
	
			if($("input[type=radio][name=create_thirdparty]:checked").val()==1) {
				$(".create_thirdparty_block").show();
				$(".select_thirdparty_block").hide();
			}else {
				$(".create_thirdparty_block").hide();
				$(".select_thirdparty_block").show();
			}
			
			if($("select[name=importfrom] option:selected").val()=="contact") {
				$("#fromcontact").show();
				$("#fromblanck").hide();
			}else {
				$("#fromcontact").hide();
				$("#fromblanck").show();
			}
			
		});';
	print "\n" . "</script>\n";

	print '<form name="create" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="create_confirm">';
	if ($url_back)
		print '<input type="hidden" name="url_back" value="' . $url_back . '">' . "\n";
	
	print '<b>' . $langs->trans("SelectRenterCreationForm") . ': </b>';

	print '<select name="importfrom" id="importfrom" class="flat">';
	$selected = '';
	if ($importfrom == 'contact')
		$selected = ' selected="selected" ';
	print '<option value="contact" ' . $selected . '>' . $langs->trans("MenuActRenterNewFromContact") . '</option>';
	$selected = '';
	if ($importfrom == 'create' || empty($importfrom))
		$selected = ' selected="selected" ';
	print '<option value="create" ' . $selected . '>' . $langs->trans("MenuActRenterNew") . '</option>';
	print '</select>';
	
	print '<div id="fromcontact" style="display:none">';
	print load_fiche_titre($langs->trans("MenuActRenterNewFromContact"));

	dol_fiche_head('');
	print '<table class="border" width="100%">';
	
	print '<tr><td class="titlefield">' . $langs->trans("ContactImportAsRenter") . '</td>';
	print '<td>';
	
	$renter_static = new Renter($db);
	$renter_static->fetchall('DESC', 's.rowid', '', 0);
	$exclude_array = array ();
	if (is_array($renter_static->lines) && count($renter_static->lines) > 0) {
		foreach ( $renter_static->lines as $line ) {
			if (! empty($line->fk_socpeople)) {
				$exclude_array[] = $line->fk_socpeople;
			}
		}
	}
	$formimmo->select_contacts_custom(0, '', 'contact', 1, $exclude_array, '', 1, '', 1);
	print '</td></tr>';
	
	print '</table>';

	dol_fiche_end('');

	print '</div>';

	print '<div id="fromblanck">';
	print load_fiche_titre($langs->trans("MenuActRenterNew"));

	dol_fiche_head('');

	print '<table class="border" width="100%">';
	print '<tbody>';
	print '<tr class="liste_titre"><td colspan="4"><b>' . $langs->trans("ThirdParty") . '</b></td>';

	if (GETPOST('create_thirdparty', 'int') > 0) {
		$checkedYes = 'checked="checked"';
		$checkedNo = '';
	} else {
		$checkedYes = '';
		$checkedNo = 'checked="checked"';
	}

	print '<tr><td class="titlefield">' . $langs->trans('CreateANewThirdPartyFromRenterForm');
	print img_picto($langs->trans("CreateANewThirdPartyFromRenterFormInfo"), 'help');
	print '</td>';
	print '<td colspan="3">';
	print '<input type="radio" id="create_thirdparty_confirm" name="create_thirdparty" value="1" ' . $checkedYes . '/> <label for="create_thirdparty_confirm">' . $langs->trans('Yes') . '</label>';
	print '&nbsp;&nbsp;&nbsp;';
	print '<input type="radio" id="create_thirdparty_cancel" name="create_thirdparty" ' . $checkedNo . ' value="-1"/> <label for="create_thirdparty_cancel">' . $langs->trans('No') . '</label>';
	print '</td>';
	print '	</tr>';
	print '<tr class="select_thirdparty_block"><td class="fieldrequired">' . $langs->trans("Company") . '</td><td colspan="3">';
	print $form->select_company(GETPOST('societe', 'int'), 'societe', '(s.client IN (1,3,2))', 1, 1);
	print '</td></tr>';
	
	print '<tr class="create_thirdparty_block"><td>' . $langs->trans("ThirdPartyName") . '</td>';
	print '<td colspan="3" ><input name="societe_name" class="flat" size="50" value="' . GETPOST('societe_name', 'alpha') . '"></td></tr>';
	
	// Address
	print '<tr class="create_thirdparty_block"><td valign="top">' . $langs->trans('Address') . '</td><td colspan="3"><textarea name="adresse" cols="40" rows="3" wrap="soft">';
	print $object->address;
	print '</textarea></td></tr>';
	
	// Zip
	print '<tr class="create_thirdparty_block"><td>' . $langs->trans('Zip') . '</td><td>';
	print $formcompany->select_ziptown($object->zip, 'zipcode', array (
			'town',
			'selectcountry_id',
			'departement_id' 
	), 6);
	print '</td></tr>';

	// Town
	print '<tr class="create_thirdparty_block"><td>' . $langs->trans('Town') . '</td><td>';
	print $formcompany->select_ziptown($object->town, 'town', array (
			'zipcode',
			'selectcountry_id',
			'departement_id' 
	));
	print '</td></tr>';
	print '</tbody>';
	print '</table>';

	dol_fiche_end('');

	// Infos locataire
	dol_fiche_head('');

	print '<table class="border" width="100%">';
	print '<tbody>';
	print '<tr class="liste_titre"><td colspan="4"><b>' . $langs->trans("Renter") . '</b></td>';
	
	print '<tr><td class="titlefield"><span class="fieldrequired">' . $langs->trans("Civility") . '</span></td>';
	print '<td colspan="3">' . $formcompany->select_civility(GETPOST('civility_id')) . '</td>';
	print '</tr>';

	print '<tr><td><span class="fieldrequired">' . $langs->trans("Lastname") . '</span></td>';
	print '<td colspan="3"><input name="nom" class="flat" size="50" value="' . GETPOST('nom', 'alpha') . '"></td></tr>';
	
	print '<tr><td><span class="fieldrequired">' . $langs->trans("Firstname") . '</span></td>';
	print '<td colspan="3"><input name="prenom" class="flat" size="50" value="' . GETPOST('prenom', 'alpha') . '"></td></tr>';
	
	print '<tr><td>' . $langs->trans('CreateANewContactFromRenterForm');
	print img_picto($langs->trans("CreateANewContactFromRenterFormInfo"), 'help');
	print '</td>';
	print '<td colspan="3">';
	if (GETPOST('create_contact', 'int') > 0) {
		$checkedYes = 'checked="checked"';
		$checkedNo = '';
	} else {
		$checkedYes = '';
		$checkedNo = 'checked="checked"';
	}
	print '<input type="radio" id="create_contact_confirm" name="create_contact" value="1" ' . $checkedYes . '/> <label for="create_contact_confirm">' . $langs->trans('Yes') . '</label>';
	print '&nbsp;&nbsp;&nbsp;';
	print '<input type="radio" id="create_contact_cancel" name="create_contact" ' . $checkedNo . ' value="-1"/> <label for="create_contact_cancel">' . $langs->trans('no') . '</label>';
	print '</td>';
	print '	</tr>';
	
	print '<tr><td>' . $langs->trans("Job") . '</td>';
	print '<td colspan="3"><input name="fonction" class="flat" size="50" value="' . GETPOST('fonction', 'alpha') . '"></td></tr>';
	
	print '<tr><td>' . $langs->trans("Phone") . '</td>';
	print '<td colspan="3"><input name="tel1" class="flat" size="50" value="' . GETPOST('tel1', 'alpha') . '"></td></tr>';
	
	print '<tr><td>' . $langs->trans("Mobile") . '</td>';
	print '<td colspan="3"><input name="tel2" class="flat" size="50" value="' . GETPOST('tel2', 'alpha') . '"></td></tr>';
	
	print '<tr><td>' . $langs->trans("Email") . '</td>';
	print '<td colspan="3"><input name="mail" class="flat" size="50" value="' . GETPOST('mail', 'alpha') . '"></td></tr>';
	
	print '<tr><td>' . $langs->trans("DateToBirth") . '</td>';
	print '<td>';
	print $form->select_date('', 'datebirth', '', '', 1);
	print '</td></tr>';
	
	print '<tr><td>' . $langs->trans("PlaceBirth") . '</td>';
	print '<td colspan="3"><input name="place_birth" class="flat" size="50" value=""></td></tr>';
	
	print '<tr><td valign="top">' . $langs->trans("Note") . '</td>';
	print '<td colspan="3"><textarea name="note" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	print '</table>';
	print '</div>';
	
	print '</table>';

	dol_fiche_end('');
	/**************************************************************OWNER******************************/
	
	dol_fiche_head('');

	print '<table class="border" width="100%">';
	print '<tbody>';
	print '<tr class="liste_titre"><td colspan="4"><b>' . $langs->trans("Owner") . '</b></td>';

	print '<tr class="select_thirdparty_block"><td class="titlefield">' . $langs->trans("Owner") . '</td><td colspan="3">';
	print $form->select_company(GETPOST('owner_id', 'int'), 'owner_id', '(s.client IN (1,3,2))', 1, 1);
	print '</td></tr>';

	print '</table>';
	print '</div>';

	dol_fiche_end('');
	
	print '<div class="center">';
	print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input type="submit" class="butActionDelete" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';

	print '</form>';
} else {
	// Affichage de la fiche locataire
	if ($id) {
		$object = new Renter($db);
		$result = $object->fetch($id);

		if ($result) {
			$head = renter_prepare_head($object);
			dol_fiche_head($head, 'card', $langs->trans("RenterCard"), 0, 'user');

			// Affichage en mode "édition"
			if ($action == 'edit') {
				print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
				print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
				print '<input type="hidden" name="action" value="update">';

				print '<input type="hidden" name="id" value="' . $id . '">';

				print '<table class="border" width="100%">';
				print '<tr><td class="titlefield">' . $langs->trans("Ref") . '</td>';
				print '<td>' . $object->id . '</td></tr>';

				// if contact renter from contact then display contact information
				if (empty($object->fk_socpeople)) {

					print '<tr><td>' . $langs->trans("Civility") . '</td>';
					print '<td>' . $formcompany->select_civility($object->civilite) . '</td>';
					print '</tr>';

					print '<tr><td>' . $langs->trans("Lastname") . '</td>';
					print '<td><input name="nom" class="flat" size="50" value="' . strtoupper($object->nom) . '"></td></tr>';

					print '<tr><td>' . $langs->trans("Firstname") . '</td>';
					print '<td><input name="prenom" class="flat" size="50" value="' . ucfirst($object->prenom) . '"></td></tr>';

					print '<tr><td valign="top">' . $langs->trans("Company") . '</td><td>';
					print $form->select_company($object->socid, 'societe', '(s.client IN (1,3,2))', 1, 1);
					print '</td></tr>';

					print '<tr><td valign="top">' . $langs->trans("Owner") . '</td><td colspan="3">';
					print $form->select_company($object->fk_owner, 'owner_id', '(s.client IN (1,3,2))', 1, 1);
					print '</td></tr>';

					print '<tr><td>' . $langs->trans("Job") . '</td>';
					print '<td><input name="fonction" class="flat" size="50" value="' . $object->fonction . '"></td></tr>';

					print '<tr><td>' . $langs->trans("Phone") . '</td>';
					print '<td><input name="tel1" class="flat" size="50" value="' . $object->phone_pro . '"></td></tr>';

					print '<tr><td>' . $langs->trans("Mobile") . '</td>';
					print '<td><input name="tel2" class="flat" size="50" value="' . $object->phone_mobile . '"></td></tr>';

					print '<tr><td>' . $langs->trans("Email") . '</td>';
					print '<td><input name="mail" class="flat" size="50" value="' . $object->email . '"></td></tr>';

					print '<tr><td>' . $langs->trans("DateToBirth") . '</td>';
					print '<td>';
					print $form->select_date($object->date_birth, 'datebirth', 0, 0, 1, 'update');
					print '</td></tr>';
				} else {
					print '<input type="hidden" name="fk_socpeople" value="' . $object->fk_socpeople . '">';
					
					print '<tr><td>' . $langs->trans("Civility") . '</td>';
					$contact_static = new Contact($db);
					$contact_static->civility_id = $object->civilite;
					print '<td>' . $contact_static->getCivilityLabel() . '</td></tr>';
					print '<input type="hidden" name="civility_id" value="' . $object->civilite . '">';

					print '<tr><td>' . $langs->trans("Lastname") . '</td>';
					print '<td><a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $object->fk_socpeople . '">' . strtoupper($object->nom) . '</a></td></tr>';
					print '<input type="hidden" name="nom" value="' . $object->nom . '">';
					
					print '<tr><td>' . $langs->trans("Firstname") . '</td>';
					print '<td>' . ucfirst($object->prenom) . '</td></tr>';
					print '<input type="hidden" name="prenom" value="' . $object->prenom . '">';

					print '<tr><td valign="top">' . $langs->trans("Company") . '</td><td>';
					if ($object->socid) {
						print '<a href="' . dol_buildpath('/comm/card.php', 1) . '?socid=' . $object->socid . '">';
						print '<input type="hidden" name="societe" value="' . $object->socid . '">';
						print img_object($langs->trans("ShowCompany"), "company") . ' ' . dol_trunc($object->socname, 20) . '</a>';
					} else {
						print '&nbsp;';
						print '<input type="hidden" name="societe" value="">';
					}
					print '</td></tr>';
					
					print '<tr><td>' . $langs->trans("Job") . '</td>';
					print '<td>' . $object->fonction . '</td></tr>';
					print '<input type="hidden" name="fonction" value="' . $object->fonction . '">';
					
					print '<tr><td>' . $langs->trans("Phone") . '</td>';
					print '<td>' . dol_print_phone($object->phone_pro) . '</td></tr>';
					print '<input type="hidden" name="tel1" value="' . $object->phone_pro . '">';
					
					print '<tr><td>' . $langs->trans("Mobile") . '</td>';
					print '<td>' . dol_print_phone($object->phone_mobile) . '</td></tr>';
					print '<input type="hidden" name="tel2" value="' . $object->phone_mobile . '">';
					
					print '<tr><td>' . $langs->trans("Email") . '</td>';
					print '<td>' . dol_print_email($object->email, $object->id, $object->socid, 'AC_EMAIL', 25) . '</td></tr>';
					print '<input type="hidden" name="mail" value="' . $object->email . '">';
					
					print '<tr><td>' . $langs->trans("DateToBirth") . '</td>';
					print '<td>' . dol_print_date($object->date_birth, "day");
					print '</td></tr>';
				}
				
				print '<tr><td>' . $langs->trans("PlaceBirth") . '</td>';
				print '<td><input name="place_birth" class="flat" size="50" value="' . $object->place_birth . '"></td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans("Note") . '</td>';
				if (! empty($object->note))
					$notes = nl2br($object->note);
				print '<td><textarea name="note" rows="3" cols="0" class="flat" style="width:360px;">' . stripslashes($object->note) . '</textarea></td></tr>';

				print '</table>';
				print '</div>';
				print '<table style=noborder align="right">';
				print '<tr><td align="center" colspan=2>';
				print '<input type="submit" class="butAction" name="save" value="' . $langs->trans("Save") . '"> &nbsp; ';
				print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '">';
				if (! empty($object->fk_socpeople)) {
					print '<a class="butAction" href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $object->fk_socpeople . '">' . $langs->trans('AgfModifierFicheContact') . '</a>';
				}
				print '</td></tr>';
				print '</table>';
				print '</form>';
				
				print '</div>' . "\n";
			} else {

				// Display in "view" mode

				/*
				 * Confirmation de la suppression
				 */
				if ($action == 'delete') {
					$ret = $form->form_confirm($_SERVER['PHP_SELF'] . "?id=" . $id, $langs->trans("ImmoDeleteRenter"), $langs->trans("ImmoConfirmDeleteRenter"), "confirm_delete", '', '', 1);
					if ($ret == 'html')
						print '<br>';
				}

				$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

				immo_banner_tab($object, 'id', $linkback, 1, 'rowid', 'name');

				print '<div class="fichecenter">';
				print '<div class="fichehalfleft">';
				
				print '<div class="underbanner clearboth"></div>';
				print '<table class="border tableforfield" width="100%">';

				/*
				print '<tr><td class="titlefield">' . $langs->trans("Ref") . '</td>';
				print '<td>' . $form->showrefnav($object, 'id	', '', 1, 'rowid', 'id') . '</td></tr>';
				*/

				print '<tr><td>' . $langs->trans("Civility") . '</td>';
				$contact_static = new Contact($db);
				$contact_static->civility_id = $object->civilite;
				print '<td>' . $contact_static->getCivilityLabel() . '</td></tr>';

				/*
				if (! empty($object->fk_socpeople)) {
					print '<tr><td>' . $langs->trans("Lastname") . '</td>';
					print '<td><a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $object->fk_socpeople . '">' . strtoupper($object->nom) . '</a></td></tr>';
				} else {
					print '<tr><td>' . $langs->trans("Lastname") . '</td>';
					print '<td>' . strtoupper($object->nom) . '</td></tr>';
				}

				print '<tr><td>' . $langs->trans("Firstname") . '</td>';
				print '<td>' . ucfirst($object->prenom) . '</td></tr>';
				*/

				print '<tr><td valign="top">' . $langs->trans("Company") . '</td><td>';
				if ($object->socid) {
					$soc = new Societe($db);
					$soc->fetch($object->socid);
					print $soc->getNomUrl(1);
				} else {
					print '&nbsp;';
				}
				print '</td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans("Owner") . '</td><td>';
				if ($object->fk_owner) {
					$soc = new Societe($db);
					$soc->fetch($object->fk_owner);
					print $soc->getNomUrl(1);
				} else {
					print '&nbsp;';
				}
				print '</td></tr>';
				
				print '<tr><td>' . $langs->trans("Job") . '</td>';
				print '<td>' . $object->fonction . '</td></tr>';

				/*
				print '<tr><td>' . $langs->trans("Phone") . '</td>';
				print '<td>' . dol_print_phone($object->phone_pro) . '</td></tr>';

				print '<tr><td>' . $langs->trans("PhoneMobile") . '</td>';
				print '<td>' . dol_print_phone($object->phone_mobile) . '</td></tr>';

				print '<tr><td>' . $langs->trans("Email") . '</td>';
				print '<td>' . dol_print_email($object->email, $object->id, $object->socid, 'AC_EMAIL', 25) . '</td></tr>';
				*/

				print '<tr><td>' . $langs->trans("DateToBirth") . '</td>';
				print '<td>' . dol_print_date($object->date_birth, "day") . '</td></tr>';
				
				print '<tr><td>' . $langs->trans("PlaceBirth") . '</td>';
				print '<td>' . $object->place_birth . '</td></tr>';

				print '</table>';
				print '</div>';
				print '<div class="fichehalfright"><div class="ficheaddleft">';
		   
				print '<div class="underbanner clearboth"></div>';
				print '<table class="border tableforfield" width="100%">';

				print '<tr><td>' . $langs->trans("Note") . '</td>';
				if (! empty($object->note))
					$notes = nl2br($object->note);
				print '<td>' . stripslashes($notes) . '</td></tr>';

				// Other attributes
				$parameters=array();
				$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
				if (empty($reshook) && ! empty($extrafields->attribute_label))
				{
					print $object->showOptionals($extrafields, 'view', $parameters);
				}
				
				print "</table>\n";
				print '</div>';
				
				print '</div></div>';
				print '<div style="clear:both"></div>';

				print '</form>';

				dol_fiche_end();
			}
		} else {
			setEventMessage($object->error, 'errors');
		}
	}
}

/*
 * Barre d'actions
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact') {
	if ($user->rights->immobilier->renter->write) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('Modify') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
	}
	if ($user->rights->immobilier->renter->delete) {
		print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $id . '">' . $langs->trans('Delete') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Delete') . '</a>';
	}
}

print '</div>';

llxFooter();
$db->close();