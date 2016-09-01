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

require_once 'pbx_servers/freepbx.php';
require_once 'server.php';

class DBHelpers {

    private $supported = array();

    function __construct(&$servers) {
        foreach ($servers as $name => $server) {

            if (!array_key_exists($name, $this->supported)) {
                list($host, $db_name, $db_user, $db_pass) = $server->getDBData();
                $this->supported[$name] = (new ReflectionClass($server->getType()))->newInstanceArgs([$host, $db_name, $db_user, $db_pass]);
                $server->setDb_helper($this->supported[$name]);
            }
        }
    }

    function getSupported($type = "") {
        if (empty($type)) {
            return $this->supported;
        }
        if (array_key_exists(strtoupper($type), $this->supported)) {
            return $this->supported[strtoupper($type)];
        } else {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Required DB Helper Type ($type) not yet supported.\n");
            fwrite($stderr, "Use one of:\n");
            foreach ($this->supported as $supported_type) {
                fwrite($stderr, "\t" . $supported_type->getName() . "\n");
            }
            fclose($stderr);
            exit(1);
        }
    }

}
