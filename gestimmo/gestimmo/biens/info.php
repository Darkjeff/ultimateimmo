<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 *  \file       	/agefodd/site/info.php
 *  \brief      	Page fiche d'info sur site de formation
 *  \version		$Id$
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");


dol_include_once('/core/lib/functions2.lib.php');
dol_include_once('/gestimmo/class/logement.class.php');
dol_include_once('/gestimmo/lib/gestimmo.lib.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$mesg = '';

$id=GETPOST('id','int');

$db->begin();

/*
 * View
*/

llxHeader('',$langs->trans("AgfTeacherSite"));

$agf = new Logement($db);
$agf->info($id);

$head = biens_prepare_head($agf);

dol_fiche_head($head, 'info', $langs->trans("AgfTeacherSite"), 0, 'address');

print '<table width="100%"><tr><td>';
dol_print_object_info($agf);
print '</td></tr></table>';
print '</div>';

$db->close();

llxFooter('$Date: 2010-03-28 19:06:42 +0200 (dim. 28 mars 2010) $ - $Revision: 51 $');
?>
