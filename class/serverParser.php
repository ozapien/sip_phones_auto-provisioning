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

require_once 'class/dbhelpers.php';
require_once 'server.php';
require_once 'network.php';

abstract class ServerParser {

    protected static $servers = [];

    static function parseFromCFG($config_servers) {
        foreach ($config_servers as $name => $server) {
            if (array_key_exists($name, ServerParser::$servers)) {
                $stderr = fopen('php://stderr', 'w');
                fwrite($stderr, "Duplicated server ($name) in config.inc.php");
                fclose($stderr);
                exit(1);
            }
            $max_users=array_key_exists("MAX_SIP_USERS", $server) ? $server["MAX_SIP_USERS"] : DEFAULT_MAX_SIP_USERS;
            ServerParser::$servers[$name] = new Server($name, $server["TYPE"], $max_users);
            foreach ($server["NETWORKS"] as $netname => $netparameters) {
                ServerParser::$servers[$name]->addNetwork(
                        new Network(
                        $netname, $netparameters["IP"], $netparameters["CIDR"], $netparameters["GW"], ((array_key_exists("VLAN", $netparameters)) ? $netparameters["VLAN"] : 0), !empty($netparameters["NS1"]) ? $netparameters["NS1"] : "", !empty($netparameters["NS2"]) ? $netparameters["NS2"] : ""
                        )
                );
                ServerParser::$servers[$name]->setUse_db_info($server["USE_DB_INFO"]);
                if ($server["USE_DB_INFO"]) {
                    ServerParser::$servers[$name]->setDBData($server["DB_HOST"], $server["DB_NAME"], $server["DB_USER"], $server["DB_PASS"]);
                }
            }
        }
        new DBHelpers(ServerParser::$servers);
        return (ServerParser::$servers);
    }

    static function getServers() {
        return $this->servers;
    }

}
