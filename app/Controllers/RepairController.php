<?php
class RepairController extends Controller {
    
    public function index() {
        $toolModel = $this->model('Tool');
        $data['tools'] = $toolModel->getAllTools(); 
        
        // عرض الـ View اللي سميناها repair_estimator.php
        $this->view('tech/repair_estimator', $data);
    }

   
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $repairModel = $this->model('Maintenance');
            $status = $repairModel->saveEstimate($_POST);
            
            header("Location: /RepairController/index?success=" . $status);
        }
    }
}
