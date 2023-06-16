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
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/security.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/ultimateimmo/lib/ultimateimmo.lib.php');

// Load translation files required by the page
$langs->loadLangs(array('companies', 'other', 'ultimateimmo@ultimateimmo', 'errors', "contracts", "bills", "compta"));

// Get parameters

$action = GETPOST('action', 'alpha');

/*
 * Action
 */
if ($action=='logout') {
	$_SESSION["urlfrom"] = dol_buildpath('/ultimateimmo/public/index.php', 1);
	header("Location: ".dol_buildpath('/user/logout.php',2).'?token='.newToken()); // Default behaviour is redirect to index.php page
	exit;
}


/*
 * View
 */

if (empty($conf->global->ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE))
{
	print $langs->trans('UltimateimmoPublicInterfaceForbidden');
	exit;
}


$form = new Form($db);

llxHeaderUltimateImmoPublic();



if (empty($user->id)) {
	dol_include_once('/core/tpl/login.tpl.php');
} else {

	dol_include_once('/ultimateimmo/class/immorenter.class.php');
	//var_dump($user);
	$renter = new ImmoRenter($db);

	$userLinkid=0;

	$sql = 'SELECT renter.rowid as renterId FROM ' . MAIN_DB_PREFIX . 'socpeople as sp ';
	$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'user as u ON u.fk_socpeople=sp.rowid';
	$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . $renter->table_element.' as renter ON renter.fk_soc=sp.fk_soc';
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

?>
<main class="container-fluid">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">
					<?= $user->getFullName($langs);?>
				</div>
				<div class="col-md-6 text-md-end">
					<a href="<?= $_SERVER['PHP_SELF'].'?action=logout&token='.newToken()?>">
						<i class="fa fa-2x fa-external-link-square" aria-hidden="true"></i>
					</a>
				</div>
			</div>
		</div>
		<div class="container-fluid">
				<div class="table-responsive-md">
					<table class="table table-striped table-bordered">
						<thead>
						<tr>
							<th scope="col"><?= $langs->trans('NomLoyer') ?></th>
							<th scope="col"><?= $langs->trans('Nomlocal') ?></th>
							<th scope="col"><?= $langs->trans('TotalAmount') ?></th>
							<th scope="col"><?= $langs->trans('PartialPayment') ?></th>
							<th scope="col"><?= $langs->trans('Balance') ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
						   $sql = "SELECT rec.rowid as reference, rec.label as receiptname,rec.ref as receiptref, loc.lastname as nom, ";
							$sql .= " prop.address, prop.label as local, loc.status as status, rec.total_amount as total, rec.partial_payment, ";
							$sql .= " rec.balance, rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_owner,";
							$sql .= " rec.fk_rent as refcontract, rent.preavis";
							$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
							//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p ON rec.rowid = p.fk_receipt";
							$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter AND loc.rowid=".(int)$renterId;
							$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property";
							$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent";
							$sql .= " WHERE rec.paye <> 1 AND rent.preavis = 1 ";
							$resql = $db->query($sql);
							if ($resql < 0) {
								setEventMessages($db->lasterror, null, 'errors');
							} else {
								$num = $db->num_rows($resql);
								if ($num > 0) {
									while ($objp = $db->fetch_object($resql)) {

										$objref = dol_sanitizeFileName($objp->receiptref);
										$relativepath = $objref . '/' . $objref . '.pdf';
										$filedir = $conf->ultimateimmo->dir_output . '/receipt' . '/' . $objref;
										if (file_exists($filedir.'/'.$objref . '.pdf')) {
											$urldlfile=dol_buildpath('/document.php', 2).'?modulepart=ultimateimmo&file=receipt/'.$relativepath;
										}

										?>
							<tr>
								<th scope="row"><a href="<?= $urldlfile ?>" target="_blank"><?=  $objp->receiptref ?></a></th>
								<td><?=  $objp->local ?></td>
								<td><?=  price($objp->total) ?></td>
								<td><?=  price($objp->partial_payment) ?></td>
								<td><?=  price($objp->balance) ?></td>
							</tr>
										<?php
									}
								}
							}
						?>

						</tbody>
					</table>
				</div>
		</div>
</main>
<?php
}

// End of page
llxFooter();
$db->close();
