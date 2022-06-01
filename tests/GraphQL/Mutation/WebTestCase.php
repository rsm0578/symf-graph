<?php

namespace App\Tests\GraphQL\Mutation;

use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    private static $dbLoaded = false;

    public function setUp() : void
    {
        
        if (self::$dbLoaded) {
            return;
        }
        $this->resetDatabase();
        self::$dbLoaded = true;
    }

    protected function resetDatabase()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        // self::runCommand("doctrine:fixtures:load --no-interaction");

    }

    protected function assertQuery($query, $jsonExpected, $jsonVariables = '{}')
    {
        $client = static::makeClient();
        $path = $this->getUrl('overblog_graphql_endpoint');

        $client->request(
            'GET', $_ENV['APP_URL'], ['query' => $query, 'variables' => $jsonVariables], [], ['CONTENT_TYPE' => 'application/graphql']
        );
        $result = $client->getResponse()->getContent();
        // dd(json_decode($jsonExpected, true), json_decode($result, true));
        $this->assertStatusCode(200, $client);
        $this->assertEquals(json_decode($jsonExpected, true), json_decode($result, true), $result);
    }
}
