<?php
namespace App\Utility;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerToEntity
{

    public static function arrayToEntity($data, $class, string $format, $oldEntity)
    {
        $encoders    = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer  = new Serializer($normalizers, $encoders);

        if (count($oldEntity) > 0) {
            return $serializer->deserialize($data, $class, $format, $oldEntity);
        }

        return $serializer->deserialize($data, $class, $format);
    }

}
