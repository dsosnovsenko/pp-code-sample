<?php
namespace Pp\CodeSample\Repository;

use Pp\CodeSample\Model\User;

class UserRepository extends AbstractRepository
{
    /**
     * Demo prototype of users repository.
     */
    protected $repository = [
        // uid => array of key/value fields
        100 => [
            'address' => 1,
            'firstName' => 'Max',
            'lastName' => 'Musterman',
            'firm' => 'PlatinPower.com GmbH',
            'phone' => '0123 345 5678',
            'email' => 'max.musterman@codesample.com',
            'pass' => '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4',
        ],
    ];

    /**
     * Create user object.
     *
     * @param int $uid
     * @param array $item
     *
     * @return User
     */
    protected function createUser($uid, array $item)
    {
        /** @var User $response */
        $response = $this->objectManager->get(User::class);
        $response->setUid($uid);
        $response->setAddress($item['address']);
        $response->setFirstName($item['firstName']);
        $response->setLastName($item['lastName']);
        $response->setFirm($item['firm']);
        $response->setPhone($item['phone']);
        $response->setEmail($item['email']);

        return $response;
    }

    /**
     * Find user by email and password.
     *
     * @param $email
     * @param $pass
     *
     * @return User|null
     */
    public function findUser($email, $pass)
    {
        $response = null;

        // the prototype of reality data
        foreach ($this->repository as $uid => $item) {
            if (md5($item['email']) === md5($email) && $item['pass'] === hash('sha256', $pass)) {
                $response = $this->createUser($uid, $item);
                break;
            }
        }

        return $response;
    }

    /**
     * Find user by email.
     *
     * @param string $email
     *
     * @return User|null
     */
    public function findUserByEmail($email)
    {
        $response = null;

        foreach ($this->repository as $uid => $item) {
            if ($item['email'] === $email) {
                $response = $this->createUser($uid, $item);
                break;
            }
        }

        return $response;
    }

    /**
     * Find user by session token.
     *
     * @note The dummy prototype method.
     *
     * @param string $token The email as sha256
     *
     * @return User|null
     */
    public function findUserBySessionToken($token)
    {
        $response = null;

        foreach ($this->repository as $uid => $item) {
            if (hash('sha256', $item['email']) === $token) {
                $response = $this->createUser($uid, $item);
                break;
            }
        }

        return $response;
    }
}