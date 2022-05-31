<?php
namespace App\GraphQL\Resolver;

use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

class HotelListResolver implements QueryInterface, AliasedInterface
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
     * Function to remove Hotel list
     *
     * @param      \Overblog\GraphQLBundle\Definition\Argument  $argument  The argument
     *
     * @return     array                                        list of hotels
     */
    public function resolve(Argument $argument)
    {

        if (isset($argument['pagingInfo']['pageSize']) && !is_numeric($argument['pagingInfo']['pageSize'])) {
            return [
                'errors' => "Please enter valid page size",
            ];
        }
        if (isset($argument['pagingInfo']['pageNumber']) && !is_numeric($argument['pagingInfo']['pageNumber'])) {
            return [
                'errors' => "Please enter valid page number",
            ];
        }

        $limit      = (isset($argument['pagingInfo']['pageSize']) ? $argument['pagingInfo']['pageSize'] : 10);
        $pageNumber = (isset($argument['pagingInfo']['pageNumber']) ? $argument['pagingInfo']['pageNumber'] : 1);
        $offset     = ($pageNumber - 1) * $limit;
        $criteria   = [];

        if (!empty($argument['name'])) {
            $criteria['name'] = $argument['name'];
        }
        if (!empty($argument['address'])) {
            $criteria['address'] = $argument['address'];
        }
        if (!empty($argument['website'])) {
            $criteria['website'] = $argument['website'];
        }
        $orderBy   = [];
        $sortOrder = !isset($argument['sortInfo']['sortOrder']) ? 'asc' : $argument['sortInfo']['sortOrder'];
        if (isset($argument['sortInfo']['sortField'])) {
            $orderBy[$argument['sortInfo']['sortField']] = (in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'asc');
        }
        
        $hotels = $this->em->getRepository('App:Hotel')
            ->findBy(
                $criteria,
                $orderBy, $limit, $offset);
        $pagingInfo = $argument['pagingInfo'];

        $pagingInfo['totalCount'] = $this->em->getRepository('App:Hotel')->count([]);

        return ['hotels' => $hotels, 'pagingInfoResponse' => $pagingInfo];
    }

    /**
     * Gets the aliases.
     *
     * @return     array  The aliases.
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'HotelList',
        ];
    }
}
