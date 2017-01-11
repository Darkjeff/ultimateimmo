<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2016		Jamelbaz			<jamelbaz@gmail.com>
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

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

$langs->load('immobilier@immobilier');

// Security check
if (! $user->rights->immobilier->rent->read)
	accessforbidden();
$action = GETPOST('action', 'alpha');

// write income

if ($action == 'validaterent') {
	
	$error = 0;
	
	$db->begin();
	
	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt as lo ";
	$sql1 .= " SET lo.paiepartiel=";
	$sql1 .= "(SELECT SUM(p.amount)";
	$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_payment as p";
	$sql1 .= " WHERE lo.rowid = p.fk_receipt";
	$sql1 .= " GROUP BY p.fk_receipt )";
	
	// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		setEventMessage($db->lasterror(), 'errors');
	} else {
		
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
		$sql1 .= " SET paye=1";
		$sql1 .= " WHERE amount_total=paiepartiel";
		
		// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
		$resql1 = $db->query($sql1);
		if (! $resql1) {
			$error ++;
			setEventMessage($db->lasterror(), 'errors');
		}
		
		if (! $error) {
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
			$sql1 .= " SET balance=amount_total-paiepartiel";
			
			// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
			$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
			
			if (! $error) {
				$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_contrat as ic";
				$sql1 .= " SET ic.encours=";
				$sql1 .= "(SELECT SUM(il.balance)";
				$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
				$sql1 .= " WHERE ic.rowid = il.fk_contract";
				$sql1 .= " GROUP BY il.fk_contract )";
				
				$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
				
				$db->commit();
				
				setEventMessage('Loyer mis a jour avec succes', 'mesgs');
			}
		} else {
			$db->rollback();
			setEventMessage($db->lasterror(), 'errors');
		}
	}
}




 
llxHeader('',$langs->trans('ImmoReceipts'));

/*
 * View
 */
print_fiche_titre($langs->trans("ListReceipts"));
?>
	
	<div class="container">
		<section>
			<table id="dataTable" class="display" cellspacing="0" width="100%">
				<thead>
					<tr class="liste_titre">
						<th><?php echo $langs->trans('Reference'); ?></th>
						<th><?php echo $langs->trans('Renter'); ?></th>
						<th><?php echo $langs->trans('Property'); ?></th>
						<th><?php echo $langs->trans('Rent'); ?></th>
						<th><?php echo $langs->trans('Echeance'); ?></th>
						<th><?php echo $langs->trans("Montant total"); ?></th>
						<th><?php echo $langs->trans("re&ccedilu"); ?></th>
						<th><?php echo $langs->trans("Paiement"); ?></th>
						<th><?php echo $langs->trans("Owner"); ?></th>
						<th><?php echo $langs->trans("Action"); ?></th>
					</tr>
				</thead>
				
			</table>
		</section>
	</div>

	<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function() {
			$('#dataTable').DataTable( {
				"processing": true,
				"serverSide": true,
				"ajax": "server_processing.php",

				"aoColumns": [ 
                        {"sClass": "left"},
                        {"sClass": "center"},
                        {"sClass": "center"},
                        {"sClass": "center"},
                        {"sClass": "center"},
                        {"sClass": "center"},
                        {"sClass": "right"},
                        {"sClass": "center"},
                        {"sClass": "left"},
                        {"sClass": "center"},
						],
						 "order": [[ 0, "desc" ]]
			});

			$("table#dataTable").on("click", ".delete", function(event) {
			event.preventDefault();
			var r=confirm("Êtes-vous sûr de vouloir supprimer ?");
			if (r==true)   {  
			   window.location = $(this).attr('href');
			}

			});
		});
	</script>

<?php llxFooter();?>




