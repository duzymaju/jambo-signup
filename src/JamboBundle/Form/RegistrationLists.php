<?php

namespace JamboBundle\Form;

use JamboBundle\Model\Participant;
use JamboBundle\Model\StatusAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Form
 */
class RegistrationLists
{
    /** @var string */
    const COUNTRY_POLAND = 'pl';

    /** @var int */
    const GRADE_NO = 0;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get status labels
     *
     * @param bool $forceAll force all
     *
     * @return array
     */
    public function getStatusLabels($forceAll = false)
    {
        $labels = $forceAll ? [
            'form.status.not_completed' => StatusAwareInterface::STATUS_NOT_COMPLETED,
        ] : [];
        $labels['form.status.completed'] = StatusAwareInterface::STATUS_COMPLETED;
        $labels['form.status.confirmed'] = StatusAwareInterface::STATUS_CONFIRMED;
        $labels['form.status.payed'] = StatusAwareInterface::STATUS_PAYED;
        $labels['form.status.resigned'] = StatusAwareInterface::STATUS_RESIGNED;

        return $labels;
    }

    /**
     * Get statuses
     *
     * @param bool $forceAll force all
     *
     * @return array
     */
    public function getStatuses($forceAll = false)
    {
        $statuses = $this->translateLabels($this->getStatusLabels($forceAll));

        return $statuses;
    }

    /**
     * Get status
     *
     * @param int $statusId status ID
     *
     * @return string|null
     */
    public function getStatus($statusId)
    {
        $statuses = $this->getStatuses(true);
        $status = array_key_exists($statusId, $statuses) ? $statuses[$statusId] : null;

        return $status;
    }

    /**
     * Get sex labels
     *
     * @return array
     */
    public function getSexLabels()
    {
        $labels = [
            'form.sex.male' => Participant::SEX_MALE,
            'form.sex.female' => Participant::SEX_FEMALE,
        ];

        return $labels;
    }

    /**
     * Get sexes
     *
     * @return array
     */
    public function getSexes()
    {
        $sexes = $this->translateLabels($this->getSexLabels());

        return $sexes;
    }

    /**
     * Get sex
     *
     * @param int $sexId sex ID
     *
     * @return string|null
     */
    public function getSex($sexId)
    {
        $sexes = $this->getSexes();
        $sex = array_key_exists($sexId, $sexes) ? $sexes[$sexId] : null;

        return $sex;
    }

    /**
     * Get shirt size labels
     *
     * @return array
     */
    public function getShirtSizeLabels()
    {
        $labels = [
            'form.shirt_size.xs' => Participant::SHIRT_SIZE_XS,
            'form.shirt_size.xs_kids' => Participant::SHIRT_SIZE_XS_KIDS,
            'form.shirt_size.s' => Participant::SHIRT_SIZE_S,
            'form.shirt_size.s_kids' => Participant::SHIRT_SIZE_S_KIDS,
            'form.shirt_size.m' => Participant::SHIRT_SIZE_M,
            'form.shirt_size.m_kids' => Participant::SHIRT_SIZE_M_KIDS,
            'form.shirt_size.l' => Participant::SHIRT_SIZE_L,
            'form.shirt_size.l_kids' => Participant::SHIRT_SIZE_L_KIDS,
            'form.shirt_size.xl' => Participant::SHIRT_SIZE_XL,
            'form.shirt_size.xl_kids' => Participant::SHIRT_SIZE_XL_KIDS,
            'form.shirt_size.xxl' => Participant::SHIRT_SIZE_XXL,
            'form.shirt_size.xxl_kids' => Participant::SHIRT_SIZE_XXL_KIDS,
            'form.shirt_size.xxxl' => Participant::SHIRT_SIZE_XXXL,
            'form.shirt_size.xxxl_kids' => Participant::SHIRT_SIZE_XXXL_KIDS,
        ];

        return $labels;
    }

    /**
     * Get shirt sizes
     *
     * @return array
     */
    public function getShirtSizes()
    {
        $shirtSizes = $this->translateLabels($this->getShirtSizeLabels());

        return $shirtSizes;
    }

    /**
     * Get shirt size
     *
     * @param int $shirtSizeId shirt size ID
     *
     * @return string|null
     */
    public function getShirtSize($shirtSizeId)
    {
        $shirtSizes = $this->getShirtSizes();
        $shirtSize = array_key_exists($shirtSizeId, $shirtSizes) ? $shirtSizes[$shirtSizeId] : null;

        return $shirtSize;
    }

