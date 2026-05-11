<?php
require_once '../app/Models/Coupon.php';
require_once '../app/Models/Category.php';

class CouponController {
    private $couponModel;
    private $categoryModel;

    public function __construct($db) {
        $this->couponModel = new Coupon($db);
        $this->categoryModel = new Category($db);
    }

    public function index() {
        // معالجة الفورم لو تم الإرسال
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coupon'])) {
            $this->couponModel->addCoupon(
                $_POST['code'],
                $_POST['discount_percent'],
                $_POST['category_id'],
                $_POST['start_date'],
                $_POST['end_date']
            );
            // ريفريش في نفس الصفحة لرؤية النتائج
        }

        // جلب البيانات للعرض
        $coupons = $this->couponModel->getAllCoupons();
        $categories = $this->categoryModel->getAllCategories(); // من وظيفة 24
        
        require_once '../app/Views/Admin/manage_coupons.php';
    }
}