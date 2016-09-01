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

require_once 'dbhelper.php';

class FreePBX extends DBHelper {

    function __construct($host, $db_name, $db_user, $db_pass) {
        parent::__construct("YEALINK", $host, $db_name, $db_user, $db_pass);
    }

    public function defineGetUserSentense() {
        $sql = "SELECT "
                . "id AS extension, "
                . "description AS display_name, "
                . "DATA AS 'secret' "
                . "FROM devices LEFT JOIN sip USING (id) "
                . "WHERE sip.keyword = 'secret' "
                . "HAVING extension = ?;";
        if (!$this->getUserSentence = $this->connection->prepare($sql)) {
            echo "Falló la preparación: (" . $this->connection->errno . ") " . $this->connection->error;
        }
    }

    public function getUserData($uniqueID) {
        if (!$this->getUserSentence->bind_param("i", $uniqueID)) {
            echo "DB execution error: 1(" . $this->getUserSentence->errno . ") " . $this->getUserSentence->error;
        }
        if (!$this->getUserSentence->execute()) {
            echo "DB execution error: 2(" . $this->getUserSentence->errno . ") " . $this->getUserSentence->error;
        }
        $extension = NULL;
        $display_name = NULL;
        $secret = NULL;
        if (!$this->getUserSentence->bind_result($extension, $display_name, $secret)) {
            echo "DB execution error: 3(" . $this->getUserSentence->errno . ") " . $this->getUserSentence->error;
        }


        if ($this->getUserSentence->fetch()) {
            return array("extension" => $extension, "display_name" => $display_name, "secret" => $secret);
        } else {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Can't get user info for: $uniqueID into $this->db_name@$this->host.\n\n");
            fclose($stderr);
            exit(1);
        }
    }

}