    /**
     * Get regions
     *
     * @param bool $allowDifferent allow different
     *
     * @return array
     */
    public function getRegions($allowDifferent = true)
    {
        $regions = $allowDifferent ? [
            0 => $this->translator->trans('form.regions_different'),
        ] : [];
        
        foreach ($this->getStructure() as $regionId => $region) {
            $regions[$regionId] = $region['name'];
        }

        return $regions;
    }

    /**
     * Get region
     *
     * @param int $regionId region ID
     *
     * @return string|null
     */
    public function getRegion($regionId)
    {
        $structure = $this->getStructure();
        $region = array_key_exists($regionId, $structure) ? $structure[$regionId]['name'] : null;

        return $region;
    }

    /**
     * Get districts
     *
     * @param int|null $regionId       region ID
     * @param bool     $allowDifferent allow different
     *
     * @return array
     */
    public function getDistricts($regionId = null, $allowDifferent = true)
    {
        $districts = $allowDifferent ? [
            0 => $this->translator->trans('form.districts_different'),
        ] : [];

        foreach ($this->getStructure() as $id => $region) {
            if (!isset($regionId) || $id == $regionId) {
                foreach ($region['districts'] as $districtKey => $district) {
                    $districtId = $this->getDistrictId($id, $districtKey);
                    $districts[$districtId] = $district;
                }
            }
        }

        return $districts;
    }

    /**
     * Get district
     *
     * @param int  $districtId     district ID
     * @param bool $allowDifferent allow different
     *
     * @return string|null
     */
    public function getDistrict($districtId, $allowDifferent = true)
    {
        if ($allowDifferent && $districtId == 0) {
            return $this->translator->trans('form.districts_different');
        }

        $regionId = $this->getRegionId($districtId);
        $districtKey = $this->getDistrictKey($districtId);
        $structure = $this->getStructure();
        $district = array_key_exists($regionId, $structure) &&
            array_key_exists($districtKey, $structure[$regionId]['districts']) ?
            $structure[$regionId]['districts'][$districtKey] : null;

        return $district;
    }

    /**
     * Get grade labels
     *
     * @return array
     */
    public function getGradeLabels()
    {
        $labels = [
            'form.grade.no' => self::GRADE_NO,
            'form.grade.guide' => 1,
            'form.grade.sub_scoutmaster' => 2,
            'form.grade.scoutmaster' => 3,
        ];

        return $labels;
    }

    /**
     * Get grades
     *
     * @return array
     */
    public function getGrades()
    {
        $grades = $this->translateLabels($this->getGradeLabels());

        return $grades;
    }

    /**
     * Get grade
     *
     * @param int $gradeId grade ID
     *
     * @return string|null
     */
    public function getGrade($gradeId)
    {
        $grades = $this->getGrades();
        $grade = array_key_exists($gradeId, $grades) ? $grades[$gradeId] : null;

        return $grade;
    }

    /**
     * Get methodology group labels
     *
     * @return array
     */
    public function getMethodologyGroupLabels()
    {
        $labels = [
            'form.methodology_group.cub_scouts' => 1,
            'form.methodology_group.scouts' => 2,
            'form.methodology_group.senior_scouts' => 3,
            'form.methodology_group.rovers' => 4,
        ];

        return $labels;
    }

    /**
     * Get methodology groups
     *
     * @return array
     */
    public function getMethodologyGroups()
    {
        $methodologyGroups = $this->translateLabels($this->getMethodologyGroupLabels());

        return $methodologyGroups;
    }

    /**
     * Get methodology group
     *
     * @param int $methodologyGroupId methodology group ID
     *
     * @return string|null
     */
    public function getMethodologyGroup($methodologyGroupId)
    {
        $methodologyGroups = $this->getMethodologyGroups();
        $methodologyGroup = array_key_exists($methodologyGroupId, $methodologyGroups) ?
            $methodologyGroups[$methodologyGroupId] : null;

        return $methodologyGroup;
    }

    /**
     * Region contains district
     *
     * @param int $regionId   region ID
     * @param int $districtId district ID
     *
     * @return bool
     */
    public function regionContainsDistrict($regionId, $districtId)
    {
        $structure = $this->getStructure();
        if (!array_key_exists($regionId, $structure) || $regionId !== $this->getRegionId($districtId)) {
            return false;
        }

        $districtKey = $this->getDistrictKey($districtId);
        $districts = $structure[$regionId]['districts'];
        return array_key_exists($districtKey, $districts);
    }

