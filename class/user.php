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
require_once 'server.php';
require_once 'phone.php';

class User {

    private $name;
    private $extension;
    private $password;
    private $server;
    private $phone;

    function __construct($extension) {
        $this->extension = $extension;
    }

    function getName() {
        return $this->name;
    }

    function getExtension() {
        return $this->extension;
    }

    function getPassword() {
        return $this->password;
    }

    function getServer() {
        return $this->server;
    }

    function getPhone() {
        return $this->phone;
    }

    function setPhone(Phone &$phone) {
        $this->phone = $phone;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setExtension($extension) {
        $this->extension = $extension;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setServer(Server &$server) {
        $server->addUser($this);
        $this->server = $server;
    }

}
