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

require_once 'model.php';

abstract class Manufacter {

    protected $name;
    protected $template_path;
    protected $has_global_config_file;
    protected $models = array();

    function __construct($name, $has_global_config_file) {
        $this->name = strtoupper($name);
        $this->has_global_config_file = $has_global_config_file;
        $this->template_path = realpath(__ROOT__ . "Templates/".$this->name);
    }

    function getModel($Req_model) {
        if (array_key_exists(strtoupper($Req_model), $this->models)) {
            return$this->models[strtoupper($Req_model)];
        } else {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, "Required model $Req_model does not exist in manufacter $this->name");
            fclose($stderr);
            exit(1);
        }
    }

    function getName() {
        return $this->name;
    }

    function getTemplate_path() {
        return $this->template_path;
    }

    function getHas_global_config_file() {
        return $this->has_global_config_file;
    }

    function getModels() {
        return $this->models;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setTemplate_path($template_path) {
        $this->template_path = $template_path;
    }

    function setHas_global_config_file($has_global_config_file) {
        $this->has_global_config_file = $has_global_config_file;
    }

    function addModel(Model $model) {
        $this->models[$model->getModel_name()] = $model;
    }

}
