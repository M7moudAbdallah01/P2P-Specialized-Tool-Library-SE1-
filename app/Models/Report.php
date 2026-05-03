<?php
require_once __DIR__ . "/../../Core/database.php";

class Report {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getTotalRevenue() {
        // تأكد أن جدول orders موجود وأن أسماء الأعمدة صحيحة
        $sql = "SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'";
        $result = $this->conn->query($sql);

        // لو الكويري فيها غلط في الداتا بيز هتعمل die وتوريك الغلط فين
        if ($result === false) {
            die("SQL Error in getTotalRevenue: " . $this->conn->error);
        }

        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function getRevenueByZone() {
        // تأكد أن العلاقات بين الجداول (zones, users, orders) مطابقة للداتا بيز عندك
        $sql = "SELECT z.zone_name, SUM(o.total_price) as revenue 
                FROM zones z 
                JOIN users u ON z.zone_id = u.zone_id 
                JOIN orders o ON u.user_id = o.user_id 
                WHERE o.status = 'completed' 
                GROUP BY z.zone_id";
        
        $result = $this->conn->query($sql);

        if ($result === false) {
            // لو الجدول مش موجود أو فيه غلط، هيرجع مصفوفة فاضية بدل ما يوقع الصفحة
            return []; 
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}