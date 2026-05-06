<?php
class InventoryController extends Controller {
    public function index() {
        $inventoryModel = $this->model('Inventory');
        $data['consumables'] = $inventoryModel->getInventory();
        
        $this->view('admin/consumables', $data);
    }

    public function restock() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['item_id'];
            $this->model('Inventory')->updateStock($id, 10);
            header("Location: /InventoryController/index");
        }
    }
}
