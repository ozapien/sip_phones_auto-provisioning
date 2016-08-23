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
require_once 'manufacter.php';

class Yealink extends Manufacter {

    public function __construct() {
        /*
         * Manufacter name and models MUST BE ALL UPPERCASE!!!!
         */
        parent::__construct("YEALINK", true);
        $this->addModel(new Model($this, "T23", 3, 3));
        $this->addModel(new Model($this, "T46", 16, 27));
    }

}
