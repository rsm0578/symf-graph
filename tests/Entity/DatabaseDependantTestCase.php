<?php

namespace App\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseDependantTestCase extends KernelTestCase
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
