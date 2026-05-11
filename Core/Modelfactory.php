<?php
/* =========================================================
   ModelFactory — Factory Design Pattern
   
   بدل ما تكتب في كل صفحة:
       $report = new Report();
       $zones  = Zone::getAll();
   
   تكتب:
       $report = ModelFactory::create('report');
       $zones  = ModelFactory::getZones();
   
   لو غيّرت اسم الـ class أو مساره، هتغيره هنا بس.
========================================================= */

require_once __DIR__ . "/../app/Models/Report.php";
require_once __DIR__ . "/../app/Models/Zone.php";

class ModelFactory
{
    /* -------------------------------------------------------
       create()
       بتمرر اسم الـ model وبترجع instance منه
       
       Usage:
           $report = ModelFactory::create('report');
           $report->getTotalRevenue();
    ------------------------------------------------------- */
    public static function create(string $model): object
    {
        switch (strtolower($model)) {

            case 'report':
                return new Report();

            // أضف أي model جديد هنا مستقبلاً
            // case 'invoice':
            //     return new Invoice();

            default:
                throw new InvalidArgumentException(
                    "ModelFactory: Unknown model '{$model}'"
                );
        }
    }

    /* -------------------------------------------------------
       getZones()
       Zone بتستخدم static methods فمش محتاج instance،
       الـ Factory هنا بتوفر نقطة دخول موحدة ليها
       
       Usage:
           $zones = ModelFactory::getZones();
    ------------------------------------------------------- */
    public static function getZones(): array
    {
        return Zone::getAll();
    }

    public static function addZone(string $name): bool
    {
        return Zone::addZone($name);
    }

    public static function updateZone(int $id, string $newName): bool
    {
        return Zone::updateZone($id, $newName);
    }

    public static function deleteZone(int $id): bool
    {
        return Zone::deleteZone($id);
    }
}
?>