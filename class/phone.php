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
require_once 'manufacter/model.php';
require_once 'network.php';

class Phone {

    private $MAC;
    private $manufacter;
    private $model;
    private $network;
    private $useDHCP;
    private $users = array();
   

    function __construct($MAC, Model $model, $useDHCP = true, $network = "") {
        $this->setMAC($MAC);
        $this->manufacter = $model->getManufacter();
        $this->model=$model;
        
        $this->useDHCP = $useDHCP;
        if (!$useDHCP) {
            if (gettype($network) === "object" && get_class($network) === "Network") {
                $this->network = $network;
            } else {
                $stderr = fopen('php://stderr', 'w');
                fwrite($stderr, "Bad Network parameter:\n");
                fwrite($stderr, print_r($network));
                fclose($stderr);
                exit(1);
            }
        } else {
            $network = "";
        }
    }

    function getMAC($separator="", $case="lowercase") {
        
        $mac = ($case==="lowercase") ? $this->MAC : strtoupper($this->MAC) ;
        
        if (empty($separator)){
            return $mac;
        }
        $return="";
        for ($i = 0; $i < 10; $i+=2) {
            $return.= substr($mac,$i,2) . $separator;
        }
        $return.= substr($mac,$i,2);
        
        return $return;
    }

    function setMAC($MAC) {
        if (Network::validateMAC($MAC)) {
            $this->MAC = strtolower(str_replace(array(":","-"), "", $MAC));
        } else {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Invalid MAC Address ($MAC)\n");
            fclose($stderr);
            exit(1);
        }
    }

    function getUseDHCP() {
        return $this->useDHCP;
    }

   

    function setUseDHCP($useDHCP) {
        $this->useDHCP = $useDHCP;
    }

    function addUser(User &$user) {
        if (!array_key_exists($user->getExtension(), $this->users)) {
            $user->setRAWPhone($this);
            $this->users[$user->getExtension()] = $user;
        }
    }

    function addNetwork(Network $net) {
        $this->network = $net;
    }

    function getNetwork() {
        return $this->network;
    }

    function getManufacter() {
        return $this->model->getManufacter();
    }

    function getMax_Accounts() {
        return $this->model->getMax_sip_accounts();
    }

    function getModel() {
        return $this->model;
    }

    function getUsers($exten = "") {
        if (empty($exten)) {
            return $this->users;
        } else {
            return (array_key_exists($exten, $this->users)) ? $this->users[$exten] : false;
        }
    }
    
    function getNumUsers(){
        return count($this->users);
    }

}
