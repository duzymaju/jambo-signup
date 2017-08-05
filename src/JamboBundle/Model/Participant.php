<?php

namespace JamboBundle\Model;

use DateTime;
use Exception;

/**
 * Model
 */
class Participant extends ItemAbstract implements PersonInterface, StatusAwareInterface
{
    use PersonTrait;
    use StatusAwareTrait;

    /** @var int|null */
    protected $gradeId;

    /** @var Troop|null */
    protected $troop;

    /** @var string|null */
    protected $pesel;

    /** @var string|null */
    protected $fatherName;

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
     * Get troop
     *
     * @return Troop|null
     */
    public function getTroop()
    {
        return $this->troop;
    }

    /**
     * Set troop
     *
     * @param Troop|null $troop troop
     *
     * @return self
     */
    public function setTroop(Troop $troop = null)
    {
        $this->troop = $troop;

        return $this;
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
     * Get father name
     *
     * @return string|null
     */
    public function getFatherName()
    {
        return $this->fatherName;
    }

    /**
     * Set father name
     *
     * @param string|null $fatherName father name
     *
     * @return self
     */
    public function setFatherName($fatherName = null)
    {
        $this->fatherName = $fatherName;

        return $this;
    }
}
