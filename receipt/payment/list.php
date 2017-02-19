<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
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

// Dolibarr environment
$res = @include ("../../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

$langs->load('immobilier@immobilier');

// Securite acces client
if ($user->societe_id > 0)
	accessforbidden();


 
llxHeader('',$langs->trans('Payments'));

/*
 * View
 */
print_fiche_titre($langs->trans("ListPayment"));
?>
	
	<div class="container">
		<section>
			<table id="dataTable" class="display" cellspacing="0" width="100%">
				<thead>
					<tr class="liste_titre">
						<th><?php echo $langs->trans('Reference'); ?></th>
						<th><?php echo $langs->trans('DatePayment'); ?></th>
						<th><?php echo $langs->trans('Renter'); ?></th>
						<th><?php echo $langs->trans('nomlocal'); ?></th>
						<th><?php echo $langs->trans('nomloyer'); ?></th>
						<th><?php echo $langs->trans("Amount"); ?></th>
						<th><?php echo $langs->trans("Comment"); ?></th>
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
                        {"sClass": "left"},
                        {"sClass": "left"},
                        {"sClass": "left"},
                        {"sClass": "right"},
                        {"sClass": "center"},
                        {"sClass": "center"},
						],
						"order": [[ 1, "desc" ]]
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
