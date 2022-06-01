<?php
namespace App\Tests\GraphQL\Mutation\Relay;

// use PHPUnit\Framework\TestCase;
// use App\Entity\Hotel;
// use Doctrine\ORM\EntityManagerInterface;
// use App\GraphQL\Mutation\HotelMutation;
// use Overblog\GraphQLBundle\Definition\Argument;
// use Doctrine\ORM\EntityManager;
use App\Tests\GraphQL\Mutation\WebTestCase;

class HotelMutationTest extends WebTestCase
{

    public function testCreateHotel()
    {
        $query = <<<'EOF'
mutation createHotel($input: AddHotel) {
  createHotel(input: $input) {
     name,
     address,
     website
     errors
  }
}

EOF;

        $jsonVariables = <<<EOF
{
    "input": {
        "name": "Hyatt",
        "website": "https://all.accor.com/",
        "address": "Kharadi, Pune"
    }
}
EOF;

        $jsonExpected = <<<EOF
{
    "data": {
        "createHotel": {
            "name": "Hyatt",
            "address": "Kharadi, Pune",
            "website": "https://all.accor.com/",
            "errors": null
        }
    }
}
EOF;

        $this->assertQuery($query, $jsonExpected, $jsonVariables);
    }

    public function testUpdateHotel()
    {
        $query = <<<'EOF'
mutation updateHotel($input: EditHotel) {
  updateHotel(input: $input) {
     name,
     address,
     website
     errors
  }
}

EOF;

        $jsonVariables = <<<EOF
{
    "input": {
        "id": 11,
        "name": "Hyatt",
        "website": "https://all.accor.com/",
        "address": "Kharadi, Pune"
    }
}
EOF;

        $jsonExpected = <<<EOF
{
    "data": {
        "updateHotel": {
            "name": "Hyatt",
            "address": "Kharadi, Pune",
            "website": "https://all.accor.com/",
            "errors": null
        }
    }
}
EOF;

        $this->assertQuery($query, $jsonExpected, $jsonVariables);
    }

    public function testRemoveHotel()
    {
        $query = <<<'EOF'
mutation removeHotel($input: DeleteHotel!) {
  removeHotel(input: $input) {
    errors
  }
}

EOF;

        $jsonVariables = <<<EOF
{
    "input": {
        "id": 11
    }
}
EOF;

        $jsonExpected = <<<EOF
{
    "data": {
        "removeHotel": {
            "errors": null
        }
    }
}
EOF;

        $this->assertQuery($query, $jsonExpected, $jsonVariables);
    }
}
