<?php
class Coupon {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // جلب كل الكوبونات مع اسم القسم المرتبط بها
    public function getAllCoupons() {
        $sql = "SELECT c.*, cat.name as category_name 
                FROM coupons c 
                LEFT JOIN category cat ON c.category_id = cat.id 
                ORDER BY c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // إضافة كوبون جديد
    public function addCoupon($code, $discount, $category_id, $start_date, $end_date) {
        $sql = "INSERT INTO coupons (code, discount_percent, category_id, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        // نستخدم null لو الكوبون ملوش قسم معين (Global)
        $cat_id = !empty($category_id) ? $category_id : null;
        return $stmt->execute([strtoupper($code), $discount, $cat_id, $start_date, $end_date]);
    }
}