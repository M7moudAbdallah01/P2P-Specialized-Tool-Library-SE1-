<?php
require_once '../Models';

class CategoryController {
    private $categoryModel;

    public function __construct($db) {
        $this->categoryModel = new Category($db);
    }

    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        require_once '../app/Views/Admin/manage_categories.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $this->categoryModel->addCategory($name, $description);
            header('Location: index.php?action=manage_categories');
            exit();
        }
    }
}