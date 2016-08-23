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

abstract class UserParser {

    static $CSVFile;
    static $csvusers = [];
    private static $requiredCols;
    private static $optionalCols;
    private static $fileHandler;

    static function parseFile($CSVUserFile, &$phones, &$servers) {
        UserParser::SetCols();
        UserParser::$CSVFile = $CSVUserFile;
        UserParser::openCSV();
        UserParser::iterateCSV();
        // instantiate
        return UserParser::$csvusers;
    }

    private static function SetCols() {
        UserParser::$requiredCols = array(
            "mac" => array("Col" => -1),
            "extension" => array("Col" => -1)
        );
        UserParser::$optionalCols = array(
            "name" => array("Exist" => false, "Col" => -1),
            "user" => array("Exist" => false, "Col" => -1),
            "password" => array("Exist" => false, "Col" => -1),
            "server_ip" => array("Exist" => false, "Col" => -1)
        );
    }

    private static function openCSV() {
        if (!file_exists(UserParser::$CSVFile)) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "User CSV file (" . UserParser::$CSVFile . "), does not exist.\n\n");
            fclose($stderr);
            exit(1);
        }
        UserParser::$fileHandler = fopen(UserParser::$CSVFile, 'r');
        if (empty(UserParser::$fileHandler)) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Cannot open " . UserParser::$CSVFile . " file.\n\n");
            fclose($stderr);
            exit(1);
        }
    }

    private static function iterateCSV() {
        // First line
        $headers_arr = fgetcsv(UserParser::$fileHandler, 0, ',');
        if (empty($headers_arr)) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Empty user CSV file (" . UserParser::$CSVFile . ").\n\n");
            fclose($stderr);
            exit(1);
        }
        UserParser::mapHeaders($headers_arr);
        while (($line_arr = fgetcsv(UserParser::$fileHandler, 0, ',')) !== FALSE) {
            UserParser::mapValues($line_arr);
        }
    }

    private static function mapHeaders($csvHeaders) {
        $required = array_keys(UserParser::$requiredCols);
        $optional = array_keys(UserParser::$optionalCols);
        foreach ($csvHeaders as $key => $value) {
            if (in_array($value, $required)) {
                UserParser::$requiredCols[$value]["Col"] = $key;
            } elseif (in_array($value, $optional)) {
                UserParser::$optionalCols[$value]["Exist"] = true;
                UserParser::$optionalCols[$value]["Col"] = $key;
            }
        }
        if (
                UserParser::$requiredCols["mac"]["Col"] < 0 ||
                UserParser::$requiredCols["extension"]["Col"] < 0
        ) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "User CSV file (" . UserParser::$CSVFile . " does not contain required columns (mac, extension).\n\n");
            fclose($stderr);
        }
        foreach (UserParser::$optionalCols as $key => $value) {
            if (!UserParser::$optionalCols[$key]["Exist"]) {
                unset(UserParser::$optionalCols[$key]);
            }
        }
    }

    private static function mapValues($csvLine) {
        $user = [];
        $extension = ($csvLine[UserParser::$requiredCols["extension"]["Col"]]);
        $user["mac"] = ($csvLine[UserParser::$requiredCols["mac"]["Col"]]);
        $options = array_keys(UserParser::$optionalCols);
        foreach ($options as $option) {
            $user[$option] = ($csvLine[UserParser::$optionalCols[$option]["Col"]]);
        }
        UserParser::$csvusers[$extension] = $user;
    }

}
