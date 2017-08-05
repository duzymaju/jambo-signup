<?php

namespace JamboBundle\Model;

use DateTime;

/**
 * Model
 */
trait PersonTrait
{
    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $address;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $email;

    /** @var int */
    protected $shirtSize;

    /** @var string */
    protected $sex;

    /** @var DateTime */
    protected $birthDate;

    /** @var string */
    protected $emergencyInfo;

    /** @var string */
    protected $emergencyPhone;

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set first name
     *
     * @param string $firstName first name
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set last name
     *
     * @param string $lastName last name
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Get birth date
     *
     * @return DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set birth date
     *
     * @param DateTime|null $birthDate birth date
     *
     * @return self
     */
    public function setBirthDate(DateTime $birthDate = null)
    {
        if (isset($birthDate)) {
            $this->birthDate = $birthDate;
        }

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set sex
     *
     * @param string $sex sex
     *
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param int $country country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param string $address address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string $phone phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get e-mail
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set e-mail
     *
     * @param string $email e-mail
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get shirt size
     *
     * @return int|null
     */
    public function getShirtSize()
    {
        return $this->shirtSize;
    }

    /**
     * Set shirt size
     *
     * @param int|null $shirtSize shirt size
     *
     * @return self
     */
    public function setShirtSize($shirtSize = null)
    {
        $this->shirtSize = $shirtSize;

        return $this;
    }

    /**
     * Get emergency info
     *
     * @return string|null
     */
    public function getEmergencyInfo()
    {
        return $this->emergencyInfo;
    }

    /**
     * Set emergency info
     *
     * @param string|null $emergencyInfo emergency info
     *
     * @return self
     */
    public function setEmergencyInfo($emergencyInfo = null)
    {
        $this->emergencyInfo = $emergencyInfo;

        return $this;
    }

    /**
     * Get emergency phone
     *
     * @return string|null
     */
    public function getEmergencyPhone()
    {
        return $this->emergencyPhone;
    }

    /**
     * Set emergency phone
     *
     * @param string|null $emergencyPhone emergency phone
     *
     * @return self
     */
    public function setEmergencyPhone($emergencyPhone = null)
    {
        $this->emergencyPhone = $emergencyPhone;

        return $this;
    }
}
