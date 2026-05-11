<?php
require_once __DIR__ . "/../../Core/database.php";

class Rental {
    public static function EscalateLateReturns() {
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT * FROM rentals WHERE return_date < CURDATE() AND status = 'active'";
        $result = mysqli_query($db, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['rental_id'];
            $today = new DateTime();
            $due = new DateTime($row['return_date']);
            $daysLate = $today->diff($due)->days;

            $penalty = 0;
            $level = 0;

            if ($daysLate <= 3) {
                $penalty = $daysLate * 10;  
                $level = 1;
            } elseif ($daysLate <= 7) {
                $penalty = (3 * 10) + (($daysLate - 3) * 25); 
                $level = 2;
            } else {
                $penalty = 200;    
                $level = 3;
            }

            $update = "UPDATE rentals SET penalty_fee = $penalty, escalation_level = $level WHERE rental_id = $id";
            mysqli_query($db, $update);
        }
    }
}