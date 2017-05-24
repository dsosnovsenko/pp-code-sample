<?php
namespace Pp\CodeSample\Model;

class User
{
    /**
     * User id.
     *
     * @var int
     */
    protected $uid = 0;

    /**
     * @var int $address
     */
    protected $address = 0;

    /**
     * List of translated addresses:
     * 0 - unknown
     * 1 - Herr
     * 2 - Frau
     *
     * @var array
     */
    protected $addresses = [];

    /** @var string $firstName */
    protected $firstName = '';

    /** @var string $lastName */
    protected $lastName = '';

    /** @var string $firm */
    protected $firm = '';

    /** @var string $phone */
    protected $phone = '';

    /** @var string $email */
    protected $email = '';

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->addresses = [
            0 => '',
            1 => _('Herr'),
            2 => _('Frau'),
        ];
    }

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return int
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getTranslatedAddress()
    {
        return isset($this->addresses[$this->address])
            ? $this->addresses[$this->address]
            : '';
    }

    /**
     * @param int $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * @param string $firm
     */
    public function setFirm($firm)
    {
        $this->firm = $firm;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}