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
require_once 'phone.php';
require_once 'manufacters.php';
require_once 'network.php';

abstract class PhoneParser {

    static $CSVFile;
    static $csvphones = [];
    static $phones = [];
    private static $requiredCols;
    private static $optionalCols;
    private static $fileHandler;

    static function parseFile($CSVPhoneFile) {
        PhoneParser::Cleanup();
        PhoneParser::SetCols();
        PhoneParser::$CSVFile = $CSVPhoneFile;
        PhoneParser::openCSV();
        PhoneParser::iterateCSV();
        PhoneParser::instantiatePhones();
        return PhoneParser::$phones;
    }

    static function Cleanup() {
        PhoneParser::$CSVFile = null;
        PhoneParser::$csvphones = [];
        PhoneParser::$phones = [];
        PhoneParser::$requiredCols = null;
        PhoneParser::$optionalCols = null;
        PhoneParser::$fileHandler = null;
    }

    private static function SetCols() {
        PhoneParser::$requiredCols = array(
            "mac" => array("Col" => -1),
            "manufacter" => array("Col" => -1),
            "model" => array("Col" => -1)
        );
        PhoneParser::$optionalCols = array(
            "dhcp" => array("Exist" => false, "Col" => -1),
            "ip" => array("Exist" => false, "Col" => -1),
            "cidr" => array("Exist" => false, "Col" => -1),
            "gw" => array("Exist" => false, "Col" => -1),
            "vlantag" => array("Exist" => false, "Col" => -1),
            "ns1" => array("Exist" => false, "Col" => -1),
            "ns2" => array("Exist" => false, "Col" => -1)
        );
    }

    private static function openCSV() {
        if (!file_exists(PhoneParser::$CSVFile)) {
            PhoneParser::exitWithError("Phone CSV file (" . PhoneParser::$CSVFile . "), does not exist.\n\n");
        }
        PhoneParser::$fileHandler = fopen(PhoneParser::$CSVFile, 'r');
        if (empty(PhoneParser::$fileHandler)) {
            PhoneParser::exitWithError("Cannot open " . PhoneParser::$CSVFile . " file.\n\n");
        }
    }

    private static function iterateCSV() {
        // First line
        $headers_arr = fgetcsv(PhoneParser::$fileHandler, 0, ',');
        if (empty($headers_arr)) {
            PhoneParser::exitWithError("Empty phone CSV file (" . PhoneParser::$CSVFile . ").\n\n");
        }
        PhoneParser::mapHeaders($headers_arr);
        while (($line_arr = fgetcsv(PhoneParser::$fileHandler, 0, ',')) !== FALSE) {
            PhoneParser::mapValues($line_arr);
        }
    }

    private static function mapHeaders($csvHeaders) {
        $required = array_keys(PhoneParser::$requiredCols);
        $optional = array_keys(PhoneParser::$optionalCols);
        foreach ($csvHeaders as $key => $value) {
            if (in_array($value, $required)) {
                PhoneParser::$requiredCols[$value]["Col"] = $key;
            } elseif (in_array($value, $optional)) {
                PhoneParser::$optionalCols[$value]["Exist"] = true;
                PhoneParser::$optionalCols[$value]["Col"] = $key;
            }
        }
        if (
                PhoneParser::$requiredCols["mac"]["Col"] < 0 ||
                PhoneParser::$requiredCols["manufacter"]["Col"] < 0 ||
                PhoneParser::$requiredCols["model"]["Col"] < 0
        ) {
            PhoneParser::exitWithError("Phone CSV file (" . PhoneParser::$CSVFile . " does not contain required columns (mac, manufacter, model).\n\n");
        }
        foreach (PhoneParser::$optionalCols as $key => $value) {
            if (!PhoneParser::$optionalCols[$key]["Exist"]) {
                unset(PhoneParser::$optionalCols[$key]);
            }
        }
        if (array_key_exists("ip", PhoneParser::$optionalCols)) {
            $existingoptions = array_keys(PhoneParser::$optionalCols);
            foreach (array("ip", "cidr", "gw") as $option) {
                if (!in_array($option, $existingoptions)) {
                    PhoneParser::exitWithError("Missing column $option in Phone CSV file ");
                }
            }
        }
    }

    private static function mapValues($csvLine) {
        $phone = [];
        $mac = ($csvLine[PhoneParser::$requiredCols["mac"]["Col"]]);
        $phone["manufacter"] = ($csvLine[PhoneParser::$requiredCols["manufacter"]["Col"]]);
        $phone["model"] = ($csvLine[PhoneParser::$requiredCols["model"]["Col"]]);
        $options = array_keys(PhoneParser::$optionalCols);
        foreach ($options as $option) {
            $phone[$option] = ($csvLine[PhoneParser::$optionalCols[$option]["Col"]]);
        }
        PhoneParser::$csvphones[$mac] = $phone;
    }

    private static function instantiatePhones() {
        global $manufacters;
        foreach (PhoneParser::$csvphones as $mac => $arr_values) {
            $manufacter = $manufacters->getSupported($arr_values["manufacter"]);
            $model = $manufacter->getModel($arr_values["model"]);
            if (PhoneParser::verifyDHCPUse($arr_values)) {
                $phone = new Phone($mac, $model);
            } else {
                list($ip, $cidr, $gateway, $vlantag, $ns1, $ns2) = PhoneParser::getNet($mac, $arr_values);
                $network = new Network("WAN", $ip, $cidr, $gateway, $vlantag, $ns1, $ns2);
                $phone = new Phone($mac, $model, false, $network);
            }
            PhoneParser::$phones[$phone->getMAC()] = $phone;
        }
    }

    private static function verifyDHCPUse($arr_values) {
        if (array_key_exists("dhcp", $arr_values) && strtoupper($arr_values["dhcp"]) === "YES") {
            return TRUE;
        }
        if (
                !empty($arr_values["ip"]) &&
                !empty($arr_values["cidr"]) &&
                !empty($arr_values["gw"])
        ) {
            return FALSE;
        }
        return TRUE;
    }

    private static function getNet($mac, $arr_values) {
        try {
            $ip = $arr_values["ip"];
            $cidr = $arr_values["cidr"];
            $gateway = $arr_values["gw"];
            $vlantag = empty($arr_values["vlantag"]) ? 0 : $arr_values["vlantag"];
            $ns1 = array_key_exists("ns1", $arr_values) ? $arr_values["ns1"] : "";
            $ns2 = array_key_exists("ns2", $arr_values) ? $arr_values["ns2"] : "";
        } catch (Exception $exc) {
            //echo $exc->getTraceAsString();
            PhoneParser::exitWithError("Missing network parameters for phone with mac address: $mac.\n\n");
        }
        return array($ip, $cidr, $gateway, $vlantag, $ns1, $ns2);
    }

    static private function exitWithError($errormsg) {
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $errormsg);
        fclose($stderr);
        exit(1);
    }

}
