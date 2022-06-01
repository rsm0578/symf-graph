<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Hotel;

class HotelFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $hotel = new Hotel();
        $hotel->setName('Novotel');
        $hotel->setAddress('Kharadi, Pune');
        $hotel->setWebsite('https://all.accor.com/');
        
        $manager->persist($hotel);

        $manager->flush();
    }
}
