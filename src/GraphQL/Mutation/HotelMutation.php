<?php
namespace App\GraphQL\Mutation;

use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use App\Entity\Hotel;

class HotelMutation implements MutationInterface, AliasedInterface
{
	private $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function createHotel(Argument $args): Hotel
	{
		$input = $args['input'];

		$hotel = new Hotel();
		$hotel->setName($input['name']);
		$hotel->setAddress($input['address']);
		$hotel->setWebsite($input['website']);

		$this->em->persist($hotel);
		$this->em->flush();

		return $hotel;
	}

	public static function getAliases() : array
	{
		return [
			'createHotel' => 'create_hotel'
		];
	}
}