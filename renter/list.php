<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2016		Jamelbaz			<jamelbaz@gmail.com>
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

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

$langs->load('immobilier@immobilier');

// Security check
if (! $user->rights->immobilier->renter->read)
	accessforbidden();

dol_include_once('/immobilier/class/immorenter.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$renters = new Renter($db);
$renterstatic=new Renter($db);
$thirdparty_static = new Societe($db);
	
$renters = $renters->fetchAll($year_current);

llxHeader('',$langs->trans('Renters'));

/*
 * View
 */
print_fiche_titre($langs->trans("ListRenters"));
?>
	<div class="container">
		<section>
			<table id="dataTable" class="display" cellspacing="0" width="100%">
				<thead>
					<tr class="liste_titre">
						<th><?php echo $langs->trans('Renter'); ?></th>
						<th><?php echo $langs->trans('Company'); ?></th>
						<th><?php echo $langs->trans('Phone'); ?></th>
						<th><?php echo $langs->trans('PhoneMobile'); ?></th>
						<th><?php echo $langs->trans('Email'); ?></th>
						<th><?php echo $langs->trans("Statut"); ?></th>
						<th><?php echo $langs->trans("Action"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($renters as $renter): ?>
					<tr>
						<td><a href="card.php?id=<?php print $renter['id'] ?>"><?php print img_object($langs->trans("ShowRenter").': '. $renter['name'], 'user' ) . " " . $renter['NomPrenom']; ?></a></td>
						<td>
						<?php 
						if(!empty($renter['Company'])){
							$thirdparty_static->id=$renter['Company_id'];
							$thirdparty_static->name=$renter['Company'];
							echo $thirdparty_static->getNomUrl(1);
						}

						?>
						</td>
						<td><?php print $renter['Phone']; ?></td>
						<td><?php print $renter['Phonemobile']; ?></td>
						<td><?php print $renter['Email']; ?></td>
						<td><?php print $renterstatic->LibStatut($renter['state']); ?></td>
						<td align="center">
							<a href="card.php?action=edit&id=<?php print $renter['id']; ?>"><?php print img_edit() ?></a>
							<a class="delete" href="card.php?action=confirm_delete&confirm=yes&id=<?php print $renter['id']; ?>"><?php print img_delete() ?></a>
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

		} );
	</script>

<?php llxFooter();?>