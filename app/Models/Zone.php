<?php
// استدعاء ملف الربط مع قاعدة البيانات
require_once __DIR__ . "/../../Core/database.php";

class Zone {

    // دالة لإضافة منطقة جديدة
    public static function add($name){
        $db = Database::getInstance()->getConnection();
        
        // تنظيف البيانات لمنع المشاكل الأمنية
        $safe_name = mysqli_real_escape_string($db, $name);

        $query = "INSERT INTO zones (name) VALUES ('$safe_name')";
        
        // تنفيذ الأمر وإرجاع النتيجة (true/false)
        return mysqli_query($db, $query);
    }

    // دالة لجلب كل المناطق لعرضها في الجدول
    public static function getAll(){
        $db = Database::getInstance()->getConnection();
        
        $query = "SELECT * FROM zones";
        $result = mysqli_query($db, $query);
        
        if ($result) {
            // تحويل النتيجة لمصفوفة (Array) عشان الـ View يعرضها بسهولة
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }
}