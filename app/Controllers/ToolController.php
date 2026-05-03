<?php
session_start();

require_once __DIR__ . '/../Models/Tool.php';
require_once __DIR__ . '/../Models/Certification.php';
require_once __DIR__ . '/../Models/MaintenanceLog.php';
require_once __DIR__ . '/../Models/Battery.php';

class ToolController
{
    public function handleRequest()
    {
        
        if (isset($_POST['add_maintenance'])) {

            $tool_id = $_POST['tool_id'];
            $action  = $_POST['action'];
            $notes   = $_POST['notes'];
            $date    = $_POST['date'];

            $maintModel = new MaintenanceLog();
            $maintModel->add($tool_id, $action, $notes, $date);

            header("Location: index.php?controller=tool&action=show&tool_id=" . $tool_id);
            exit;
        }

        
        if (isset($_POST['add_cert'])) {

            $tool_id = $_POST['tool_id'];
            $type    = $_POST['type'];
            $issue   = $_POST['issue_date'];
            $expiry  = $_POST['expiry_date'];

            $certModel = new Certification();
            $certModel->add($tool_id, $type, $issue, $expiry);
            
            header("Location: index.php?controller=tool&action=show&tool_id=" . $tool_id);
            exit;
        }

        
        if (isset($_POST['update_battery'])) {

            $tool_id = $_POST['tool_id'];
            $cycles  = $_POST['charge_cycles'];
            $status  = $_POST['health_status'];

            $batteryModel = new Battery();
            $batteryModel->update($tool_id, $cycles, $status);

            header("Location: index.php?controller=tool&action=show&tool_id=" . $tool_id);
            exit;
        }

        
        $this->show();
    }

    public function show()
    {
        $tool_id = $_GET['tool_id'] ?? null;

        if (!$tool_id) {
            die("Tool not found");
        }

        $toolModel = new Tool();
        $certModel = new Certification();
        $maintModel = new MaintenanceLog();
        $batteryModel = new Battery();

        $tool = $toolModel->getById($tool_id);
        $certifications = $certModel->getByTool($tool_id);
        $maintenance = $maintModel->getByTool($tool_id);
        $battery = $batteryModel->getByTool($tool_id);

        require __DIR__ . '/../Views/Tools/tool_details.php';
    }
}