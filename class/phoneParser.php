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
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Phone CSV file (" . PhoneParser::$CSVFile . "), does not exist.\n\n");
            fclose($stderr);
            exit(1);
        }
        PhoneParser::$fileHandler = fopen(PhoneParser::$CSVFile, 'r');
        if (empty(PhoneParser::$fileHandler)) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Cannot open " . PhoneParser::$CSVFile . " file.\n\n");
            fclose($stderr);
            exit(1);
        }
    }

    private static function iterateCSV() {
        // First line
        $headers_arr = fgetcsv(PhoneParser::$fileHandler, 0, ',');
        if (empty($headers_arr)) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Empty phone CSV file (" . PhoneParser::$CSVFile . ").\n\n");
            fclose($stderr);
            exit(1);
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
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Phone CSV file (" . PhoneParser::$CSVFile . " does not contain required columns (mac, manufacter, model).\n\n");
            fclose($stderr);
        }
        foreach (PhoneParser::$optionalCols as $key => $value) {
            if (!PhoneParser::$optionalCols[$key]["Exist"]) {
                unset(PhoneParser::$optionalCols[$key]);
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
            $user[$option] = ($csvLine[PhoneParser::$optionalCols[$option]["Col"]]);
        }
        PhoneParser::$csvphones[$mac] = $phone;
    }

    private static function instantiatePhones() {
        global $manufacters;
        foreach (PhoneParser::$csvphones as $mac => $arr_values) {
            $manufacter = $manufacters->getSupported($arr_values["manufacter"]);
            $model = $manufacter->getModel($arr_values["model"]);
            if (empty($arr_values["dhcp"])) {
                $phone = new Phone($mac, $model);
            } else {
                list($ip, $cidr, $gateway, $vlantag, $ns1, $ns2) = PhoneParser::getNet($mac, $arr_values);
                $network = new Network("WAN", $ip, $cidr, $gateway, $vlantag, $ns1, $ns2);
                $phone = new Phone($mac, $model, false, $network);
            }
            PhoneParser::$phones[$phone->getMAC()] = $phone;
        }
    }

    private static function getNet($mac, $arr_values) {
        try {
            $ip = $arr_values["ip"];
            $cidr = $arr_values["cidr"];
            $gateway = $arr_values["gateway"];
            $vlantag = empty($arr_values["vlantag"]) ? 0 : $arr_values["vlantag"];
            $ns1 = $arr_values["ns1"];
            $ns2 = empty($arr_values["ns2"]) ? "4.2.2.2" : $arr_values["ns2"];
        } catch (Exception $exc) {
            //echo $exc->getTraceAsString();
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Missing network parameters for phone with mac address: $mac.\n\n");
            fclose($stderr);
            exit(1);
        }
        return array($ip, $cidr, $gateway, $vlantag, $ns1, $ns2);
    }

}
