<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2017 Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file 		immobilier/rent/card.php
 * \ingroup 	Immobilier
 * \brief 		Card of rent
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/immorent.class.php');
require_once ('../core/lib/immobilier.lib.php');
require_once ('../class/html.formimmobilier.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php');

// Langs
$langs->load("immobilier@immobilier");

$mesg = '';
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);

$object = new Rent($db);

// Security check
if (! $user->rights->immobilier->property->read)
	accessforbidden();
	
/*
 * Actions
 */
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->immobilier->property->delete) {
	$object = new Rent($db);
	$object->id = $id;
	$result = $object->delete($user);
	
	if ($result > 0) {
		Header("Location: list.php");
		exit();
	} else {
		setEventMessage($object->error, 'errors');
	}
}

if ($action == 'add' && $user->rights->immobilier->property->write) {
	
	$error = 0;
	
	if (! $cancel) {
		$datect = dol_mktime(0, 0, 0, GETPOST('datecontractmonth', 'int'), GETPOST('datecontractday', 'int'), GETPOST('datecontractyear', 'int'));
		
		$object = new Rent($db);
		
		$object->fk_property = GETPOST("fk_property");
		$object->fk_renter = GETPOST("fk_renter");
		$object->date_start = $datect;
		$object->date_end = $datect;
		$object->date_prochain_loyer = $datect;
		$object->montant_tot = GETPOST("loyer") + GETPOST("charges");
		$object->loyer = GETPOST("loyer");
		$object->charges = GETPOST("charges");
		$object->tva = GETPOST('tva_value','alpha');
		$object->periode = GETPOST("periode");
		$object->depot = GETPOST("depot");
		$object->commentaire = GETPOST("commentaire");
		$object->fk_user_author = $user->id;
		
		$id = $object->create($user);
		
		if ($id > 0) {
			header("Location: list.php");
			exit();
		} else {
			setEventMessage($object->error, 'errors');
			$action = 'create';
		}
	} else {
		header("Location: list.php");
		exit();
	}
}

if ($action == 'update' && $user->rights->immobilier->property->write) {
	$error = 0;
	
	$datect = dol_mktime(0, 0, 0, GETPOST('datectmonth', 'int'), GETPOST('datectday', 'int'), GETPOST('datectyear', 'int'));
	$datectend = dol_mktime(0, 0, 0, GETPOST('datectendmonth', 'int'), GETPOST('datectendday', 'int'), GETPOST('datectendyear', 'int'));
	
	// if ((GETPOST('preavis')>0) && dol_strlen ( $datectend ) != 0) {
	// setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Date fin")), 'errors');
	// $error++;
	// }
	
	if (! $error) {
		$object = new Rent($db);
		$result = $object->fetch($id);
		
		$object->fk_property = GETPOST("fk_property");
		$object->date_start = $datect;
		$object->preavis = GETPOST('preavis');
		$object->date_end = $datectend;
		$object->montant_tot =  GETPOST("loyer") + GETPOST("charges");
		$object->loyer = GETPOST("loyer");
		$object->charges = GETPOST("charges");
		$object->tva = GETPOST('tva_value','alpha');
		$object->periode = GETPOST("periode");
		$object->depot = GETPOST("depot");
		$object->commentaire = GETPOST("commentaire");
		$object->proprietaire_id = GETPOST("proprietaire_id");
		$object->id = $id;
		$object->fk_user_author	= $user->id;
		
		$e_contrat = $object;
		
		$res = $object->update($user);
		
		if ($res >= 0) {
			setEventMessage($langs->trans("RentAdded"), 'mesgs');
		} else
			setEventMessage($object->error, 'errors');
		
		header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
	}
}

/*
 * View
 */
llxheader('', $langs->trans("RentCard") . ' | ' . $langs->trans("Card"), '');

