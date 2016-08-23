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

    function __construct($name, $ip, $cidr, $gateway, $vlantag=0, $ns1 = "8.8.8.8", $ns2 = "4.2.2.2") {
        if (
                !filter_var($ip, FILTER_VALIDATE_IP) ||
                ($cidr < 0 || $cidr > 32) ||
                !filter_var($gateway, FILTER_VALIDATE_IP) ||
                !filter_var($ns1, FILTER_VALIDATE_IP) ||
                !filter_var($ns2, FILTER_VALIDATE_IP) || 
                ($vlantag<0 || $vlantag > 4096)
        ) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Parámetros de red incorrectos:\n");
            fwrite($stderr, "IP: $ip\nCIDR: $cidr\nGATEWAY: $ip\nNS1: $ns1\nNS2: $ns2");
            fclose($stderr);
            exit(1);
        }
        $this->name=$name;
        $this->ip = $ip;
        $this->cidr = $cidr;
        $this->gateway = $gateway;
        $this->ns1 = $ns1;
        $this->ns2 = $ns2;
        $this->vlantag=$vlantag;
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
    
    static function  validateMAC($mac){
        if (defined(FILTER_VALIDATE_MAC)){
            return filter_var($mac, FILTER_VALIDATE_MAC);
        } else{
            $d = '[0-9a-f]';
            $s = '[:|-]';
            $mac_pattern="/(^$d{12}\$)|(^$d{2}$s$d{2}$s$d{2}$s$d{2}$s$d{2}$s$d{2}\$)/i";
            return preg_match($mac_pattern, $mac) ? $mac : false;
        }
    }

}
