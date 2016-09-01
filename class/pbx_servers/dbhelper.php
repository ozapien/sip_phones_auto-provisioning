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

abstract class DBHelper {

    protected $name;
    protected $host;
    protected $db_name;
    protected $db_user;
    protected $db_pass;
    protected $connection;
    protected $getUserSentence;

    function __construct($name, $host, $db_name, $db_user, $db_pass) {
        $this->name = $name;
        $this->host = $host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->connection = new mysqli($host, $db_user, $db_pass, $db_name);
        if (!$this->connection) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Error connectind to server DB: $host.\n\n");
            fwrite($stderr, "(" . $this->connection->connect_errno . ") " . $this->connection->connect_error);
            fclose($stderr);
            exit(1);
        }
        $this->defineGetUserSentense();
    }

    abstract function defineGetUserSentense();

    abstract function getUserData($uniqueID);

    public function __destruct() {
        if (is_resource($this->connection) && get_resource_type($this->connection) === 'mysql link') {
            if ($mysqli_connection_thread = mysqli_thread_id($this->connection)) {
                $this->connection->kill($mysqli_connection_thread);
            }
            $this->connection->close();
        }
    }

    function getName() {
        return $this->name;
    }

}
