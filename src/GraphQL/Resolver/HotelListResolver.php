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

	public function resolve(Argument $argument)
	{
		$hotels = $this->em->getRepository('App:Hotel')
		                     ->findBy(
		                     	['name' => $argument['name']],
		                        [], $argument['limit'], 0);

		return ['hotels' => $hotels];
	}

	public static function getAliases() : array
	{
		return [
			'resolve' => 'HotelList'
		];
	}
}