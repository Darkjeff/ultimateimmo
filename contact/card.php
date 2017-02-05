<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015 		Alexandre Spangaro  <aspangaro@zendsi.com>
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
 * \file	immobilier/contact/card.php
 * \ingroup immobilier
 * \brief	card of contact
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/contact.class.php');
require_once ('../core/lib/immobilier.lib.php');

// Security check
if (! $user->rights->immobilier->renter->read)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$spid = GETPOST('spid', 'int');
$arch = GETPOST('arch', 'int');

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer) {
	$agf = new Agefodd_contact($db);
	$result = $agf->remove($id);
	
	if ($result > 0) {
		Header("Location: list.php");
		exit();
	} else {
		setEventMessage($langs->trans("AgfDeleteErr"), 'errors');
	}
}

/*
 * Actions archive/active
 */
if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer) {
	if ($confirm == "yes") {
		$agf = new Agefodd_contact($db);
		
		$result = $agf->fetch($id, 'peopleid');
		$agf->archive = $arch;
		
		$result = $agf->update($user);
		
		if ($result > 0) {
			Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	} else {
		Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
		exit();
	}
}

/*
 * Action create (fiche formateur: attention, le contact DLB doit déjà exister)
 */

if ($action == 'create_confirm' && $user->rights->agefodd->creer) {
	if (! $_POST ["cancel"]) {
		$agf = new Agefodd_contact($db);
		
		$agf->spid = $spid;
		$result = $agf->create($user);
		
		if ($result > 0) {
			Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $result);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	} else {
		Header("Location: list.php");
		exit();
	}
}

/*
 * View
*/

$title = ($action == 'create' ? $langs->trans("AgfCreateContact") : $langs->trans("AgfContactFiche"));
llxHeader('', $title);

$form = new Form($db);

/*
 * Action create
*/
if ($action == 'create' && $user->rights->agefodd->creer) {
	print load_fiche_titre($langs->trans("AgfCreateContact"));
	
	print '<form name="create" action="' . $_SERVER ['PHP_SELF'] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
	print '<input type="hidden" name="action" value="create_confirm">' . "\n";
	
	print '<div class="warning">' . $langs->trans("AgfContactNewWarning1");
	print ' <a href="' . DOL_URL_ROOT . '/contact/card.php?action=create">' . $langs->trans("AgfContactNewWarning2") . '</a>.';
	print $langs->trans("AgfContactNewWarning3") . '</div>' . "\n";
	
	print '<table class="border" width="100%">' . "\n";
	
	print '<tr><td>' . $langs->trans("AgfContact") . '</td>';
	print '<td>';
	
	$agf_static = new Agefodd_contact($db);
	$nbcontact = $agf_static->fetch_all('ASC', 'rowid', '', 0);
	$exclude_array = array ();
	if ($nbcontact > 0) {
		foreach ( $agf_static->line as $line ) {
			$exclude_array [] = $line->fk_socpeople;
		}
	}
	
	$form->select_contacts(0, '', 'spid', 1, $exclude_array, '', 1, '', 1);
	print '</td></tr>';
	
	print '</table>';
	print '</div>';
	
	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '">';
	print '</td></tr>';
	print '</table>';
	print '</form>';
} else {
	// Affichage de la fiche "intervenant"
	if ($id) {
		$agf = new Agefodd_contact($db);
		$result = $agf->fetch($id, 'peopleid');
		
		if ($result > 0) {
			$head = contact_prepare_head($agf);
			
			dol_fiche_head($head, 'card', $langs->trans("AgfContactFiche"), 0, 'user');
			
			// Affichage en mode "consultation"
			
			/*
			 * Confirmation de la suppression
			*/
			if ($action == 'delete') {
				$ret = $form->form_confirm($_SERVER ['PHP_SELF'] . "?id=" . $id, $langs->trans("AgfDeleteContact"), $langs->trans("AgfConfirmDeleteContact"), "confirm_delete", '', '', 1);
				if ($ret == 'html')
					print '<br>';
			}
			
			/*
			 * Confirmation de l'archivage/activation suppression
			*/
			if ($action == 'archive' || $action == 'active') {
				if ($action == 'archive')
					$value = 1;
				if ($action == 'active')
					$value = 0;
				
				$ret = $form->form_confirm($_SERVER ['PHP_SELF'] . "?arch=" . $value . "&id=" . $id, $langs->trans("AgfFormationArchiveChange"), $langs->trans("AgfConfirmArchiveChange"), "arch_confirm_delete", '', '', 1);
				if ($ret == 'html')
					print '<br>';
			}
			
			print '<table class="border" width="100%">';
			
			print '<tr><td width="25%">' . $langs->trans("Ref") . '</td>';
			print '<td>' . $form->showrefnav($agf, 'id', '', 1, 'rowid', 'id') . '</td></tr>';
			
			print '<tr><td>' . $langs->trans("Name") . '</td>';
			print '<td>' . ucfirst(strtolower($agf->civilite)) . ' ' . strtoupper($agf->lastname) . ' ' . ucfirst(strtolower($agf->firstname)) . '</td></tr>';
			
			print "</table>";
			
			print '</div>';
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}
}

/*
 * Barre d'actions
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact') {
	if ($user->rights->agefodd->creer) {
		print '<a class="butAction" href="' . DOL_URL_ROOT . '/contact/card.php?id=' . $agf->spid . '">' . $langs->trans('AgfModifierFicheContact') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfModifierFicheContact') . '</a>';
	}
	if ($user->rights->agefodd->creer) {
		print '<a class="butActionDelete" href="' . $_SERVER ['PHP_SELF'] . '?action=delete&id=' . $id . '">' . $langs->trans('Delete') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Delete') . '</a>';
	}
	
	if ($user->rights->agefodd->modifier) {
		if ($agf->archive == 0) {
			print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=archive&id=' . $id . '">' . $langs->trans('AgfArchiver') . '</a>';
		} else {
			print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=active&id=' . $id . '">' . $langs->trans('AgfActiver') . '</a>';
		}
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfArchiver') . '/' . $langs->trans('AgfActiver') . '</a>';
	}
}

print '</div>';

llxFooter();

$db->close();
