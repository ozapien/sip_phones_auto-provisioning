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

require_once 'user.php';
require_once 'phone.php';
require_once 'network.php';

class Server {

    private $name;
    private $networks = array();
    private $max_users;
    private $users = array();
    private $overloaded;
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_pass;

    function __construct($name, $max_users = DEFAULT_MAX_SIP_USERS) {
        $this->name = $name;
        $this->max_users = $max_users;
        $this->overloaded = false;
    }

    function addNetwork(Network &$net) {
        $this->networks[] = $net;
    }

    function getNetworks($ip = "", $cidr = 24) {
        if (empty($ip)) {
            return $this->networks;
        }

        foreach ($this->networks as $net) {
            if (Network::cidr_match($ip, $net->getNet_CIDR())) {
                return $net;
            }
        }
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, "There's not available network in server $this->name for IP: $ip");
        fclose($stderr);
        exit(1);
    }

    function setDB($db_host, $db_name, $db_user, $db_pass) {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
    }

    function getName() {
        return $this->name;
    }

    function getMax_users() {
        return $this->max_users;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setMax_users($max_users) {
        $this->max_users = $max_users;
    }

    function getUsers($exten = "") {
        if (empty($exten)) {
            return $this->usuarios;
        } else {
            return (array_key_exists($exten, $this->usuarios)) ? $this->usuarios[$exten] : false;
        }
    }

    function addUser(User $user) {
        $this->users[] = $user;
        $this->overloaded = ($this->getNumUsers() > $this->max_users);
    }

    function getStatus() {
        // Verify if server can handle current number of users
        $status = array();
        $status["overload"] = ($this->overloaded);
        $status["load"] = round($this->UserCount() / $this->max_users * 100);

        return $status;
    }

    function getNumUsers() {
        return count($this->users);
    }

}
