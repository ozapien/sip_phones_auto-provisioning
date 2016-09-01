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
require_once 'dbhelpers.php';

class Server {

    private $name;
    private $networks = array();
    private $max_users;
    private $users = array();
    private $overloaded;
    private $use_db_info;
    private $type;
    private $db_helper;
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_pass;

    function __construct($name, $type, $max_users) {
        $this->name = $name;
        $this->type = $type;
        $this->max_users = $max_users;
        $this->overloaded = false;
    }

    function addNetwork(Network &$net) {
        $this->networks[$net->getName()] = $net;
    }

    function getNetworks($forIP = "") {
        if (empty($forIP)) {
            return $this->networks;
        }

        foreach ($this->networks as $net) {
            if (Network::cidr_match($forIP, $net->getNet_CIDR())) {
                return $net;
            }
        }
        return false;
    }

    function getType() {
        return $this->type;
    }

    function getDb_helper() {
        return $this->db_helper;
    }

    function setDb_helper(DBHelper $db_helper) {
        $this->db_helper = $db_helper;
    }

    function getUse_db_info() {
        return $this->use_db_info;
    }

    function setUse_db_info($use_db_info) {
        $this->use_db_info = $use_db_info;
    }

    function setDBData($db_host, $db_name, $db_user, $db_pass) {
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
    }

    function getDBData() {
        return array(
            $this->db_host,
            $this->db_name,
            $this->db_user,
            $this->db_pass
        );
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
        if (!array_key_exists($user->getExtension(), $this->users)) {
            $this->users[$user->getExtension()] = $user;
            $this->overloaded = ($this->getNumUsers() > $this->max_users);
            $user->setRAWServer($this);
        }
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
