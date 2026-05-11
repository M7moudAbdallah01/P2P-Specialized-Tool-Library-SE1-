<?php

require_once __DIR__ . "/../app/Models/Report.php";
require_once __DIR__ . "/../app/Models/Zone.php";

class ModelFactory
{
    public static function create(string $model): object
    {
        switch (strtolower($model)) {

            case 'report':
                return new Report();

            default:
                throw new InvalidArgumentException(
                    "ModelFactory: Unknown model '{$model}'"
                );
        }
    }
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