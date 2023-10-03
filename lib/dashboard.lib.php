<?php
/*
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
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

/**
 * Get the number of devices
 *
 * @return int
 */
 // get number property
function getPropertiesNumber($status)
{
    global $db;

    $res = 0;
    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'ultimateimmo_immoproperty WHERE status = '.$status;
    $resql = $db->query($sql);
    if ($resql) {
        $res = ($db->fetch_object($resql))->total;
    }

    return $res;
}
/**
 * Get the number of devices
 *
 * @return int
 */
 // get number property
function getOwnerNumber($status)
{
    global $db;

    $res = 0;
    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'ultimateimmo_immoowner WHERE status = '.$status;
    $resql = $db->query($sql);
    if ($resql) {
        $res = ($db->fetch_object($resql))->total;
    }

    return $res;
}

function getRentNumber($status)
{
    global $db;

    $res = 0;
    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'ultimateimmo_immorent WHERE preavis = '.$status;
    $resql = $db->query($sql);
    if ($resql) {
        $res = ($db->fetch_object($resql))->total;
    }

    return $res;
}

function getRenterNumber($status)
{
    global $db;

    $res = 0;
    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'ultimateimmo_immorenter WHERE status = '.$status;
    $resql = $db->query($sql);
    if ($resql) {
        $res = ($db->fetch_object($resql))->total;
    }

    return $res;
}

///**
// * Get the number of devices under contract
// *
// * @return int
// */
//function getDevicesNumberUnderContract()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'c_gestionparc_device WHERE under_management = 1 AND entity IN('.getEntity('c_gestionparc_device').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of devices without contracts
// *
// * @return int
// */
//function getDevicesNumberWithoutContract()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM ' . MAIN_DB_PREFIX . 'c_gestionparc_device WHERE under_management IS NULL AND entity IN('.getEntity('c_gestionparc_device').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of users associated to gestion de parc
// *
// * @return int
// */
//function getUsersNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(se.rowid) AS total FROM '.MAIN_DB_PREFIX.'socpeople_extrafields AS se';
//    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'socpeople AS s ON s.rowid = se.fk_object';
//    $sql.= ' WHERE se.c42contact_infoextranet = 1';
//    $sql.= ' AND s.entity IN('.getEntity('socpeople').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of applications
// *
// * @return int
// */
//function getApplicationsNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'c_gestionparc_application WHERE entity IN('.getEntity('c_gestionparc_application').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of addresses
// *
// * @return int
// */
//function getAddressesNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'c_gestionparc_ip WHERE entity IN('.getEntity('c_gestionparc_ip').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of authentications
// *
// * @return int
// */
//function getAuthenticationsNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'c_gestionparc_auth WHERE entity IN('.getEntity('c_gestionparc_auth').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of contracts
// *
// * @return int
// */
//function getContractsNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(rowid) AS total FROM '.MAIN_DB_PREFIX.'contrat WHERE entity IN('.getEntity('contrat').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of interventions
// *
// * @return int
// */
//function getInterventionsNumber()
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(f.rowid) AS total FROM '.MAIN_DB_PREFIX.'fichinter AS f LEFT JOIN '.MAIN_DB_PREFIX.'fichinter_extrafields AS fe
//    ON f.rowid = fe.fk_object WHERE fe.c42i_device_id_under_contract <> 0 AND f.date_valid IS NOT NULL AND f.entity IN('.getEntity('fichinter').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
//
///**
// * Get the number of interventions
// *
// * @param   int     $status     Status of ticket to search
// * @return  int
// */
//function getTicketsNumber($status)
//{
//    global $db;
//
//    $res = 0;
//    $sql = 'SELECT COUNT(f.rowid) AS total FROM '.MAIN_DB_PREFIX.'ticket AS f LEFT JOIN '.MAIN_DB_PREFIX.'ticket_extrafields AS fe
//    ON f.rowid = fe.fk_object WHERE fk_statut = '.$status.' AND f.entity IN('.getEntity('ticket').')';
//    $resql = $db->query($sql);
//    if ($resql) {
//        $res = ($db->fetch_object($resql))->total;
//    }
//
//    return $res;
//}
