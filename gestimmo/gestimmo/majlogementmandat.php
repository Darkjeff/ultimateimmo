<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       dev/Logements/Logement_page.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *					Initialy built by build_class_from_table on 2013-05-13 14:38
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;

// Change this following line to use the correct relative path from htdocs
require '../main.inc.php';
dol_include_once('/gestimmo/class/logement.class.php');
dol_include_once('/gestimmo/lib/gestimmo.lib.php');
dol_include_once('/gestimmo/class/mandat.class.php');


$mandat=new Logement($db);
//$logement= new Mandat($db);

$i=0;
$sql = "SELECT t.rowid , t.ref_interne,t.fk_biens from llx_mandat as t";
$result = $db->query($sql);
if ($result)
{
    $num = $db->num_rows($result);
}
if ($num > 0)
    {
        $var=True;
        while ($i < $num /*&& $i < $conf->liste_limit*/)
        {
            $objp = $db->fetch_object($result);
            print "<td>rowid ".$objp->rowid." --> </td>";
            print "<td>ref interne".$objp->ref_interne."---> </td>";
            print "<td>biens ".$objp->fk_biens."</td>";
            print "<br>";
            $logement= new logement($db);
            $idbiens=$objp->fk_biens;
             print "<td>id biens ".$idbiens."</td><br>";
            $logement->fetch($idbiens);
            print "<td>retour ref_interne :".$logement->ref_interne." ----->  </td><br>";
            print "<td>retour logement :".$logement->id." ----->  </td><br>";
            
            
            $maj=$logement->update_fk_mandat($objp->rowid);
           // $sql2 = 'UPDATE '.MAIN_DB_PREFIX.'llx_logement set fk_mandat = '.$objp->rowid;
           // $sql2.= ' WHERE rowid = '.$objp->fk_biens;
           //$result2 = $db->query($sql2);
           print $maj;
            $i++;
        }
    }


?>