    /**
     * Get district ID
     * 
     * @param int $regionId    region ID
     * @param int $districtKey district key
     *
     * @return int
     */
    private function getDistrictId($regionId, $districtKey)
    {
        $districtId = $districtKey + $regionId * 1000;

        return $districtId;
    }

    /**
     * Get region ID
     *
     * @param int $districtId district ID
     *
     * @return int
     */
    private function getRegionId($districtId)
    {
        $regionId = (int) floor($districtId / 1000);

        return $regionId;
    }

    /**
     * Get district key
     *
     * @param int $districtId district ID
     *
     * @return int
     */
    private function getDistrictKey($districtId)
    {
        $regionId = $this->getRegionId($districtId);
        $districtKey = $districtId - $regionId * 1000;

        return $districtKey;
    }

    /**
     * Translate labels
     *
     * @param array $labels labels
     *
     * @return array
     */
    private function translateLabels(array $labels)
    {
        $list = [];
        foreach ($labels as $label => $id) {
            /** @Ignore */ $list[$id] = $this->translator->trans($label);
        }

        return $list;
    }

    /**
     * Get structure
     *
     * @return array
     */
    public function getStructure()
    {
        $structure = [
            20 => [
                'name' => 'Białostocka',
                'districts' => [
                    1 => 'Augustów',
                    2 => 'Białystok',
                    4 => 'Biebrzański',
                    3 => 'Bielsk Podlaski',
                    5 => 'Kolno',
                    6 => 'Nadnarwiański',
                    8 => 'Sokółka',
                    9 => 'Suwałki',
                ],
            ],
            2 => [
                'name' => 'Dolnośląska',
                'districts' => [
                    1 => 'Bierutów',
                    2 => 'Bolesławiec',
                    3 => 'Bystrzyca Kłodzka',
                    5 => 'Długołęka',
                    7 => 'Głogów',
                    8 => 'Jawor',
                    33 => 'Kamienna Góra',
                    9 => 'Karkonoski',
                    10 => 'Kąty Wrocławskie',
                    11 => 'Kłodzko',
                    12 => 'Legnica',
                    13 => 'Lubań',
                    14 => 'Lubin',
                    15 => 'Łagiewniki',
                    17 => 'Oleśnica',
                    18 => 'Oława',
                    19 => 'Polkowice',
                    31 => 'Powiatu Trzebnickiego',
                    21 => 'Syców',
                    22 => 'Środa Śląska',
                    23 => 'Świdnica',
                    26 => 'Wrocław',
                    34 => 'Zgorzelec',
                    6 => 'Ziemi Dzierżoniowskiej',
                    24 => 'Ziemi Wałbrzyskiej',
                    30 => 'Złotoryja',
                    32 => 'Żórawina',
                ],
            ],
            22 => [
                'name' => 'Gdańska',
                'districts' => [
                    1 => 'Bytów',
                    2 => 'Czarna Woda',
                    3 => 'Gdańsk - Wrzeszcz - Oliwa',
                    4 => 'Gdańsk - Portowa',
                    14 => 'Gdańsk - Przymorze',
                    5 => 'Gdańsk - Śródmieście',
                    6 => 'Gdynia',
                    8 => 'Kartuzy',
                    9 => 'Kościerzyna',
                    10 => 'Kwidzyn',
                    11 => 'Lębork',
                    12 => 'Malbork',
                    13 => 'Miastko',
                    15 => 'Puck',
                    16 => 'Rumia',
                    20 => 'Sopot',
                    21 => 'Stare Pole',
                    22 => 'Starogard Gdański',
                    23 => 'Sztum',
                    24 => 'Tczew',
                    25 => 'Wejherowo',
                    19 => 'Ziemi Słupskiej',
                ],
            ],
            26 => [
                'name' => 'Kielecka',
                'districts' => [
                    1 => 'Busko-Zdrój',
                    2 => 'Jędrzejów',
                    3 => 'Kielce - Miasto',
                    4 => 'Kielce - Południe',
                    5 => 'Kielce - Powiat',
                    6 => 'Końskie',
                    7 => 'Lelów',
                    8 => 'Miechów',
                    9 => 'Opatów',
                    10 => 'Ostrowiec Świętokrzyski',
                    11 => 'Pińczów',
                    12 => 'Sandomierz',
                    16 => 'Skarżysko-Kamienna',
                    13 => 'Starachowice',
                    14 => 'Staszów',
                    15 => 'Szczekocińsko-Włoszczowski',
                ],
            ],
            12 => [
                'name' => 'Krakowska',
                'districts' => [
                    1 => 'Andrychów',
                    2 => 'Bochnia',
                    3 => 'Brzesko',
                    6 => 'Gorczański',
                    7 => 'Gorlice',
                    8 => 'Jordanów',
                    9 => 'Kęty',
                    11 => 'Kraków - Nowa Huta',
                    12 => 'Kraków - Podgórze',
                    13 => 'Kraków - Śródmieście',
                    10 => 'Kraków - Krowodrza',
                    14 => 'Krzeszowice',
                    16 => 'Myślenice',
                    17 => 'Nowy Sącz',
                    18 => 'Olkusz',
                    19 => 'Oświęcim',
                    29 => 'Podhalański',
                    20 => 'Podkrakowski',
                    22 => 'Tarnów',
                    23 => 'Trzebinia',
                    24 => 'Wieliczka',
                    28 => 'Ziemi Wadowickiej',
                ],
            ],
            4 => [
                'name' => 'Kujawsko-Pomorska',
                'districts' => [
                    1 => 'Aleksandrów Kujawski',
                    2 => 'Brodnica',
                    3 => 'Bydgoszcz - Miasto',
                    4 => 'Chełmża',
                    6 => 'Chojnice',
                    25 => 'Golub - Dobrzyń',
                    7 => 'Grudziądz',
                    23 => 'Pałuki',
                    8 => 'Inowrocław',
                    9 => 'Jabłonowo Pomorskie',
                    10 => 'Kijewo Królewskie',
                    11 => 'Koronowo',
                    13 => 'Mogilno',
                    14 => 'Nakło Nad Notecią',
                    15 => 'Nowe Miasto Lubawskie',
                    17 => 'Rypin',
                    18 => 'Solec Kujawski',
                    19 => 'Świecie - Powiat',
                    20 => 'Toruń',
                    24 => 'Tuchola',
                    5 => 'Włocławek - Powiat',
                    22 => 'Włocławek - Miasto',
                ],
            ],
            6 => [
                'name' => 'Lubelska',
                'districts' => [
                    2 => 'Biłgoraj',
                    3 => 'Chełm',
                    4 => 'Hrubieszów',
                    8 => 'Lublin',
                    10 => 'Łęczna',
                    11 => 'Łuków',
                    13 => 'Puławy',
                    14 => 'Ryki',
                    15 => 'Tomaszów Lubelski',
                    17 => 'Zamość',
                ],
            ],
            10 => [
                'name' => 'Łódzka',
                'districts' => [
                    1 => 'Brzeziny',
                    2 => 'Głowno',
                    3 => 'Inowłódz',
                    4 => 'Konstantynów Łódzki',
                    5 => 'Kutno',
                    6 => 'Łask',
                    7 => 'Łowicz',
                    8 => 'Łódź - Bałuty',
                    9 => 'Łódź - Górna',
                    10 => 'Łódź - Polesie',
                    11 => 'Łódź - Śródmieście',
                    12 => 'Łódź - Widzew',
                    13 => 'Opoczno',
                    14 => 'Ozorków',
                    15 => 'Pabianice',
                    16 => 'Piotrków Trybunalski',
                    17 => 'Radomsko',
                    18 => 'Sieradz',
                    19 => 'Skierniewice',
                    20 => 'Tomaszów Mazowiecki',
                    21 => 'Tuszyn',
                    22 => 'Uniejów',
                    23 => 'Zduńska Wola',
                    25 => 'Zgierz',
                    27 => 'Żychlin',
                ],
            ],
            14 => [
                'name' => 'Mazowiecka',
                'districts' => [
                    35 => 'Ciechanów',
                    34 => 'Doliny Liwca',
                    2 => 'Gostynin',
                    3 => 'Grójec',
                    4 => 'Jaktorów',
                    6 => 'Lipsko',
                    8 => 'Maków Mazowiecki',
                    10 => 'Mazowsze - Mińsk Mazowiecki',
                    9 => 'Mazowsze - Płock',
                    11 => 'Mława',
                    12 => 'Mszczonów',
                    13 => 'Ostrołęka',
                    15 => 'Pionki',
                    16 => 'Płock',
                    17 => 'Płońsk',
                    18 => 'Podlasie',
                    19 => 'Przasnysz',
                    20 => 'Przysucha',
                    22 => 'Radom - Miasto',
                    23 => 'Radom - Powiat',
                    24 => 'Sierpc',
                    25 => 'Sochaczew',
                    26 => 'Sokołów Podlaski',
                    30 => 'Wyszków',
                    32 => 'Żuromin',
                    33 => 'Żyrardów',
                ],
            ],
            16 => [
                'name' => 'Opolska',
                'districts' => [
                    1 => 'Brzeg',
                    3 => 'Głubczyce',
                    6 => 'Kędzierzyn-Koźle',
                    8 => 'Krapkowice',
                    9 => 'Namysłów',
                    10 => 'Niemodlin',
                    11 => 'Nysa',
                    12 => 'Opole',
                    14 => 'Praszka',
                ],
            ],
            18 => [
                'name' => 'Podkarpacka',
                'districts' => [
                    7 => 'Bieszczadzki',
                    1 => 'Brzozów',
                    2 => 'Dębica',
                    3 => 'Jarosław',
                    4 => 'Jasło',
                    5 => 'Kolbuszowa',
                    6 => 'Krosno',
                    17 => 'Stalowa Wola',
                    8 => 'Leżajsk',
                    9 => 'Lubaczów',
                    10 => 'Łańcut',
                    11 => 'Mielec',
                    12 => 'Nisko',
                    14 => 'Przeworsk',
                    15 => 'Ropczycko-Sędziszowski',
                    16 => 'Rzeszów',
                    19 => 'Strzyżów',
                    20 => 'Tarnobrzeg',
                    21 => 'Ustrzyki Dolne',
                    13 => 'Ziemi Pilźnieńskiej',
                    22 => 'Ziemi Przemyskiej',
                    23 => 'Ziemi Rzeszowskiej',
                    24 => 'Ziemi Sanockiej',
                ],
            ],
            34 => [
                'name' => 'Stołeczna',
                'districts' => [
                    1 => 'Błonie',
                    2 => 'Celestynów',
                    3 => 'Garwolin',
                    5 => 'Grodzisk Mazowiecki',
                    8 => 'Legionowo',
                    9 => 'Milanówek',
                    11 => 'Nowy Dwór Mazowiecki',
                    12 => 'Otwock',
                    13 => 'Piaseczno',
                    14 => 'Piastów',
                    15 => 'Pruszków',
                    16 => 'Sulejówek',
                    17 => 'Tłuszcz',
                    21 => 'Warszawa - Centrum',
                    22 => 'Warszawa - Mokotów',
                    23 => 'Warszawa - Ochota',
                    24 => 'Warszawa - Praga - Południe',
                    25 => 'Warszawa - Praga - Północ',
                    26 => 'Warszawa - Ursus',
                    27 => 'Warszawa - Ursynów',
                    31 => 'Warszawa - Wawer',
                    28 => 'Warszawa - Wola',
                    29 => 'Warszawa - Żoliborz',
                    18 => 'Wołomin',
                    30 => 'Zalew',
                    19 => 'Ząbki',
                    20 => 'Zielonka',
                ],
            ],
            24 => [
                'name' => 'Śląska',
                'districts' => [
                    1 => 'Beskidzki',
                    2 => 'Bytom',
                    3 => 'Chorzów',
                    4 => 'Chrzanów',
                    6 => 'Czechowice-Dziedzice',
                    7 => 'Czerwionka-Leszczyny',
                    8 => 'Częstochowa',
                    9 => 'Dąbrowa Górnicza',
                    32 => 'Hufiec Ziemi Zawierciańskiej',
                    11 => 'Jastrzębie-Zdrój',
                    12 => 'Jaworzno',
                    13 => 'Katowice',
                    14 => 'Kłobuck',
                    16 => 'Lubliniec',
                    18 => 'Mysłowice',
                    20 => 'Piekary Śląskie',
                    21 => 'Ruda Śląska',
                    35 => 'Rydułtowy',
                    23 => 'Siemianowice Śląskie',
                    24 => 'Sosnowiec',
                    28 => 'Węgierska Górka',
                    30 => 'Zabrze',
                    31 => 'Ziemi Będzińskiej',
                    5 => 'Ziemi Cieszyńskiej',
                    10 => 'Ziemi Gliwickiej',
                    17 => 'Ziemi Mikołowskiej',
                    19 => 'Ziemi Myszkowskiej',
                    15 => 'Ziemi Raciborskiej',
                    22 => 'Ziemi Rybnickiej',
                    26 => 'Ziemi Tarnogórskiej',
                    27 => 'Ziemi Tyskiej',
                    29 => 'Ziemi Wodzisławskiej',
                    33 => 'Żory',
                    34 => 'Żywiec',
                ],
            ],
            28 => [
                'name' => 'Warmińsko-Mazurska',
                'districts' => [
                    1 => 'Bartoszyce',
                    2 => 'Biskupiec',
                    3 => 'Braniewo',
                    5 => 'Działdowo',
                    6 => 'Elbląg',
                    7 => 'Ełk',
                    8 => 'Giżycko',
                    9 => 'Gołdap',
                    10 => 'Iława',
                    11 => 'Kętrzyn',
                    12 => 'Lidzbark Welski',
                    13 => 'Morąg',
                    14 => 'Mrągowo',
                    15 => 'Nidzica',
                    16 => 'Olecko',
                    17 => 'Orneta',
                    18 => 'Ostróda',
                    20 => 'Pisz',
                    21 => 'Rodło',
                    22 => 'Warmiński',
                    23 => 'Wegorzewo',
                ],
            ],
            30 => [
                'name' => 'Wielkopolska',
                'districts' => [
                    1 => 'Chodzież',
                    2 => 'Czerwonak',
                    3 => 'Gniezno',
                    7 => 'Jarocin',
                    8 => 'Kalisz',
                    9 => 'Kępno',
                    10 => 'Koło',
                    11 => 'Konin',
                    12 => 'Kościan',
                    13 => 'Koźmin Wielkopolski',
                    14 => 'Kórnik',
                    15 => 'Krotoszyn',
                    16 => 'Leszno',
                    17 => 'Nowy Tomyśl',
                    18 => 'Oborniki Wielkopolskie',
                    20 => 'Ostrów Wielkopolski',
                    21 => 'Piła',
                    19 => 'Powiatu Kaliskiego',
                    23 => 'Poznań - Grunwald',
                    24 => 'Poznań - Jeżyce',
                    25 => 'Poznań - Nowe Miasto',
                    26 => 'Poznań - Stare Miasto',
                    27 => 'Poznań - Śródmieście',
                    28 => 'Poznań - Wilda',
                    29 => 'Poznań - Rejon',
                    30 => 'Rawicz',
                    32 => 'Szamotuły',
                    33 => 'Śmigiel',
                    34 => 'Śrem',
                    35 => 'Środa Wielkopolska',
                    36 => 'Trzcianka',
                    37 => 'Trzemeszno',
                    38 => 'Turek',
                    39 => 'Wągrowiec',
                    42 => 'Września',
                    40 => 'Wschowa',
                    5 => 'Ziemi Ostrzeszowskiej',
                    41 => 'Złotów',
                ],
            ],
            32 => [
                'name' => 'Zachodniopomorska',
                'districts' => [
                    2 => 'Czaplinek',
                    3 => 'Goleniów',
                    4 => 'Kołobrzeg',
                    6 => 'Myślibórz',
                    7 => 'Sławno',
                    8 => 'Stargard Szczeciński',
                    9 => 'Szczecin',
                    10 => 'Szczecin - Dąbie',
                    11 => 'Szczecin - Pogodno',
                    12 => 'Szczecinek',
                    5 => 'Ziemi Koszalińskiej',
                    13 => 'Ziemi Wolińskiej',
                ],
            ],
            8 => [
                'name' => 'Ziemi Lubuskiej',
                'districts' => [
                    8 => 'Babimojsko-Sulechowski',
                    1 => 'Gorzów Wielkopolski',
                    2 => 'Kostrzyn Nad Odrą',
                    5 => 'Międzychód',
                    17 => 'Międzyrzecz',
                    4 => 'Nowa Sól',
                    7 => 'Słubice',
                    9 => 'Strzelce Krajeńskie',
                    10 => 'Sulęcin',
                    11 => 'Szprotawa',
                    12 => 'Zielona Góra',
                    13 => 'Żagań',
                    14 => 'Żary',
                ],
            ],
        ];

        return $structure;
    }
}
