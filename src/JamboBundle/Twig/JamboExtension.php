<?php

namespace JamboBundle\Twig;

use DateTime;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Intl\Intl;
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
            new Twig_SimpleFilter('languagename', [$this, 'languageNameFilter']),
            new Twig_SimpleFilter('localizedcountry', [$this, 'localizedCountryFilter']),
            new Twig_SimpleFilter('participantdate', [$this, 'participantDateFilter']),
            new Twig_SimpleFilter('permissionname', [$this, 'permissionNameFilter']),
            new Twig_SimpleFilter('peselmodify', [$this, 'peselModifyFilter']),
            new Twig_SimpleFilter('servicename', [$this, 'serviceNameFilter']),
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
     * Language name filter
     *
     * @param string $languageSlug language slug
     *
     * @return string|null
     */
    public function languageNameFilter($languageSlug)
    {
        $languageName = $this->registrationLists->getLanguage($languageSlug);

        return $languageName;
    }

    /**
     * Localized country filter
     *
     * @param string $countryCode country code
     * 
     * @return string
     */
    public function localizedCountryFilter($countryCode)
    {
        $regionBundle = Intl::getRegionBundle();
        $countryName = $regionBundle->getCountryName($countryCode);

        return $countryName;
    }

    /**
     * Permission name filter
     *
     * @param int $permissionId permission ID
     *
     * @return string|null
     */
    public function permissionNameFilter($permissionId)
    {
        $permissionName = $this->registrationLists->getPermission($permissionId);

        return $permissionName;
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
     * Participant date filter
     *
     * @param int $participantDateId participant date ID
     *
     * @return string|null
     */
    public function participantDateFilter($participantDateId)
    {
        $pilgrimDate = $this->registrationLists->getParticipantDate($participantDateId);

        return $pilgrimDate;
    }

    /**
     * Service name filter
     *
     * @param int $serviceId service ID
     *
     * @return string|null
     */
    public function serviceNameFilter($serviceId)
    {
        $serviceName = $this->registrationLists->getService($serviceId);

        return $serviceName;
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
