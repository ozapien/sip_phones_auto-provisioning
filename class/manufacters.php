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
require_once 'manufacter/yealink.php';

class Manufacters {

    private $supported = array();

    function __construct() {
        $this->supported["YEALINK"] = new Yealink();
    }

    function getSupported($manufacter = "") {
        if (empty($manufacter)) {
            return $this->supported;
        }
        if (array_key_exists(strtoupper($manufacter), $this->supported)) {
            return $this->supported[strtoupper($manufacter)];
        } else {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Required manufacter ($manufacter) not yet supported.\n");
            fwrite($stderr, "Use one of:\n");
            foreach ($this->supported as $supported_manufacter) {
                fwrite($stderr, "\t" . $supported_manufacter->getName()."\n");
            }
            fclose($stderr);
            exit(1);
        }
    }

}

$manufacters = new Manufacters();

