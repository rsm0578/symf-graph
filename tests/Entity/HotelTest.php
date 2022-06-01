<?php

namespace App\Tests;

use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HotelTest extends DatabaseDependantTestCase
{
    /** @test */
    public function a_hotel_record_can_be_created_in_the_database()
    {
        // Set up

        // Hotel
        $hotel = new Hotel();
        $hotel->setName('AMZN');
        $hotel->setAddress('Amazon Inc, USA');
        $hotel->setWebsite('www.amazon.in');
        
        $this->entityManager->persist($hotel);

        // Do something
        $this->entityManager->flush();

        $hotelRepository = $this->entityManager->getRepository(Hotel::class);

        $hotelRecord = $hotelRepository->findOneBy(['name' => 'AMZN']);
        // Make assertions
        $this->assertEquals('AMZN', $hotelRecord->getName());
        $this->assertEquals('Amazon Inc, USA', $hotelRecord->getAddress());
        $this->assertEquals('www.amazon.in', $hotelRecord->getWebsite());
    }
}