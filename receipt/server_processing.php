<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

 $res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/class/immorenter.class.php");
dol_include_once('/immobilier/class/immoproperty.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$properties = new Immoproperty($db);
$receiptstatic = new Immoreceipt($db);
$thirdparty_static = new Societe($db);

// DB table to use
$table = 'llx_immo_receipt';

// Table's primary key
$primaryKey = 'rowid';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
	array(
		'db'        => 'receipt_id',
		'dt'        => 0,
		'formatter' => function( $d, $row, $db ) {
			$receiptstatic = new Immoreceipt($db);
			$receiptstatic->id = $d;
			$receiptstatic->name = $row['name'];
			return $receiptstatic->getNomUrl(1);
		}
	),
		
	array(
		'db'        => 'renter_id',
		'dt'        => 1,
		'formatter' => function( $d, $row, $db ) {
			$renter = new Renter($db);
			$renter->id = $d;
			$renter->nom = $row['nomlocataire'];
			$renter->prenom = $row['prenomlocataire'];
			return $renter->getNomUrl(1);
		}
	),
	
	array(
		'db'        => 'nomlocal',
		'dt'        => 2,
		'formatter' => function( $d, $row, $db ) {
			$propertystatic=new Immoproperty($db);
			$propertystatic->id = $row['property_id'];
			$propertystatic->name = $row['nomlocal'];
			return $propertystatic->getNomUrl(1);
		}
	),
	
	array( 'db' => 'rent',  'dt' => 3 ),
	
	
	array(
		'db'        => 'echeance',
		'dt'        => 4,
		'formatter' => function( $d, $row, $db ) {
			return dol_print_date($d, 'day');
		}
	),
	
	
	array(
		'db'        => 'amount_total',
		'dt'        => 5,
		'formatter' => function( $d, $row, $db ) {
			return price($d);
		}
	),
	
	
	array(
		'db'        => 'paiepartiel',
		'dt'        => 6,
		'formatter' => function( $d, $row, $db ) {
			return price($d);
		}
	),
	

	array(
		'db'        => 'paye',
		'dt'        => 7,
		'formatter' => function( $d, $row, $db ) {
			$thirdparty_static = new Societe($db);
			$receiptstatic = new Immoreceipt($db);
			return $receiptstatic->LibStatut($d, 5);
			//return $receiptstatic->getNomUrl(1);
		}
	),
	
	array(
		'db'        => 'nomlocataire',
		'dt'        => 8,
		'formatter' => function( $d, $row, $db ) {
			$thirdparty_static = new Societe($db);
			$thirdparty_static->id=$row['soc_id'];
			$thirdparty_static->name= $row['owner_name'];
			return $thirdparty_static->getNomUrl(1);
			//return $receiptstatic->getNomUrl(1);
		}
	),
	
	array(
		'db'        => 'receipt_id',
		'dt'        => 9,
		'formatter' => function( $d, $row, $db ) {
			
			$act = '<a href="card.php?action=edit&id='. $d .'">' . img_edit() . '</a><a class="delete" href="card.php?action=confirm_delete&confirm=yes&id=' . $d . '">' . img_delete() . '</a>';
			return $act;
		}
	),
	
	
);

// SQL server connection information
$sql_details = array(
	'user' => $dolibarr_main_db_user,
	'pass' => $dolibarr_main_db_pass,
	'db'   => $dolibarr_main_db_name,
	'host' => $dolibarr_main_db_host
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

 
	$sql = 'SELECT SQL_CALC_FOUND_ROWS';
	$sql .= ' t.rowid as receipt_id,';
	
	$sql .= " t.fk_contract,";
	$sql .= " t.fk_property,";
	$sql .= " t.name as name,";
	$sql .= " t.fk_renter,";
	$sql .= " t.amount_total as amount_total,";
	$sql .= " t.rent as rent,";
	$sql .= " t.balance,";
	$sql .= " t.paiepartiel as paiepartiel,";
	$sql .= " t.charges,";
	$sql .= " t.vat,";
	$sql .= " t.echeance as echeance,";
	$sql .= " t.commentaire,";
	$sql .= " t.statut as receipt_statut,";
	$sql .= " t.date_rent,";
	$sql .= " t.date_start,";
	$sql .= " t.date_end,";
	$sql .= " t.fk_owner,";
	$sql .= " t.paye as paye,";
	$sql .= " lc.rowid as renter_id,";
	$sql .= " lc.nom as nomlocataire,";
	$sql .= " lc.prenom as prenomlocataire,";
	$sql .= " ll.name as nomlocal,";
	$sql .= " ll.rowid as property_id,";
	$sql .= " soc.rowid as soc_id,";
	$sql .= " soc.nom as owner_name";

	
	$sql .= ' FROM llx_immo_receipt as t';
	$sql .= ' INNER JOIN llx_immo_renter as lc ON t.fk_renter = lc.rowid';
	$sql .= ' INNER JOIN llx_immo_property as ll ON t.fk_property = ll.rowid';
	$sql .= ' LEFT JOIN llx_societe as soc ON soc.rowid = t.fk_owner';
	
 
require( '../class/ssp.class.php' );
$rep = SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $db, $sql );
//$arr = array("test1", "test2");
//var_dump($rep);
echo json_encode($rep);
?>
