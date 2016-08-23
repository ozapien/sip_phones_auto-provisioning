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
require_once 'class/userParser.php';
require_once 'class/phoneParser.php';


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
$DefaultServer = reset(array_keys($servers));


if (!getPhones()) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "No PHONE list provided.\n\n");
    fclose($stderr);
    getHelp();
    exit(1);
}

if (!$users = getUsers()) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "No USER list provided.\n\n");
    fclose($stderr);
    getHelp();
    exit(1);
}



foreach ($users as $user) {
    if (array_key_exists($user["mac"], $phones)) {
        $phone = $phones[$user["mac"]];
        $phone->addUser($user);
    }
}


echo "fin";

function getServers() {
    /*
     * Convert config_servers in config.inc.php to Objects
     */
    global $servers;
    $servers = [];
    global $config_servers;
    foreach ($config_servers as $name => $server) {
        $servers[$name] = new Server($name);
        foreach ($server["NETWORKS"] as $netname => $netparameters) {
            $servers[$name]->addNetwork(
                    new Network(
                    $netname, $netparameters["IP"], $netparameters["CIDR"], $netparameters["GW"], ((array_key_exists("VLAN", $netparameters)) ? $netparameters["VLAN"] : 0), $netparameters["NS1"], $netparameters["NS2"]
                    )
            );
            if (USE_DB_DATA) {
                $servers[$name]->setDB(
                        $server["DB_HOST"], $server["DB_NAME"], $server["DB_USER"], $server["DB_PASS"]
                );
            }
        }
    }
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
        -v                  Verbose mode. Provide stats and validate load
                            of server.
    
    USERS_CSV_FILE          Comma separated value file containing following
                            cols:
        mac                 MAC Address of phone to assign.
        extension           Unique extension to assing to user.
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
                            configured into the first supplied (default) server
    
    PHONES_CSV_FILE         Comma separated value file containing following
                            cols:
        mac                 MAC Address of phone to assign.
        manufacter          Name of phone manufacter. Can be one of:
                            -Yealink
        model               Model of supported phones. Canbe one of:
                            -Yealink
                                *T23
                                *T46
        dhcp                OPTIONAL: 0 = USE STATIC ADDRESS otherwise use DHCP
        ip                  REQUIRED IF NO DHCP IT'S USED:
                                IP to configure on WAN port
        cidr                REQUIRED IF NO DHCP IT'S USED:
                                Netmask bit length.
        gw                  REQUIRED IF NO DHCP IT'S USED:
                                Phone gateway Address.
        vlantag             REQUIRED IF NO DHCP IT'S USED:
                                0 = not used otherwise vlan number to tag
                                into WAN phone port
        ns1                 REQUIRED IF NO DHCP IT'S USED:
                                Phone primary DNS.
        ns2                 OPTIONAL: Phone secondary DNS.
    
EOF;
}
