  <?php
/* Copyright (C) 2013 Thierry.lecerf T3S
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *      \file       gestimmo/Mandats/liste.php
 *      \ingroup    mymodule othermodule1 othermodule2
 *      \brief      This file is an example of a php page
 *                  Initialy built by build_class_from_table on 2013-05-12 23:01
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');           // Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');          // Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');        // Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');         // If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');         // If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');               // If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
/*
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");

 */
 require ("../../main.inc.php");
 require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
 require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
// Change this following line to use the correct relative path from htdocs
dol_include_once('/immobilier/class/mandat.class.php');
require_once DOL_DOCUMENT_ROOT.'/immobilier/core/lib/immobilier.lib.php';
dol_include_once('immobilier.gestimmo/gestimmo/class/logement.class.php');
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("gestimmo");
// Langs
$langs->load ( "immobilier@immobilier" );
$sortfield=GETPOST('sortfield','alpha');
$sortorder=GETPOST('sortorder','alpha');
$page=GETPOST('page','int');
if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;

$search_nom=GETPOST('search_nom');
$search_contract=GETPOST('search_contract');
$sall=GETPOST('sall');
$statut=GETPOST('statut')?GETPOST('statut'):1;
$socid=GETPOST('socid');

if (! $sortfield) $sortfield="t.rowid";
if (! $sortorder) $sortorder="DESC";

// Get parameters
/*
$id         = GETPOST('id','int');
$action     = GETPOST('action','alpha');
$myparam    = GETPOST('myparam','alpha');
*/
$action ="list";

// Protection if external user
if ($user->societe_id > 0)
{
    //accessforbidden();
}



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($action == 'add')
{
    $object=new Mandat($db);
    $object->prop1=$_POST["field1"];
    $object->prop2=$_POST["field2"];
    $result=$object->create($user);
    if ($result > 0)
    {
        // Creation OK
    }
    {
        // Creation KO
        $mesg=$object->error;
    }
}





/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$texte="Listes des mandats";
//llxHeader('','Liste mandats','');
llxHeader("","",$texte);
$form=new Form($db);


$text="Gestion des mandats ";
//print_barre_liste($langs->trans("$text"), $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, "", $num);
 print '<div class="tabBar">';
print '<table width="auto">';
$head=mandat_prepare_head($user);
$titre=$langs->trans("mandats");
$picto='biens';
//dol_fiche_head($head, 'Mandat', $titre, 0, $picto);
// Put here content of your page
// pour test liste
//$action='list';
// Put here content of your page

/* Example 1 : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
    function init_myfunc()
    {
        jQuery("#myid").removeAttr(\'disabled\');
        jQuery("#myid").attr(\'disabled\',\'disabled\');
    }
    init_myfunc();
    jQuery("#mybutton").click(function() {
        init_needroot();
    });
});
</script>';
*/

// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();


// Example 3 : List of data
if ($action == 'list')
{
    $sortfield="t.ref_interne";
    $sortorder="ASC";
        
    $sql = "SELECT";
    $sql.= " t.rowid,";
    
        $sql.= " t.ref_interne,";
        $sql.= " t.fk_soc,";
        $sql.= " t.fk_biens,";
        $sql.= " t.date_cloture,";
        $sql.= " t.notes_public,";
        $sql.= " t.fk_user_author,";
        $sql.= " t.datec,";
        $sql.= " t.fk_user_mod,";
        $sql.= " t.tms,";
        $sql.= " t.entity,";
        $sql.= " t.date_contrat,";
        $sql.= "t.fin_validite";
    
    $sql.= " FROM ".MAIN_DB_PREFIX."immo_mandat as t";
   // $sql.= " WHERE field3 = 'xxx'";
   $sql.= " ORDER BY $sortfield $sortorder ";

    
// pour la liste
$text="Liste des Mandats ";
//print_barre_liste($langs->trans("$text"), $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, "", $num);
print_barre_liste($langs->trans("$text"), $page, $_SERVER["PHP_SELF"], '&search_contract='.$search_contract.'&search_nom='.$search_nom, $sortfield, $sortorder,'',$num);


print '<div class="tabBar">';
print '<table width="auto">';

//colonne gauche
print '<tr><td width=auto>';
print '<table class="noborder" width="400px">';
print '<tr class="liste_titre"><td colspan=4>'.$langs->trans("Liste des Mandats").'</td></tr>';

//print_fiche_titre($text);

$i = 0;
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans("Id"),$_SERVER['PHP_SELF'],"t.rowid","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("N°mandat"),$_SERVER['PHP_SELF'],"t.ref_interne","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Biens"),$_SERVER['PHP_SELF'],"t.fk_biens","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("type de bien"),$_SERVER['PHP_SELF'],"c.typebien","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Date mandat"),$_SERVER['PHP_SELF'],"t.date_contrat","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Date fin"),$_SERVER['PHP_SELF'],"t.fin_validite","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Propio"),$_SERVER['PHP_SELF'],"t.fk_societe","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("ref locataire"),$_SERVER['PHP_SELF'],"c.loc","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("date entre"),$_SERVER['PHP_SELF'],'' ,'&arch='.$arch,'',$sortfield,$sortorder);
print "</tr>\n";

// fin
    dol_syslog($script_file." sql=".$sql, LOG_DEBUG);
    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                { $titre="";
                    // You can use here results
                    print '<tr><td>';
                    print "<a href=\"fiche.php?id=$obj->rowid\">$obj->rowid</a>";
                   // print $obj->rowid;
                    
                    //print $product_static->getNomUrl(1,'',24);
                    print '<td>'.$obj->ref_interne.'</td>';
                   // print '<td>'.$obj->fk_biens.'</td>';
                    //print '<td>'.$obj->getNomUrl(1,'fk_societe').'</td>';
               // ref du biens
                  
     
              //    $biens=new Immeuble($db);
              //    $biens->fetch($obj->fk_biens);
                 // $titre=$biens->ref;
              // $titre=$biens->getbiensUrl(1).$titre;
           //    print'<td>'.$titre.'</td>';
           //    print '<td> type: '.$biens->nb_piece.'</td>';
               
               print '<td>'.dol_print_date($obj->date_contrat).'</td>';
               print '<td>'.dol_print_date($obj->fin_validite).'</td>';
                   // print '<td>'.$obj->fk_societe.'</td>';
                    //print '<td><a href="../comm/fiche.php?socid='.$obj->fk_soc.'">'.img_object($langs->trans("ShowCompany"),"company").'prop '.$obj->nom.'</a></td>';
                    //print '<td>'.$obj->fk_user_mod.'</td>';
                    //print '<td><a href="../comm/fiche.php?socid='.$obj->fk_user_mod.'">'.img_object($langs->trans("ShowCompany"),"company").' loc'.$obj->nom.'</a></td>';
                    //print '<td>'.$obj->fk_societe.'</td>';
                    //print '</td></tr>';
                }
                $i++;
            }
        }
    }
    else
    {
        $error++;
        dol_print_error($db);
    }

    print '</table>'."\n";
    
}



// End of page
llxFooter();
$db->close();
?>
