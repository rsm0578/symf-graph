<?php
namespace App\GraphQL\Resolver;

use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use App\Entity\Hotel;

class HotelResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get single Hotel
     *
     * @param      \Overblog\GraphQLBundle\Definition\Argument  $argument  The argument
     *
     * @return     Hotel                                       Single Hotel
     */
    public function resolve(Argument $argument)
    {

        $hotel = $this->em->getRepository(Hotel::class)->find($argument['id']);

        return $hotel;
    }

    public static function getAliases(): array
    {
        return [
            'resolve' => 'Hotel',
        ];
    }
}
