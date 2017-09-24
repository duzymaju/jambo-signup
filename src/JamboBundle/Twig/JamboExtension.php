<?php

namespace JamboBundle\Twig;

use DateTime;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Twig
 */
class JamboExtension extends Twig_Extension
{
    /** @var RegistrationLists */
    private $registrationLists;

    /** @var DateTime */
    private $ageLimit;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator translator
     * @param string              $ageLimit   age limit
     */
    public function __construct(TranslatorInterface $translator, $ageLimit)
    {
        $this->registrationLists = new RegistrationLists($translator);
        $this->ageLimit = (new DateTime($ageLimit))->modify('-1 day');
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = [
            new Twig_SimpleFilter('ageatlimit', [$this, 'ageAtLimitFilter']),
            new Twig_SimpleFilter('changekeys', [$this, 'changeKeysFilter']),
            new Twig_SimpleFilter('districtname', [$this, 'districtNameFilter']),
            new Twig_SimpleFilter('gradename', [$this, 'gradeNameFilter']),
            new Twig_SimpleFilter('methodologygroupname', [$this, 'methodologyGroupNameFilter']),
            new Twig_SimpleFilter('peselmodify', [$this, 'peselModifyFilter']),
            new Twig_SimpleFilter('sexname', [$this, 'sexNameFilter']),
            new Twig_SimpleFilter('shirtsizename', [$this, 'shirtSizeNameFilter']),
            new Twig_SimpleFilter('statusname', [$this, 'statusNameFilter']),
        ];

        return $filters;
    }

    /**
     * Age at limit filter
     *
     * @param DateTime $birthDate birth date
     *
     * @return int
     */
    public function ageAtLimitFilter(DateTime $birthDate)
    {
        $ageAtLimit = (int) $birthDate->diff($this->ageLimit)
            ->format('%y');

        return $ageAtLimit;
    }

    /**
     * Change keys filter
     *
     * @param array $array      array
     * @param array $keysMapper keys mapper
     *
     * @return array
     */
    public function changeKeysFilter(array $array, array $keysMapper)
    {
        foreach ($keysMapper as $oldKey => $newKey) {
            if (array_key_exists($oldKey, $array)) {
                $array[$newKey] = $array[$oldKey];
                unset($array[$oldKey]);
            }
        }

        return $array;
    }

    /**
     * District name filter
     *
     * @param int $districtId district ID
     *
     * @return string|null
     */
    public function districtNameFilter($districtId)
    {
        $districtName = $this->registrationLists->getDistrict($districtId);

        return $districtName;
    }

    /**
     * Grade name filter
     *
     * @param int $gradeId grade ID
     *
     * @return string|null
     */
    public function gradeNameFilter($gradeId)
    {
        $gradeName = $this->registrationLists->getGrade($gradeId);

        return $gradeName;
    }

    /**
     * Methodology group name filter
     *
     * @param int $methodologyGroupId methodology group ID
     *
     * @return string|null
     */
    public function methodologyGroupNameFilter($methodologyGroupId)
    {
        $methodologyGroupName = $this->registrationLists->getMethodologyGroup($methodologyGroupId);

        return $methodologyGroupName;
    }

    /**
     * PESEL modify filter
     *
     * @param string $pesel     PESEL
     * @param bool   $showWhole show whole
     *
     * @return string|null
     */
    public function peselModifyFilter($pesel, $showWhole = false)
    {
        if (empty($pesel)) {
            $modifiedPesel = null;
        } else {
            $formattedPesel = str_repeat('0', 11 - strlen($pesel)) . $pesel;
            $modifiedPesel = $showWhole ? $formattedPesel : substr($formattedPesel, 0, 6) . '*****';
        }

        return $modifiedPesel;
    }

    /**
     * Sex name filter
     *
     * @param string $sexId sex ID
     *
     * @return string|null
     */
    public function sexNameFilter($sexId)
    {
        $sexName = $this->registrationLists->getSex($sexId);

        return $sexName;
    }

    /**
     * Shirt size name filter
     *
     * @param int $shirtSizeId shirt size ID
     *
     * @return string|null
     */
    public function shirtSizeNameFilter($shirtSizeId)
    {
        $shirtSizeName = $this->registrationLists->getShirtSize($shirtSizeId);

        return $shirtSizeName;
    }

    /**
     * Status name filter
     *
     * @param int $status status
     *
     * @return string|null
     */
    public function statusNameFilter($status)
    {
        $statusName = $this->registrationLists->getStatus($status);

        return $statusName;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'jambo_extension';
    }
}
