<?php
namespace App\Utility;

class DepthLooper
{
    public static function extractNodeFields($stdObject)
    {
        $extractedFields = [];
        foreach ($stdObject as $value) {
            if ($value->getName() != '__typename') {
                $extractedFields[] = $value->getName();
            }
        };

        return $extractedFields;
    }
}
