<?php
require_once __DIR__ . '/../../Core/Controller.php';
class AdminController extends Controller {
    public function lockout() {
        $toolModel = $this->model('Tool');
        $data['tools'] = $toolModel->getActiveTools();
        
        $this->view('admin/emergency_lockout', $data);
    }

    public function executeLock() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $toolId = $_POST['lock_tool_id'];
            $adminModel = $this->model('Admin');
            
            
            if($adminModel->lockToolAndCancelRentals($toolId)) {
                header("Location: /AdminController/lockout?status=success");
            }
        }
    }
}
