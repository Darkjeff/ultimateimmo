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


 
llxHeader('',$langs->trans('Charges'));

/*
 * View
 */
print_fiche_titre($langs->trans("Charges"));
?>

	
	<div class="container">
		<section>
			<table id="dataTable" class="display" cellspacing="0" width="100%">
				<thead>
					<tr class="liste_titre">
						<th><?php echo $langs->trans('Reference'); ?></th>
						<th><?php echo $langs->trans('Libelle'); ?></th>
						<th><?php echo $langs->trans('Local'); ?></th>
						<th><?php echo $langs->trans('Montant TTC'); ?></th>
						<th><?php echo $langs->trans('Date acquittement'); ?></th>
						<th><?php echo $langs->trans("Dispatch"); ?></th>
						<th><?php echo $langs->trans("Company"); ?></th>
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
                        {"sClass": "right"},
                        {"sClass": "center"},
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