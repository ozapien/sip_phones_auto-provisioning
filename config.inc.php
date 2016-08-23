<?php

/*
 * Copyright (C) 2016 Omar Zapien <omar.zapien at Google mail>
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

/*
 * OUTPUT_DIR define absolut path of dir to write generated provisioning files.
 */

define('__ROOT__', dirname(__FILE__) . "/");



define("OUTPUT_DIR", __ROOT__ . "tftpboot");


/*
 * If you have database structure kind FreePBX, use DB data instead of CSV data
 * to fill user name and secret.
 * Connection parameters must be included in csv file
 */
define("USE_DB_DATA", TRUE);

$config_servers = array(
    "FreePBX1" => array(
        "TYPE"=>    "FreePBX",
        "DB_HOST" => "localhost",
        "DB_NAME" => "asterisl",
        "DB_USER" => "root",
        "DB_PASS" => "",
        "NETWORKS" => array(
            "Management" => array(
                "IP" => "172.18.0.1",
                "CIDR" => 24,
                "GW" => "172.18.0.254",
                "NS1" => "10.10.5.1",
                "NS2" => "10.10.20.1"
            ),
            "VLAN_2501" => array(
                "IP" => "172.18.129.1",
                "CIDR" => 24,
                "GW" => "172.18.129.254",
                "NS1" => "10.10.5.1",
                "NS2" => "10.10.20.1",
                "VLAN" => 2501
            ),
            "VLAN_2502" => array(
                "IP" => "172.18.129.1",
                "CIDR" => 24,
                "GW" => "172.18.129.254",
                "NS1" => "10.10.5.1",
                "NS2" => "10.10.20.1",
                "VLAN" => 2502
            ),
        )
    ),
        /*
         * "FreePBX2" => array(
         * "DB_HOST" => "localhost",
         * "DB_NAME" => "asterisl",
         * "DB_USER" => "root",
         * "DB_PASS" => ""
         * )
         */
);

/*
 * Define default max user per server
 * Exceed the number of sip users will cause a warning message,
 * however provisioning files still will be generated.
 */
define("DEFAULT_MAX_SIP_USERS", 50);
