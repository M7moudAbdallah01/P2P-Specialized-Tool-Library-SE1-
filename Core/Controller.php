<?php

class Controller
{
    public function view($view, $data = [])
    {
        $viewPath = "../app/Views/" . $view . ".php";

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View does not exist: " . $view);
        }
    }

    public function model($model)
    {
        $modelPath = "../app/Models/" . $model . ".php";

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        } else {
            die("Model does not exist: " . $model);
        }
    }
}