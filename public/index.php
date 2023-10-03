<?php
/* Copyright (C) 2013-2016	Jean-François FERRY <hello@librethic.io>
 * Copyright (C) 2018-2019  Philippe GRAND  	<philippe.grand@atoo-net.com>
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
 *       \file       ultimateimmo/public/index.php
 *       \ingroup    ultimateimmo
 *       \brief      Public file to add and manage rent
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');            // Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');        // Do not check anti POST attack test
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');            // If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
/*if (!defined("NOLOGIN")) {
    define("NOLOGIN", '1');
}*/
// If this page is public (can be called outside logged session)

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/lib/security.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
dol_include_once('/ultimateimmo/lib/ultimateimmo.lib.php');

// Load translation files required by the page
$langs->loadLangs(array('companies', 'other', 'ultimateimmo@ultimateimmo', 'errors', "contracts", "bills", "compta"));

// Get parameters

$action = GETPOST('action', 'alpha');

/*
 * Action
 */
if ($action == 'logout') {
	$_SESSION["urlfrom"] = dol_buildpath('/ultimateimmo/public/index.php', 1);
	header("Location: " . dol_buildpath('/user/logout.php', 2) . '?token=' . newToken()); // Default behaviour is redirect to index.php page
	exit;
}


/*
 * View
 */

if (empty($conf->global->ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE)) {
	print $langs->trans('UltimateimmoPublicInterfaceForbidden');
	exit;
}


$form = new Form($db);

llxHeaderUltimateImmoPublic();
?>
	<script type="text/javascript">
		$(document).ready(function () {
			const loadingModal = document.getElementById("loadingModal");
			const bsModalLoading = new bootstrap.Modal(loadingModal);

        	const errorModal = document.getElementById("errorModal");
			let bsModalError = new bootstrap.Modal(errorModal);

			const inputCompteurModal = document.getElementById('inputCompteurModal');
			const bsinputCompteurModal = new bootstrap.Modal(inputCompteurModal);
			$('#validCounter').click(function () {
				bsModalLoading.show();
				$.ajax({
					type: "POST",
					url: "<?php echo dol_buildpath('/ultimateimmo/ajax/publiccounter.php', 2); ?>",
					data: {
						action: "validCounterInput",
						token: "<?php echo currentToken();?>",
						immoProperty: $('#counterProperty').val(),
						counterValue: $('#counterValue').val(),
					},
					dataType: "json"
				}).done(function (response) {
					location.reload();
				}).fail(function (response) {
					bsinputCompteurModal.hide();
					console.log("Error in ajax call");
					console.log(response);
					setTimeout(function () {
						bsModalLoading.hide();
						$('#errorModalText').text(response.responseJSON)
						bsModalError.show();
					}, 500);
				});
			});


		});
	</script>
