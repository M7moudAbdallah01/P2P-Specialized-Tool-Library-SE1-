<?php
class MaintenanceController extends Controller {
    public function workflow() {
        $maintenanceModel = $this->model('Maintenance');
        $data['external_repairs'] = $maintenanceModel->getExternalWorks();
        
        $this->view('tech/external_repair', $data);
    }
}
