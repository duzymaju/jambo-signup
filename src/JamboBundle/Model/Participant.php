<?php

namespace JamboBundle\Model;

use DateTime;
use Exception;

/**
 * Model
 */
class Participant implements StatusAwareInterface
{
    use RecordTrait;
    use StatusAwareTrait;

    /** @const string */
    const SEX_MALE = 'm';

    /** @const string */
    const SEX_FEMALE = 'f';

    /** @const int */
    const SHIRT_SIZE_XS = 1;

    /** @const int */
    const SHIRT_SIZE_S = 2;

    /** @const int */
    const SHIRT_SIZE_M = 3;

    /** @const int */
    const SHIRT_SIZE_L = 4;

    /** @const int */
    const SHIRT_SIZE_XL = 5;

    /** @const int */
    const SHIRT_SIZE_XXL = 6;

    /** @const int */
    const SHIRT_SIZE_XXXL = 7;

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

    /** @var int|null */
    protected $gradeId;

    /** @var int */
    protected $districtId;

    /** @var Patrol|null */
    protected $patrol;

    /** @var string|null */
    protected $pesel;

    /** @var string|null */
    protected $specialDiet;

    /** @var string|null */
    protected $comments;

    /** @var string */
    protected $guardianName;

    /** @var string */
    protected $guardianPhone;

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
     * Get grade ID
     *
     * @return int|null
     */
    public function getGradeId()
    {
        return $this->gradeId;
    }

    /**
     * Set grade ID
     *
     * @param int|null $gradeId grade ID
     *
     * @return self
     */
    public function setGradeId($gradeId = null)
    {
        $this->gradeId = $gradeId;

        return $this;
    }

    /**
     * Get district ID
     *
     * @return int
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * Set district ID
     *
     * @param int $districtId district ID
     *
     * @return self
     */
    public function setDistrictId($districtId = null)
    {
        $this->districtId = $districtId;

        return $this;
    }

    /**
     * Get patrol
     *
     * @return Patrol|null
     */
    public function getPatrol()
    {
        return $this->patrol;
    }

    /**
     * Set patrol
     *
     * @param Patrol|null $patrol patrol
     *
     * @return self
     */
    public function setPatrol(Patrol $patrol = null)
    {
        $this->patrol = $patrol;

        return $this;
    }

    /**
     * Is patrol member
     *
     * @return bool
     */
    public function isPatrolMember()
    {
        $patrol = $this->getPatrol();
        $isPatrolMember = isset($patrol);

        return $isPatrolMember;
    }

    /**
     * Is patrol leader
     *
     * @return bool
     */
    public function isPatrolLeader()
    {
        $patrol = $this->getPatrol();
        $isPatrolLeader = isset($patrol) && $patrol->getLeader() == $this;

        return $isPatrolLeader;
    }

    /**
     * Get troop
     *
     * @return Troop|null
     */
    public function getTroop()
    {
        $patrol = $this->getPatrol();
        $troop = isset($patrol) ? $patrol->getTroop() : null;

        return $troop;
    }

    /**
     * Is troop member
     *
     * @return bool
     */
    public function isTroopMember()
    {
        $troop = $this->getTroop();
        $isTroopMember = isset($troop);

        return $isTroopMember;
    }

    /**
     * Is troop leader
     *
     * @return bool
     */
    public function isTroopLeader()
    {
        $troop = $this->getTroop();
        $isTroopLeader = isset($troop) && $troop->getLeader() == $this;

        return $isTroopLeader;
    }

    /**
     * Get PESEL
     *
     * @return string|null
     */
    public function getPesel()
    {
        return $this->pesel;
    }

    /**
     * Set PESEL
     *
     * @param string|null $pesel PESEL
     *
     * @return self
     */
    public function setPesel($pesel = null)
    {
        $this->pesel = $pesel;

        if (!empty($pesel)) {
            $this->setBirthDate($this->getBirthDateFromPesel());
            $this->setSex($this->getSexFromPesel());
        }

        return $this;
    }

    /**
     * Get birth date from PESEL
     * 
     * @return DateTime|null
     */
    public function getBirthDateFromPesel()
    {
        if (empty($this->pesel)) {
            return null;
        }

        $year = (int) substr($this->pesel, 0, 2);
        $month = (int) substr($this->pesel, 2, 2);
        $day = (int) substr($this->pesel, 4, 2);

        if ($month > 20 && $month < 33) {
            $month -= 20;
            $year += 2000;
        } elseif ($month > 40 && $month < 53) {
            $month -= 40;
            $year += 2100;
        } elseif ($month > 60 && $month < 73) {
            $month -= 60;
            $year += 2200;
        } elseif ($month > 80 && $month < 93) {
            $month -= 80;
            $year += 1800;
        } else {
            $year += 1900;
        }
        try {
            $birthDate = new DateTime($year . '-' . $month . '-' . $day);
        } catch (Exception $e) {
            $birthDate = null;
        }

        return $birthDate;
    }

    /**
     * Get sex from PESEL
     *
     * @return string|null
     */
    public function getSexFromPesel()
    {
        if (empty($this->pesel)) {
            return null;
        }

        $sex = preg_match('#^[02468]$#', substr($this->pesel, 9, 1)) ? self::SEX_FEMALE : self::SEX_MALE;

        return $sex;
    }

    /**
     * Get special diet
     *
     * @return string|null
     */
    public function getSpecialDiet()
    {
        return $this->specialDiet;
    }

    /**
     * Set special diet
     *
     * @param string|null $specialDiet special diet
     *
     * @return self
     */
    public function setSpecialDiet($specialDiet = null)
    {
        $this->specialDiet = $specialDiet;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set comments
     *
     * @param string|null $comments comments
     *
     * @return self
     */
    public function setComments($comments = null)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get guardian name
     *
     * @return string
     */
    public function getGuardianName()
    {
        return $this->guardianName;
    }

    /**
     * Set guardian name
     *
     * @param string $guardianName guardian name
     *
     * @return self
     */
    public function setGuardianName($guardianName = null)
    {
        $this->guardianName = $guardianName;

        return $this;
    }

    /**
     * Get guardian phone
     *
     * @return string
     */
    public function getGuardianPhone()
    {
        return $this->guardianPhone;
    }

    /**
     * Set guardian phone
     *
     * @param string $guardianPhone guardian phone
     *
     * @return self
     */
    public function setGuardianPhone($guardianPhone = null)
    {
        $this->guardianPhone = $guardianPhone;

        return $this;
    }
}
