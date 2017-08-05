<?php

namespace JamboBundle\Controller\Admin;

use DateTime;
use JamboBundle\Entity\Repository\BaseRepositoryInterface;
use JamboBundle\Exception\EditFormException;
use JamboBundle\Model\BandInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract controller
 */
abstract class AbstractController extends Controller
{
    /**
     * Get locale
     *
     * @param string $country country
     *
     * @return string
     */
    protected function getLocale($country)
    {
        $locale = strtolower($country);
        if (!in_array($locale, $this->getParameter('locales'))) {
            $locale = $this->getParameter('locale');
        }

        return $locale;
    }

    /**
     * Get CSV from array
     * 
     * @param array $data data
     *
     * @return string
     */
    protected function getCsvFromArray(array $data)
    {
        $lines = [];
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                if (strpos($cell, '"') !== false || strpos($cell, ' ') !== false || strpos($cell, ',') !== false) {
                    $row[$i] = '"' . str_replace('"', '""', $cell) . '"';
                }
            }
            $lines[] = implode(',', $row);
        }

        return implode("\r\n", $lines);
    }

    /**
     * Get CSV response
     *
     * @param array  $data     data
     * @param string $fileName file name
     *
     * @return Response
     */
    protected function getCsvResponse(array $data, $fileName = 'list')
    {
        $csv = $this->getCsvFromArray($data);

        return new Response($csv, Response::HTTP_OK, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Content-Disposition' => 'attachment; filename=' . $fileName . '_' . date('Y-m-d') . '.csv',
            'Content-Type' => 'text/csv, charset=UTF-8',
            'Expires' => '0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Get criteria
     *
     * @param ParameterBag $query            query
     * @param array        $criteriaSettings criteria settings
     *
     * @return array
     */
    protected function getCriteria(ParameterBag $query, array $criteriaSettings)
    {
        $registrationLists = $this->get('jambo_bundle.registration.lists');
        $criteria = [];

        foreach ($criteriaSettings as $criteriaId => $options) {
            if (!is_array($options)) {
                $options = [
                    'getter' => $options,
                ];
            }
            $getter = $options['getter'];
            $queryId = array_key_exists('queryId', $options) ? $options['queryId'] : $criteriaId;
            $lowestValue = array_key_exists('lowestValue', $options) ? $options['lowestValue'] : 1;

            $item = $query->getInt($queryId, $lowestValue - 1);
            if ($item >= $lowestValue && $registrationLists->$getter($item)) {
                $criteria[$criteriaId] = $item;
                break;
            }
        }

        return $criteria;
    }

    /**
     * Add error message
     *
     * @param FormInterface $form
     */
    protected function addErrorMessage(FormInterface $form)
    {
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addMessage('form.errors', 'error');
        }
    }

    /**
     * Add message
     *
     * @param string $message message
     * @param string $type    type
     *
     * @return self
     */
    protected function addMessage($message, $type = 'message')
    {
        $this->get('session')
            ->getFlashBag()
            ->add($type, $message);

        return $this;
    }

    /**
     * Soft redirect
     *
     * @param string $url URL
     *
     * @return Response
     */
    protected function softRedirect($url)
    {
        $response = new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Headers' => 'X-Location',
            'X-Location' => $url,
        ]);

        return $response;
    }
}
