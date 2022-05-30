<?php
namespace App\GraphQL\Resolver;


use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

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

	public function resolve(Argument $argument)
	{
		$hotel = $this->em->getRepository('App:Hotel')->find($argument['id']);

		return $hotel;
	}

	public static function getAliases() : array
	{
		return [
			'resolve' => 'Hotel'
		];
	}
}