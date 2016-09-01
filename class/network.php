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

class Network {

    private $name;
    private $ip;
    private $cidr;
    private $gateway;
    private $ns1;
    private $ns2;
    private $vlantag;

    function __construct($name, $ip, $cidr, $gateway, $vlantag = 0, $ns1 = "", $ns2 = "") {
        Network::validateNetwork($ip, $cidr, $gateway, $vlantag, $ns1, $ns2, "Invalid network parameters.\n");
        $this->name = $name;
        $this->ip = $ip;
        $this->cidr = intval($cidr);
        $this->gateway = $gateway;
        $this->ns1 = $ns1;
        $this->ns2 = $ns2;
        $this->vlantag = intval($vlantag);
    }

    function getIp() {
        return $this->ip;
    }

    function getCidr() {
        return $this->cidr;
    }

    function getNetmask() {
        return Network::CIDRtoMask($this->cidr);
    }

    function getNet_CIDR() {
        $host = long2ip(ip2long($this->ip) & (int) 0);
        return $host . "/" . $this->cidr;
    }

    function getNet_NETMASK() {
        $host = long2ip(ip2long($this->ip) & (int) 0);
        return $host . "/" . Network::CIDRtoMask($this->cidr);
    }

    function getGateway() {
        return $this->gateway;
    }

    function getNs1() {
        return $this->ns1;
    }

    function getNs2() {
        return $this->ns2;
    }

    function getName() {
        return $this->name;
    }

    function getVlantag() {
        return $this->vlantag;
    }

    function setIp($ip) {
        $this->ip = $ip;
    }

    function setCidr($cidr) {
        $this->cidr = $cidr;
    }

    function setGateway($gateway) {
        $this->gateway = $gateway;
    }

    function setNs1($ns1) {
        $this->ns1 = $ns1;
    }

    function setNs2($ns2) {
        $this->ns2 = $ns2;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setVlantag($vlantag) {
        $this->vlantag = $vlantag;
    }

    static function cidr_match($ip, $range) {
        list ($networkAddr, $bits) = explode('/', $range);
        $significativeIP = substr(Network::IP2bin($ip), 0, $bits);
        $significativeNetwork = substr(Network::IP2Bin($networkAddr), 0, $bits);
        return ($significativeIP === $significativeNetwork);
    }

    static function IP2Bin($ip) {
        $octetos = explode(".", $ip);
        $ipbin = "";
        foreach ($octetos as $octeto) {
            $ipbin .= sprintf("%08b", $octeto);
        }
        return $ipbin;
    }

    static function CIDRtoMask($cidr) {
        return long2ip(-1 << (32 - (int) $cidr));
    }

    static function validateMAC($mac) {
        if (defined(FILTER_VALIDATE_MAC)) {
            return filter_var($mac, FILTER_VALIDATE_MAC);
        } else {
            $d = '[0-9a-f]';
            $s = '[:|-]';
            $mac_pattern = "/(^$d{12}\$)|(^$d{2}$s$d{2}$s$d{2}$s$d{2}$s$d{2}$s$d{2}\$)/i";
            return preg_match($mac_pattern, $mac) ? $mac : false;
        }
    }

    static function validateNetwork($ip, $cidr, $gateway, $vlantag, $ns1, $ns2, $errormsg) {
        if (!Network::validateIP($ip)) {
            Network::exitWithError($errormsg . "Invalid ip address ($ip)");
        }
        if (!Network::validateCIDR($cidr)) {
            Network::exitWithError($errormsg . "Invalid CIDR ($cidr)");
        }
        if (!Network::validateGateway($ip, $cidr, $gateway)) {
            Network::exitWithError($errormsg . "Gateway $gateway is not part of $ip/$cidr");
        }

        if (!empty($vlantag)) {
            if (!Network::validateVLANTag($vlantag)) {
                Network::exitWithError($errormsg . "invalid VLAN ($vlantag)");
            }
        }
        if (!empty($ns1)) {
            if (!Network::validateIP($ns1)) {
                Network::exitWithError($errormsg . "Invalid Primary name server ($ns1)");
            }
        }
        if (!empty($ns2)) {
            if (!empty($ns2) && !Network::validateIP($ns2)) {
                Network::exitWithError($errormsg . "Invalid secondary name server ($ns1)");
            }
        }
    }

    private static function exitWithError($errormsg) {
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $errormsg);
        fclose($stderr);
        exit(1);
    }

    static function validateIP($ip) {
        if (defined("FILTER_VALIDATE_IP")) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        } else {
            $octets = explode('.', $ip);
            if (count($octets) != 4) {
                return false;
            }
            foreach ($octets as $octect) {
                $octect = intval($octect);
                if ($octect < 0 || $octect > 255) {
                    return false;
                }
            }
        }
        return true;
    }

    static function validateCIDR($cidr) {
        if (defined("FILTER_VALIDATE_INT")) {
            return !(filter_var($cidr, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0, "max_range" => 32))) === false);
        }
        Network::exitWithError("FILTER_VALIDATE_INT not suported. verify PHP version");

        // TODO: implement method if filter_var it's not supported
    }

    static function validateVLANTag($vlan) {
        if (defined("FILTER_VALIDATE_INT")) {
            return !(filter_var($vlan, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0, "max_range" => 4096))) === false);
        }
        Network::exitWithError("FILTER_VALIDATE_INT not suported. verify PHP version");
    }

    static function validateGateway($ip, $cidr, $gateway) {
        if (!Network::validateIP($gateway)) {
            Network::exitWithError($errormsg . "Invalid gateway address ($gateway)");
        }
        return Network::cidr_match($gateway, "$ip/$cidr");
    }

}
