<?php
namespace App\GraphQL\Mutation;

use App\Entity\Hotel;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class HotelMutation implements MutationInterface, AliasedInterface
{
    private $em;

    /**
     * Constructs a new instance.
     *
     * @param      \Doctrine\ORM\EntityManager  $em     entity Manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Creates a hotel.
     *
     * @param      \Overblog\GraphQLBundle\Definition\Argument  $args   The arguments
     *
     * @return     Hotel                                        created hotel response
     */
    public function createHotel(Argument $args): Hotel | array
    {
        $input = $args['input'];

        $errors = $this->validateInput($input);
        if (!$errors) {
            try {
                $hotel = new Hotel();
                $hotel->setName($input['name']);
                $hotel->setAddress($input['address']);
                $hotel->setWebsite($input['website']);
                $this->em->getRepository(Hotel::class)->upsert($hotel);

                return $hotel;
            } catch (\Exception $err) {
                throw new \Exception($err);

                return false;
            }
        } else {
            return $errors;
        }
    }

    /**
     * update Hotel
     *
     * @param      \Overblog\GraphQLBundle\Definition\Argument  $args   The arguments
     *
     * @return     Hotel                                        update hotel response
     */
    public function updateHotel(Argument $args): Hotel | array
    {
        $input = $args['input'];

        if (isset($input['id'])) {
            try {
                if (!is_numeric($input['id'])) {
                    return [
                        'errors' => "Please enter valid hotel id to update.",
                    ];
                }
                $hotel = $this->em->getRepository(Hotel::class)->find($input['id']);
                if (empty($hotel)) {
                    return [
                        'errors' => "No hotel found for given hotel id.",
                    ];
                }
                if (isset($input['name'])) {
                    $hotel->setName($input['name']);
                }
                if (isset($input['address'])) {
                    $hotel->setAddress($input['address']);
                }
                if (isset($input['website'])) {
                    $url = $input['website'];
                    $url = filter_var($url, FILTER_SANITIZE_URL);
                    if (filter_var($input['website'], FILTER_VALIDATE_URL) === false) {
                        $validationErrors['errors'] = 'Hotel website must be valid.';

                        return ['errors' => $validationErrors['errors']];
                    }
                    $hotel->setWebsite($input['website']);
                }
                $this->em->getRepository(Hotel::class)->upsert($hotel);
            } catch (\Exception $err) {
                throw new \Exception($err);

                return false;
            }
        } else {
            return [
                'errors' => "Please enter hotel id to update",
            ];
        }

        return $hotel;
    }

    /**
     * Removes a hotel.
     *
     * @param      \Overblog\GraphQLBundle\Definition\Argument  $args   The arguments
     *
     * @return     Hotel|array                                  removed Hotel
     */
    public function removeHotel(Argument $args): Hotel | array
    {
        $input = $args['input'];
        if (isset($input['id'])) {
            try {
                $hotel = $this->em->getRepository(Hotel::class)->find($input['id']);
                if (empty($hotel)) {
                    return [
                        'errors' => "No hotel found for given hotel id.",
                    ];
                }
                $this->em->getRepository(Hotel::class)->remove($hotel);

                return [
                    'message' => "Hotel removed successfully",
                ];
            } catch (\Exception $err) {
                throw new \Exception($err);

                return false;
            }
        } else {
            return [
                'errors' => "Please enter hotel id to delete",
            ];
        }

        return $hotel;

    }

    /**
     * Function to validate input
     *
     * @param      Argument  $input  The input
     *
     * @return     array   error array if any
     */
    private function validateInput($input)
    {
        $validationErrors = [];
        if (empty($input['name'])) {
            // $validationErrors['errors']['name']['required'] = 'Please enter hotel name.';
            $validationErrors['errors'] = 'Please enter hotel name.';

            return ['errors' => $validationErrors['errors']];
        }
        if (strlen($input['name']) < 1 || strlen($input['name']) > 255) {
            // $validationErrors['errors']['name']['length'] = 'Hotel name must be between 1 & 255 characters.';
            $validationErrors['errors'] = 'Hotel name must be between 1 & 255 characters.';

            return ['errors' => $validationErrors['errors']];
        }
        if (empty($input['address'])) {
            // $validationErrors['errors']['address']['required'] = 'Please enter hotel address.';
            $validationErrors['errors'] = 'Please enter hotel address.';

            return ['errors' => $validationErrors['errors']];
        }
        if (strlen($input['address']) < 1 || strlen($input['address']) > 255) {
            // $validationErrors['errors']['address']['length'] = 'Hotel address must be between 1 & 255 characters.';
            $validationErrors['errors'] = 'Hotel address must be between 1 & 255 characters.';

            return ['errors' => $validationErrors['errors']];
        }
        if (empty($input['website'])) {
            // $validationErrors['errors']['website']['required'] = 'Please enter hotel website.';
            $validationErrors['errors'] = 'Please enter hotel website.';

            return ['errors' => $validationErrors['errors']];
        }
        if (strlen($input['website']) < 1 || strlen($input['website']) > 255) {
            // $validationErrors['errors']['website']['length'] = 'Hotel website must be between 1 & 255 characters.';
            $validationErrors['errors'] = 'Hotel website must be between 1 & 255 characters.';

            return ['errors' => $validationErrors['errors']];
        }
        $url = $input['website'];
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (filter_var($input['website'], FILTER_VALIDATE_URL) === false) {
            // $validationErrors['errors']['website']['url'] = 'Hotel website must be valid.';
            $validationErrors['errors'] = 'Hotel website must be valid.';

            return ['errors' => $validationErrors['errors']];
        }

        return [];
    }

    /**
     * Gets the aliases.
     *
     * @return     array  The aliases.
     */
    public static function getAliases(): array
    {
        return [
            'createHotel' => 'create_hotel',
            'updateHotel' => 'update_hotel',
            'removeHotel' => 'remove_hotel',
        ];
    }
}