<?php
if (empty($user->id)) {
	dol_include_once('/core/tpl/login.tpl.php');
} else {

	dol_include_once('/ultimateimmo/class/immorenter.class.php');
	//var_dump($user);
	$renter = new ImmoRenter($db);

	$userLinkid = 0;

	$sql = 'SELECT renter.rowid as renterId FROM ' . MAIN_DB_PREFIX . 'socpeople as sp ';
	$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'user as u ON u.fk_socpeople=sp.rowid';
	$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . $renter->table_element . ' as renter ON renter.fk_soc=sp.fk_soc';
	$sql .= ' WHERE sp.fk_soc=' . (int)$user->socid;
	$sql .= ' AND u.rowid="' . $user->id . '"';
	$sql .= ' AND sp.email="' . $user->email . '"';
	$sql .= ' AND sp.rowid="' . $user->contact_id . '"';
	$resql = $db->query($sql);
	if ($resql < 0) {
		setEventMessages($db->lasterror, null, 'errors');
	} else {
		$num = $db->num_rows($resql);
		if ($num > 0) {
			while ($obc = $db->fetch_object($resql)) {
				$renterId = $obc->renterId;
			}
		}
	}

	$sql = "SELECT DISTINCT rec.rowid as reference, rec.label as receiptname,rec.ref as receiptref, loc.lastname as nom, ";
	$sql .= " prop.address, prop.label as local, loc.status as status, rec.total_amount as total, rec.partial_payment, ";
	$sql .= " rec.balance, rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_owner,";
	$sql .= " rec.fk_rent as refcontract, rent.preavis,";
	$sql .= " rec.date_echeance, rent.preavis";
	$sql .= " ,prop.rowid as propid";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter AND loc.rowid=" . (int)$renterId;
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property";
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent AND rent.preavis=1";
	$sql .= $db->order('rec.date_echeance', 'DESC');
	$resql = $db->query($sql);
	$datas = [];
	$resultDataProperty = array();
	$totalBalance = 0;
	if ($resql < 0) {
		setEventMessages($db->lasterror, null, 'errors');
	} else {
		$num = $db->num_rows($resql);
		if ($num > 0) {
			while ($objp = $db->fetch_object($resql)) {

				$objref = dol_sanitizeFileName($objp->receiptref);
				$relativepath = $objref . '/' . $objref . '.pdf';
				$filedir = $conf->ultimateimmo->dir_output . '/receipt' . '/' . $objref;
				if (file_exists($filedir . '/' . $objref . '.pdf')) {
					$urldlfile = dol_buildpath('/document.php', 2) . '?modulepart=ultimateimmo&file=receipt/' . $relativepath;
				}

				$datas[$objp->reference]['date_echeance'] = $objp->date_echeance;
				$datas[$objp->reference]['local'] = $objp->local;
				$datas[$objp->reference]['total'] = $objp->total;
				$datas[$objp->reference]['partial_payment'] = $objp->partial_payment;
				$datas[$objp->reference]['balance'] = $objp->balance;
				$datas[$objp->reference]['receiptref'] = $objp->receiptref;
				$datas[$objp->reference]['urldlfile'] = $urldlfile;
				$resultDataProperty[$objp->propid]=$objp->local;

				$totalBalance += (float)$objp->balance;

			}
		}
	}

	if (!empty($datas)) {
		dol_include_once('/ultimateimmo/class/immocompteur.class.php');
		$compteur = new ImmoCompteur($db);
		$sql = 'SELECT ';
		$sql .= $compteur->getFieldList('compteur');
		$sql .= ',prop.label as local, ict.label as label_compteur, ict.rowid as typecounterid, YEAR(compteur.date_relever) as yearrelever';
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter AND loc.rowid=" . (int)$renterId;
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent AND rent.preavis=1";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocompteur as compteur ON compteur.fk_immoproperty=rec.fk_property";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "c_ultimateimmo_immocompteur_type as ict ON ict.rowid=compteur.compteur_type_id";
		$sql .= " WHERE 1=1";
		$sql .= " AND YEAR(compteur.date_relever)=YEAR(rec.date_echeance)";
		$sql .= $db->order('rec.date_echeance', 'DESC');
		$resultDataCompteur = array();

		$resql = $db->query($sql);
		if ($resql < 0) {
			setEventMessages($db->lasterror, null, 'errors');
		} else {
			$num = $db->num_rows($resql);
			if ($num > 0) {

				while ($obj = $db->fetch_object($resql)) {
					$newObj = new stdClass();
					if (array_key_exists($obj->fk_immoproperty, $resultDataCompteur)
					&& array_key_exists($obj->yearrelever, $resultDataCompteur[$obj->fk_immoproperty])
					&& array_key_exists($obj->typecounterid, $resultDataCompteur[$obj->fk_immoproperty][$obj->yearrelever])) {
						if ($obj->date_relever < $resultDataCompteur[$obj->fk_immoproperty][$obj->yearrelever][$obj->typecounterid]->dt_first) {
							$newObj->dt_first = $obj->date_relever;
							$newObj->value_cpt_dt_first = $obj->qty;
						} else {
							$newObj = $resultDataCompteur[$obj->fk_immoproperty][$obj->yearrelever][$obj->typecounterid];
						}
						if ($obj->date_relever > $resultDataCompteur[$obj->fk_immoproperty][$obj->yearrelever][$obj->typecounterid]->dt_last) {
							$newObj->dt_last = $obj->date_relever;
							$newObj->value_cpt_dt_last = $obj->qty;
						}
					} else {
						$newObj->dt_first = $obj->date_relever;
						$newObj->value_cpt_dt_first = $obj->qty;
						$newObj->dt_last = $obj->date_relever;
						$newObj->value_cpt_dt_last = $obj->qty;
					}
					$newObj->fk_immoproperty = $obj->fk_immoproperty;
					$newObj->local = $obj->local;
					$newObj->label_compteur = $obj->label_compteur;
					$newObj->conso = $newObj->value_cpt_dt_last  - $newObj->value_cpt_dt_first;

					$resultDataCompteur[$obj->fk_immoproperty][$obj->yearrelever][$obj->typecounterid] = $newObj;
				}
				$db->free($resql);
			}
		}
	}


	$sqlBilan = "(SELECT l.date_start as date , l.total_amount as debit, 0 as credit , l.label as des";
	$sqlBilan .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
	$sqlBilan .= " WHERE  l.fk_renter =" . (int)$renterId;
	$sqlBilan .= ")";
	$sqlBilan .= "UNION (SELECT p.date_payment as date, 0 as debit, p.amount as credit, p.note_public as des";
	$sqlBilan .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sqlBilan .= " WHERE p.fk_renter =" . (int)$renterId;
	$sqlBilan .= ")";
	$sqlBilan .= $db->order('date', 'DESC');
	$resultDataBilan = array();
	$resql = $db->query($sqlBilan);
	if ($resql < 0) {
		setEventMessages($db->lasterror, null, 'errors');
	} else {
		$num = $db->num_rows($resql);
		if ($num > 0) {
			while ($objp = $db->fetch_object($resql)) {
				$resultDataBilan[]=$objp;
			}
		}
	}

	$sql2 = "SELECT SUM(l.total_amount) as debit, 0 as credit ";
	$sql2 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
	$sql2 .= " WHERE l.fk_renter =" . (int)$renterId;


	$sql3 = "SELECT 0 as debit , sum(p.amount) as credit";
	$sql3 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sql3 .= " WHERE p.fk_renter =" . (int)$renterId;

	$result2 = $db->query ( $sql2 );
	if ($result2 < 0) {
		setEventMessages($db->lasterror, null, 'errors');
	}
	$result3 = $db->query ( $sql3 );
	if ($result3 < 0) {
		setEventMessages($db->lasterror, null, 'errors');
	}
	$objp2 = $db->fetch_object ( $result2 );
	$objp3 = $db->fetch_object ( $result3 );
	$newObj = new stdClass();
	$newObj->debit = $objp2->debit;
	$newObj->debit = $objp3->credit;
	$newObj->balance = (float)$objp3->credit-(float)$objp2->debit;
	$resultDataBilan['total']=$newObj;

	?>
	<main>
		<div class="container">
			<div class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
				<div class="col-md-3">
					<span class="fs-4"><?= $langs->trans('Renter') . ': ' . $user->getFullName($langs); ?></span>
				</div>
				<div class="col-md-3 text-md-center">
						<span
							class="fs-4"><?= $langs->trans('DetteLocative') . ': ' . price($totalBalance, 0, $langs, 1, -1, -1, $conf->currency) ?></span>
				</div>
				<div class="col-md-3 text-md-end">
						<?php
					if (!empty($resultDataProperty))
					{
						?>
					<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#inputCompteurModal"><?= $langs->trans('InputCounterWarter');?></button>
						<?php
					}
						?>
				</div>
				<div class="col-md-3 text-md-end">
					<a href="<?= $_SERVER['PHP_SELF'] . '?action=logout&token=' . newToken() ?>">
						<i class="fa fa-2x fa-sign-out" aria-hidden="true"></i>
					</a>
				</div>
			</div>
		</div>
		<div class="container" id="recepit">
			<div class="row">
				<div class="col-md-12 text-md-center">
					<span class="fs-4"><?= $langs->trans('MenuListImmoReceipt'); ?></span>
				</div>
			</div>
			<div class="col row border border-dark rounded-4 table-responsive-md overflow-scroll"
				 style="max-height: 500px;">
				<table class="table table-striped table-bordered table-primary">
					<thead>
					<tr>
						<th scope="col"><?= $langs->trans('NomLoyer') ?></th>
						<th scope="col"><?= $langs->trans('Date') ?></th>
						<th scope="col"><?= $langs->trans('Nomlocal') ?></th>
						<th scope="col"><?= $langs->trans('TotalAmount') ?></th>
						<th scope="col"><?= $langs->trans('PartialPayment') ?></th>
						<th scope="col"><?= $langs->trans('DetteLocative') ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					if (!empty($datas)) {
						foreach ($datas as $data) {
							?>
							<tr>
								<th scope="row"><a href="<?= $data['urldlfile'] ?>" target="_blank"><i
											class="fa fa-file-pdf px-1"
											aria-hidden="true"></i><?= $data['receiptref'] ?></a></th>
								<td><?= dol_print_date($data['date_echeance']) ?></td>
								<td><?= $data['local'] ?></td>
								<td><?= price($data['total']) ?></td>
								<td><?= price($data['partial_payment']) ?></td>
								<td><?= price($data['balance']) ?></td>
							</tr>
							<?php

						}
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php if (!empty($resultDataCompteur)) { ?>
		<div class="container" id="dataCompteur">
			<div class="row">
				<div class="col-md-12 text-md-center">
					<span class="fs-4"><?= $langs->trans('MenuImmoCompteur'); ?></span>
				</div>
			</div>
			<div class="col row border border-dark rounded-4 table-responsive-md overflow-scroll"
				 style="max-height: 500px;">
				<table class="table table-striped table-bordered table-secondary">
						<thead>
						<tr>
							<th scope="col"><?= $langs->trans('Year') ?></th>
							<th scope="col"><?= $langs->trans('ImmoCompteurType') ?></th>
							<th scope="col"><?= $langs->trans('Nomlocal') ?></th>
							<th scope="col"><?= $langs->trans('Consommation') ?></th>
						</tr>
						</thead>
						<tbody>
							<?php
							if (!empty($resultDataCompteur)) {
								foreach ($resultDataCompteur as $propertyId=>$dataByProperty) {
									foreach ($dataByProperty as $yearRelever=>$dataByYear) {
										foreach ($dataByYear as $counterType=>$dataByCounterType) {

									?>
									<tr>
										<th scope="row"><?= $yearRelever ?></th>
										<td><?= $dataByCounterType->label_compteur ?></td>
										<td><?= $dataByCounterType->local ?></td>
										<td><?= $dataByCounterType->conso ?></td>
									</tr>
									<?php
										}
									}
								}
							}
						?>
						</tbody>
				</table>
			</div>
		</div>
		<?php } ?>
		<?php if (!empty($resultDataBilan)) { ?>
		<div class="container" id="dataBilan">
			<div class="row">
				<div class="col-md-12 text-md-center">
					<span class="fs-4"><?= $langs->trans('DetailPayment'); ?></span>
				</div>
			</div>
			<div class="col row border border-dark rounded-4 table-responsive-md overflow-scroll"
				 style="max-height: 500px;">
				<table class="table table-striped table-bordered table-info">
					<thead>
					<tr>
						<th scope="col"><?= $langs->trans('Date') ?></th>
						<th scope="col"><?= $langs->trans('Debit') ?></th>
						<th scope="col"><?= $langs->trans('Credit') ?></th>
						<th scope="col"><?= $langs->trans('Description') ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
						foreach ($resultDataBilan as $key=>$data) {
							if ($key !== 'total') {
							?>
						<tr>
							<th scope="row"><?= dol_print_date ( $db->jdate ( $data->date ), 'day' ) ?></th>
							<td><?= price($data->debit) ?></td>
							<td><?= price($data->credit) ?></td>
							<td><?= $data->des ?></td>
						</tr>
					<?php
							}
						}
							?>
					</tbody>
					<tfoot>
						<th scope="row"><?= $langs->trans("Total")  ?></th>
						<td><?= price($resultDataBilan['total']->debit) ?></td>
						<td><?= price($resultDataBilan['total']->credit) ?></td>
						<td><?= price($resultDataBilan['total']->balance) ?></td>
					</tfoot>
				</table>
			</div>
		<?php } ?>

			<!-- Input Compteur Modal -->
			<div class="modal fade" id="inputCompteurModal" tabindex="-1"
			aria-labelledby="inputCompteurModal" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title"
								id="inputCompteurModalLabel"><?= $langs->trans('InputCounterWarter');?></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"
									aria-label="<?=  $langs->trans('Close'); ?>"></button>
						</div>
						<div class="modal-body">
							<div class="row pt-2">
								<label for="counterProperty" class="form-label"><?=  $langs->trans('Property');?></label>
								<select class="form-select" id="counterProperty" name="counterProperty" aria-label="<?=  $langs->trans('Property');?>">
									<?php
										foreach($resultDataProperty as $propid=>$proplabel) {
											?>
												<option value="<?= $propid?>"><?= $proplabel?></option>
											<?php
										}
									?>
								</select>
							</div>
							<div class="row pt-2">
								<label for="counterValue" class="form-label"><?=  $langs->trans('ImmoCompteurStatement');?></label>
								<input type="number" class="form-control" id="counterValue" name="counterValue">
							</div>
							<div class="row pt-2">
								<div class="col text-md-end">
									<button type="button" class="btn btn-primary validCounter" id="validCounter" data-bs-dismiss="modal">
									<?= $langs->trans('Valid'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Error Modal -->
			<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModal"
				 aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header bg-danger">
							<h5 class="modal-title"
								id="errorModalLabel"><?= $langs->trans('Error');?></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"
									aria-label="<?= $langs->trans('Close') ?>"></button>
						</div>
						<div class="modal-body bg-warning">
							<h5 id="errorModalText"></h5>
						</div>
					</div>
				</div>
			</div>


			<!-- Loading -->
			<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModal"
				 aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title"
								id="loadingModalLabel"><?= $langs->trans('Loading') ?></h5>
							<div class="spinner-border" role="status">
							  <span class="visually-hidden"><?= $langs->trans('Loading') ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
	</main>
	<?php
}

// End of page
llxFooter();
$db->close();
