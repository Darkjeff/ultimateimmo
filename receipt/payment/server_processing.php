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

// Dolibarr environment
$res = @include ("../../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once("/immobilier/class/immopayment.class.php");
dol_include_once("/immobilier/class/immorenter.class.php");
dol_include_once('/immobilier/class/immoproperty.class.php');


$paymentstatic = new Immopayment($db);

// DB table to use
$table = 'llx_immo_payment';

// Table's primary key
$primaryKey = 'rowid';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

			
$columns = array(
	array(
		'db'        => 'reference',
		'dt'        => 0,
		'formatter' => function( $d, $row, $db ) {
			$pay_static = new Immopayment($db);
			$pay_static->id = $d;
			$pay_static->ref = $d;
			return $pay_static->getNomUrl(1, '20');
		}
	),
	
	array(
		'db'        => 'date_payment',
		'dt'        => 1,
		'formatter' => function( $d, $row, $db ) {
			return dol_print_date($d, 'day');
		}
	),
	
	array(
		'db'        => 'renter_id',
		'dt'        => 2,
		'formatter' => function( $d, $row, $db ) {
			$renter = new Renter($db);
			$renter->id = $d;
			$renter->nom = $row['nomlocataire'];
			$renter->prenom = $row['prenomlocataire'];
			return $renter->getNomUrl(1);
		}
	),
	
	array(
		'db'        => 'property_id',
		'dt'        => 3,
		'formatter' => function( $d, $row, $db ) {
			$propertystatic=new Immoproperty($db);
			$propertystatic->id = $row['property_id'];
			$propertystatic->name = $row['nomlocal'];
			return $propertystatic->getNomUrl(1);
		}
	),
	
	array( 'db' => 'nomloyer',  'dt' => 4 ),	
	
	//array( 'db' => 'fk_soc',  'dt' => 3 ),
	
	array(
		'db'        => 'amount',
		'dt'        => 5,
		'formatter' => function( $d, $row, $db ) {
			return price($d);
		}
	),
	
	array( 'db' => 'comment',  'dt' => 6 ),
	
	array(
		'db'        => 'reference',
		'dt'        => 7,
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
	$sql .= ' t.rowid as reference,';
	
	$sql .= " t.fk_contract,";
	$sql .= " t.fk_property,";
	$sql .= " t.fk_renter,";
	$sql .= " t.amount,";
	$sql .= " t.comment,";
	$sql .= " t.date_payment as date_payment,";
	$sql .= " t.fk_owner,";
	$sql .= " t.fk_receipt";
	$sql .= " , lc.rowid as renter_id, lc.nom as nomlocataire, lc.prenom as prenomlocataire , ll.rowid as property_id, ll.name as nomlocal , lo.name as nomloyer ";

	
	$sql .= ' FROM ' . $table. ' as t';
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_renter as lc ON t.fk_renter = lc.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON t.fk_property = ll.rowid ";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_receipt as lo ON t.fk_receipt = lo.rowid";


 
require( '../../class/ssp.class.php' );
$rep = SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $db, $sql );
//$arr = array("test1", "test2");
//var_dump($rep);
echo json_encode($rep);
?>
