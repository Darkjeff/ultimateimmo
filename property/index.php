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
if (! $user->rights->immobilier->property->read)
	accessforbidden();

dol_include_once('/immobilier/class/immoproperty.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$properties = new Immoproperty($db);
$propertystatic=new Immoproperty($db);

$prps = $properties->fetchAll($year_current);

llxHeader('',$langs->trans('Properties'));

/*
 * View
 */
print_fiche_titre($langs->trans("ListProperties"));
?>
	<div class="container">
		<section>
			<table id="dataTable" class="display" cellspacing="0" width="100%">
				<thead>
					<tr class="liste_titre">
						<th><?php echo $langs->trans('Name'); ?></th>
						<th><?php echo $langs->trans('Address'); ?></th>
						<th><?php echo $langs->trans('Zip'); ?></th>
						<th><?php echo $langs->trans('Town'); ?></th>
						<th><?php echo $langs->trans('Country'); ?></th>
						<th><?php echo $langs->trans("TypeProperty"); ?></th>
						<th><?php echo $langs->trans("Building"); ?></th>
						<th><?php echo $langs->trans("Owner"); ?></th>
						<th><?php echo $langs->trans("Status"); ?></th>
						<th><?php echo $langs->trans("Action"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($prps as $prp): ?>
					<tr>
						<td><a href="card.php?id=<?php print $prp['id'] ?>"><?php print img_object($langs->trans("ShowProperty").': '. $prp['name'], 'building@immobilier' ) . " " . $prp['name']?></a></td>
						<td><?php print $prp['address']; ?></td>
						<td><?php print $prp['zip']; ?></td>
						<td><?php print $prp['town']; ?></td>
						<td><?php print getCountry($prp['fk_pays'],0); ?></td>
						<td><?php print $prp['type_label']; ?></td>
						<td><?php print $prp['building']; ?></td>
						<td><?php print $prp['owner_name']; ?></td>
						<td><?php print $propertystatic->LibStatut($prp['statut'],5); ?></td>
						<td align="center">
							<a href="card.php?action=edit&id=<?php print $prp['id']; ?>"><?php print img_edit() ?></a> 
							<a class="delete" href="card.php?action=confirm_delete&confirm=yes&id=<?php print $prp['id']; ?>"><?php print img_delete() ?></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>
	</div>

	<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function() {
			$('#dataTable').DataTable( {
				language: {
					processing:     "Traitement en cours...",
					search:         "Rechercher&nbsp;:",
					lengthMenu:    "Afficher _MENU_ &eacute;l&eacute;ments",
					info:           "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
					infoEmpty:      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
					infoFiltered:   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
					infoPostFix:    "",
					loadingRecords: "Chargement en cours...",
					zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
					emptyTable:     "Aucune donnée disponible dans le tableau",
					paginate: {
						first:      "Premier",
						previous:   "Pr&eacute;c&eacute;dent",
						next:       "Suivant",
						last:       "Dernier"
					},
					aria: {
						sortAscending:  ": activer pour trier la colonne par ordre croissant",
						sortDescending: ": activer pour trier la colonne par ordre décroissant"
					}
				}
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