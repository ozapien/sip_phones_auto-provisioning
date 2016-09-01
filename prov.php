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

require_once 'config.inc.php';
require_once 'class/user.php';
require_once 'class/phone.php';
require_once 'class/server.php';
require_once 'class/serverParser.php';
require_once 'class/phoneParser.php';
require_once 'class/userParser.php';


$shortopts = "";
$shortopts .= "u:";     // Required: CSV File of Users
$shortopts .= "p:";     // Required: CSV File of Phones
$shortopts .= "gv";     // g => Add Global config file; v => Verbose output


if (!is_array($options = getopt($shortopts))) {
    getHelp();
    exit(1);
}

if (!getServers()) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "No Server list provided into config.inc.php");
    fclose($stderr);
    exit(1);
}


if (!getPhones()) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "No PHONE list provided.\n\n");
    fclose($stderr);
    getHelp();
    exit(1);
}

if (!getUsers()) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "No USER list provided.\n\n");
    fclose($stderr);
    getHelp();
    exit(1);
}



foreach ($users as $user) {
   
}


echo "fin";

function getServers() {
    /*
     * Convert config_servers in config.inc.php to Objects
     */
    global $servers, $config_servers;
    $servers = ServerParser::parseFromCFG($config_servers);
    return !empty($servers);
}

function getPhones() {
    global $phones, $options;
    $phones = PhoneParser::parseFile($options["p"]);
    return !empty($phones);
}

function getUsers() {
    global $users, $phones, $servers, $options;
    $users = UserParser::parseFile($options["u"], $phones, $servers);
    return !empty($users);
}

function getHelp() {
    echo <<<EOF
Create auto-provision files for IP Phone,
by processing a CSV file containing users and phones data.
    
Use prov -u USERS_CSV_FILE -p PHONES_CSV_FILE [OPTIONS]
    
    OPTIONS:
        -g                  Copy global configuration file template of
                            processed phone models.
        -v                  Verbose mode. Provide stats.
    
    USERS_CSV_FILE          Comma separated value file containing following
                            cols:
        mac                 REQUIRED: MAC Address of phone to assign.
        extension           REQUIRED: Unique extension to assing to user.
        name                OPTIONAL: Display name. If DB access is 
                            configurated, name will be set from DB and this
                            column can be ommited.
        user                OPTIONAL: SIP Account. By default, extension it's
                            used as user, this column can be used to set user
                            data.
        password            OPTIONAL: SIP account password. If DB access is 
                            configurated, name will be set from DB and this
                            column can be ommited.
        server_ip           OPTIONAL: Server ip in which the user is assigned
                            In case this column was not supplied, users will be
                            configured in the first configured server into
                            config.inc.php
    
    PHONES_CSV_FILE         Comma separated value file containing following
                            cols:
        mac                 REQUIRED: MAC Address of phone to assign.
        manufacter          REQUIRED: Name of phone manufacter. Can be one of:
                            -Yealink
        model               REUIRED: Model of supported phones. Canbe one of:
                            -Yealink
                                *T23
                                *T46
        dhcp                OPTIONAL: Configure phone network setting by DHCP
                                YES = USE DHCP,
                                NO = USE STATIC ADDRESS
                                If not provided, DHCP it's used, except that
                                network parameters have been provided.
        ip                  REQUIRED IF NO DHCP IT'S USED:
                                IPV4 to configure on WAN port
        cidr                REQUIRED IF NO DHCP IT'S USED:
                                IPV4, netmask bit length (0 to 32).
        gw                  REQUIRED IF NO DHCP IT'S USED:
                                Phone gateway Address.
        vlantag             OPTIONAL: 0 or "" (Empty) = not used,
                                otherwise vlan number to tag in WAN phone port
        ns1                 OPTIONAL: Phone primary DNS.
        ns2                 OPTIONAL: Phone secondary DNS.
    
EOF;
}
