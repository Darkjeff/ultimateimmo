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

dol_include_once("/immobilier/class/immocost.class.php");
dol_include_once("/immobilier/class/immorenter.class.php");
dol_include_once('/immobilier/class/immoproperty.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';


$receiptstatic = new ImmoCost($db);

// DB table to use
$table = 'llx_immo_cost';

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
			$charge_static = new ImmoCost($db);
			$charge_static->id = $d;
			$charge_static->nom = $d;
			$charge_static->ref = $d;
			return $charge_static->getNomUrl(1, '20');
		}
	),
	
	
	array( 'db' => 'libelle',  'dt' => 1 ),	
	
	array(
		'db'        => 'nomlocal',
		'dt'        => 2,
		'formatter' => function( $d, $row, $db ) {
			$propertystatic=new ImmoProperty($db);
			$propertystatic->id = $row['property_id'];
			$propertystatic->name = $row['nomlocal'];
			return $propertystatic->getNomUrl(1);
		}
	),
	
	//array( 'db' => 'fk_soc',  'dt' => 3 ),
	
	array( 'db' => 'amount',  'dt' => 3 ),
	
	array(
		'db'        => 'datec',
		'dt'        => 4,
		'formatter' => function( $d, $row, $db ) {
			return dol_print_date($d, 'day');
		}
	),
	
	array(
		'db'        => 'dispatch',
		'dt'        => 5,
		'formatter' => function( $d, $row, $db ) {
			$charge_static = new ImmoCost($db);
			return $charge_static->LibStatut ( $d, 5 );
		}
	),
	
		
	array(
		'db'        => 'soc_id',
		'dt'        => 6,
		'formatter' => function( $d, $row, $db ) {
			$thirdparty_static = new Societe($db);
			$thirdparty_static->id=$row['soc_id'];
			$thirdparty_static->name= $row['company'];
			return $thirdparty_static->getNomUrl(1);
			//return $receiptstatic->getNomUrl(1);
		}
	),
	
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

$sql = "SELECT SQL_CALC_FOUND_ROWS ch.rowid as reference, ch.fk_property as idlocal , ch.cost_type as cost_type, ch.label as libelle, ch.amount_ht , ch.amount_vat , ch.amount , ch.datec , ch.fk_soc, ch.dispatch";
$sql .= ", ll.rowid as property_id, ll.name as nomlocal,";
$sql .= " soc.rowid as soc_id,";
$sql .= " soc.nom as company";
$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immocost as ch";
$sql .= " LEFT JOIN llx_immo_property as ll ON ch.fk_property = ll.rowid";
$sql .= " LEFT JOIN llx_societe as soc ON soc.rowid = ch.fk_soc";


 
require( '../class/ssp.class.php' );
$rep = SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $db, $sql );
//$arr = array("test1", "test2");
//var_dump($rep);
echo json_encode($rep);
?>
