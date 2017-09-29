<?php

namespace JamboBundle\Form\Type;

use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Parser;

/*
 * Form type
 */
abstract class AbstractType extends BaseAbstractType
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $locale;

    /** @var RegistrationLists */
    protected $registrationLists;

    /** @var array */
    protected $validation = [];

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator        translator
     * @param RegistrationLists   $registrationLists registration lists
     */
    public function __construct(TranslatorInterface $translator, RegistrationLists $registrationLists)
    {
        $this->translator = $translator;
        $this->locale = $translator->getLocale();
        $this->registrationLists = $registrationLists;
    }

    /**
     * Load validation
     *
     * @param string $entityName entity name
     */
    protected function loadValidation($entityName)
    {
        $entity = ucfirst($entityName);
        $entityPath = 'JamboBundle\\Entity\\' . $entity;
        $configFileUrl = sprintf('Resources/config/validation/%s.yml', $entity);

        $yamlParser = new Parser();
        $configData = $yamlParser->parse(file_get_contents(__DIR__ . '/../../' . $configFileUrl));
        if (is_array($configData) && array_key_exists($entityPath, $configData) &&
            array_key_exists('properties', $configData[$entityPath])
        ) {
            $this->validation = (array) $configData[$entityPath]['properties'];
        }
    }

    /**
     * Merge options
     *
     * @param string $child   child
     * @param array  $options options
     *
     * @return array
     */
    protected function mergeOptions($child, array $options)
    {
        if (array_key_exists($child, $this->validation)) {
            $maxLength = null;
            $pattern = null;
            foreach ($this->validation[$child] as $constraint) {
                if (array_key_exists('Length', $constraint) && array_key_exists('max', $constraint['Length'])) {
                    $currentMaxLength = $constraint['Length']['max'];
                    $maxLength = isset($maxLength) ? min($maxLength, $currentMaxLength) : $currentMaxLength;
                }
                if (array_key_exists('Regex', $constraint) && array_key_exists('pattern', $constraint['Regex'])) {
                    $pattern = $constraint['Regex']['pattern'];
                }
            }

            $attributes = [];
            if (isset($maxLength)) {
                $attributes['maxlength'] = $maxLength;
            }
            if (isset($pattern)) {
                $attributes['pattern'] = rtrim(ltrim(trim($pattern, '/#'), '^'), '$');
            }

            if (count($attributes) > 0) {
                $options['attr'] =
                    array_key_exists('attr', $options) ? array_merge($attributes, $options['attr']) : $attributes;
            }
        }

        return $options;
    }
}
