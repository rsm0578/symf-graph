<?php
namespace App\Tests\Queries;

use App\Tests\GraphQL\Mutation\WebTestCase;
use GraphQL\GraphQL;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

class AllQueries extends WebTestCase{
	public function testSingleHotel()
    {
        
        $query = <<<'EOF'
query {
    hotel(id: 11){
        id
        name
        website
        address
    }
}
EOF;

        $jsonExpected = <<<EOF
{
    "data": {
        "hotel": {
            "id": 11,
            "name": "Novotel",
            "website": "https://all.accor.com/",
            "address": "Kharadi, Pune"
        }
    }
}
EOF;

        $this->assertQuery($query, $jsonExpected);
    }
    public function testHotelCollection(){
        $query = <<<'EOF'
query hotels_collection($pagingInfo: PagingInfo, $sortInfo: SortInfo, $name: String, $address: String,$website: String){
    hotels_collection(pagingInfo: $pagingInfo, sortInfo: $sortInfo, name: $name, address: $address, website: $website) {
        hotels {
            id
            name
            address
            website
        }
        pagingInfoResponse{
            pageNumber
            pageSize
            totalCount
        }
        errors
  }
}
EOF;

$jsonVariables = <<<EOF
{
    "pagingInfo": {
        "pageNumber":1,
        "pageSize": 10 
    },
    "sortInfo": {
        "sortField": "name",
        "sortOrder": "asc"
    },
    "address": "Kharadi, Pune"

}
EOF;

        $jsonExpected = <<<EOF
{
    "data": {
        "hotels_collection": {
            "hotels": [
                {
                    "id": 11,
                    "name": "Novotel",
                    "website": "https://all.accor.com/",
                    "address": "Kharadi, Pune"
                }
            ],
            "pagingInfoResponse": {
                "pageNumber": 1,
                "pageSize": 10,
                "totalCount": 1
            },
            "errors": null
        }
    }
}
EOF;

        $this->assertQuery($query, $jsonExpected, $jsonVariables);   
    }
}