// Create mode
if ($action == 'create' && $user->rights->immobilier->rent->write) {
	
	print load_fiche_titre($langs->trans("NewRent"));
	
	print '<form name="create" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
	print '<input type="hidden" name="action" value="add">' . "\n";
	
	dol_fiche_head('');
	
	print '<table class="border" width="100%">';
	print '<tbody>';
	
	// Name Property
	print '<tr>';
	print '<td class="titlefield"><span class="fieldrequired"><label for="nameproperty">' . $langs->trans("Property") . '</label></td>';
	print '<td>';
	print $htmlimmo->select_property($object->fk_property, 'fk_property');
	print '</td></tr>';
	
	// Name Renter
	print '<tr>';
	print '<td class="fieldrequired"><label for="renter">' . $langs->trans("Renter") . '</label></td>';
	print '<td>';
	print $htmlimmo->select_renter($object->fk_renter, 'fk_renter');
	print '</td></tr>';
	
	// Date
	print '<tr>';
	print '<td><label for="date">' . $langs->trans("DateBeginningLease") . '</label></td>';
	print '<td align="left">';
	print $form->select_date('', 'datecontract', '', '', 1);
	print '</td></tr>';
	
	// Income rent
	print '<tr>';
	print '<td><label for="loyer">' . $langs->trans("AmountHC") . '</label></td>';
	print '<td><input name="loyer" id="loyer" size="10" value="' . price($object->loyer) . '" </td></tr>';
	
	print '<tr>';
	print '<td><label for="charges">' . $langs->trans("Charges") . '</label></td>';
	print '<td><input name="charges" id="charges" size="10" value="' . price($object->charges) . '" ></td></tr>';
	print '<tr>';
	
	print '<tr>';
	print '<td><label for="amount">' . $langs->trans("AmountTC") . '</label></td>';
	print '<td><input name="montant_tot" id="amount" size="10" value="' . price($object->montant_tot) . '" disabled="disabled"></td></tr>';
	
	
	print '<tr><td>'.fieldLabel('VAT','tva_value').'</td><td>';
    print $form->selectyesno('tva_value',$object->tva,1);
    print '</td>';
	

	/*
	print '<tr>';
	print '<td><label for="periode">' . $langs->trans("Period") . '</label></td>';
	print '<td><input name="periode" id="periode" size="10" value="' . $object->periode . '"></td></tr>';
	*/

	print '<tr>';
	print '<td><label for="depot">' . $langs->trans("Caution") . '</label></td>';
	print '<td><input name="depot" size="10" id="depot" value="' . $object->depot . '"></td></tr>';
	print '<tr>';
	print '<td><label for="note">' . $langs->trans("Note") . '</label></td>';
	print '<td><input name="commentaire" id="note" size="10" value="' . $object->commentaire . '"></td></tr>';
	print '<tr>';
	print '<td><label for="owner">' . $langs->trans("Owner") . '</label></td>';
	print '<td><input name="proprietaire_id" id="owner" size="10" value="' . $object->proprietaire_id . '"></td></tr>';
	
	print '</tbody>';
	print '</table>';
	
	dol_fiche_end();
	
	print '<div align="center">';
	print '<input type="submit" value="' . $langs->trans("Save") . '" name="bouton" class="button" />';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="' . $langs->trans("Cancel") . '" class="button" onclick="history.go(-1)" />';
	print '</div>';

	
	
	print '</form>';
} else {
	
	if ($id > 0) {
		
		// Edit mode
		if ($action == 'edit') {
			$object = new Rent($db);
			$result = $object->fetch($id);
			
			if ($result < 0) {
				setEventMessages(null, $object->errors, 'errors');
			}
			
			print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="id" value="' . $id . '">';
			
			$head = rent_prepare_head($object);
			dol_fiche_head($head, 'card', $langs->trans("RentCard"), 0, 'rent@immobilier');

			$linkback = '<a href="' . DOL_URL_ROOT . '/immobilier/rent/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

			print '<table class="border" width="100%">';

			print '<tr>';
			print '<td class="titlefield">'.fieldLabel('Property','fk_property',1).'</td>';
			print '<td>';
			print $htmlimmo->select_property($object->fk_property, 'fk_property');
			print '</td></tr>';

			print '<tr>';
			print '<td><label for="renter">' . $langs->trans("Renter") . '</label></td>';
			print '<td>' . $object->nomlocataire . ' ' . $object->firstname_renter . '</td>';
			print '</tr>';

			print '<tr>';
			print '<td><label for="datect">' . $langs->trans("DateStart") . '</label></td>';
			print '<td align="left">';
			print $html->select_date($object->date_start, 'datect', 0, 0, 0, 'fiche_contrat', 1);
			print '</td></tr>';

			print '<tr>';
			print '<td><label for="datectend">' . $langs->trans("DateEnd") . '</label></td>';
			print '<td align="left">';
			print $html->select_date($object->date_end, 'datectend', 0, 0, 0, 'fiche_contrat', 1);

			print '<tr>';
			print '<td><label for="preavis">' . $langs->trans("Preavis") . '</label></td>';
			if ($object->preavis) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			print '<td><input type="checkbox" id="preavis" name="preavis" ' . $checked . ' value="1"></td></tr>';

			print '<tr>';
			print '<td>'.fieldLabel('AmountHC','loyer',0).'</td>';
			print '<td><input name="loyer" id=loyer" size="10" value="' . price($object->loyer) . '" </td></tr>';

			print '<tr>';
			print '<td>'.fieldLabel('Charges','charges',0).'</td>';
			print '<td><input name="charges" id="charges" size="10" value="' . price($object->charges) . '"  </td></tr>';

			print '<tr>';
			print '<td>'.fieldLabel('AmountTC','amount',1).'</td>';
			print '<td><input name="montant_tot" id="amount" size="10" value="' . price($object->montant_tot) . '" disabled="disabled"></td></tr>';
			
			print '<tr><td>'.fieldLabel('VAT','tva_value',1).'</td><td>';
			print $form->selectyesno('tva_value',$object->tva,1);
			print '</td>';

			/*
			print '<tr>';
			print '<td><label for="periode">' . $langs->trans("Period") . '</label></td>';
			print '<td><input name="periode" id=periode" size="10" value="' . $object->periode . '"</td></tr>';
			*/

			print '<tr>';
			print '<td><label for="depot">' . $langs->trans("Caution") . '</label></td>';
			print '<td><input name="depot" size="10" value="' . price($object->depot) . '"</td></tr>';
			
			// Public note
			print '<tr>';
			print '<td class="border" valign="top">'.fieldLabel('NotePublic','note',0).'</td>';
			print '<td valign="top" colspan="2">';

			$doleditor = new DolEditor('note', $object->commentaire, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, 70);
			print $doleditor->Create(1);
			print '</td></tr>';
			
			print '<tr>';
			print '<td><label for="owner">' . $langs->trans("Owner") . '</label></td>';
			print '<td><input name="proprietaire_id" id="owner" size="10" value="' . $object->proprietaire_id . '"</td></tr>';
			
			print '</table>';
			dol_fiche_end();
			
			print '<div class="center">';
			print '<input type="submit" value="' . $langs->trans("Modify") . '" name="bouton" class="button">';
			print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="' . $langs->trans("Cancel") . '" class="button" onclick="history.go(-1)" />';
			print '</div>';

		
			
			
			
			print '</form>';
		} else {
		
		// Display contract card
			$object = new Rent($db);
			$result = $object->fetch($id);
			
			if ($result < 0) {
				setEventMessages(null, $object->errors, 'errors');
			}
			
			if ($result) {
			// View mode
				$head = rent_prepare_head($object);
				dol_fiche_head($head, 'card', $langs->trans("RentCard"), 0, 'rent@immobilier');

				if ($mesg)
					print $mesg . "<br>";
				
				print '<table class="border" width="100%">';

				$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

				// Ref
				print '<tr><td class="titlefield">'.$langs->trans("Ref").'</td><td>';
				print $form->showrefnav($object, 'id', $linkback, 1, 'rowid', 'ref', '');
				print '</td></tr>';

				print '<tr>';
				print '<td>' . $langs->trans("NameProperty") . '</td>';
				print '<td>' . $object->nomlocal . '</td>';
				print '</tr>';
				
				print '<tr>';
				print '<td>' . $langs->trans("Renter") . '</td>';
				print '<td>' . $object->nomlocataire . ' ' . $object->firstname_renter . '</td>';
				print '</tr>';
				
				print '<tr>';
				print '<td>' . $langs->trans("DateStart") . '</td>';
				print '<td>' . dol_print_date($object->date_start,"day") . '</td>';
				print '</tr>';
				
				print '<tr>';
				print '<td>' . $langs->trans("DateEnd") . '</td>';
				print '<td>' . dol_print_date($object->date_end,"day") . '</td>';
				print '</tr>';
				
				print '<tr>';
				print '<td>' . $langs->trans("preavis") . '</td>';
				print '<td>' . $object->preavis . '</td>';
				print '</tr>';

				print '<tr>';
				print '<td>' . $langs->trans("AmountHC") . '</td>';
				print '<td>' . price($object->loyer) . '</td>';
				print '</tr>';

				print '<tr>';
				print '<td>' . $langs->trans("Charges") . '</td>';
				print '<td>' . price($object->charges) . '</td>';
				print '</tr>';

				print '<tr>';
				print '<td>' . $langs->trans("AmountTC") . '</td>';
				print '<td>' . price($object->montant_tot) . '</td>';
				print '</tr>';
				
				// VAT payers
				print '<tr><td>';
				print $langs->trans('VAT');
				print '</td><td>';
				print yn($object->tva);
				print '</td>';
				print '</tr>';
		
				print '<tr>';
				print '<td>' . $langs->trans("Caution") . '</td>';
				print '<td>' . price($object->depot) . '</td>';
				print '</tr>';

				print '<tr>';
				print '<td>' . $langs->trans("Note") . '</td>';
				print '<td>' . $object->commentaire . '</td>';
				print '</tr>';
				
				print '</table>';
				dol_fiche_end();
				
				print '</form>';
			}
		}
	}
}

/*
 * Actions bar
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit') {
	/* Si l'?tat est "activ?" ou "d?sactiv?"
	 *	ET user ? droit "creer/supprimer"
	 * 	Afficher : "Modifier" / "Supprimer"
	 */
	if ($user->rights->immobilier->rent->write) {
		// Modify
		print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=edit&id=' . $object->id . '">' . $langs->trans('Modify') . '</a>';
		
		// Delete
		if ($user->rights->immobilier->rent->delete) {
			print '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?action=delete&id=' . $object->id . '">' . $langs->trans('Delete') . '</a>';
		}
	}
}
print '</div>';

llxFooter();

$db->close();
