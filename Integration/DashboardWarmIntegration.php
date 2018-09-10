<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDashboardWarmBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class HealthIntegration.
 */
class DashboardWarmIntegration extends AbstractIntegration
{
    /** @var int */
    const CACHE_TTL = 60;

    /** @var int */
    const SHARE_CACHES = 1;

    /**
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'DashboardWarm';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'Dashboard Warmer';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $data
     * @param string                                       $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
            $builder->add(
                'cache_ttl',
                'number',
                [
                    'label' => $this->translator->trans('mautic.dashboard.warm.cache_ttl'),
                    'data'  => !isset($data['cache_ttl']) ? self::CACHE_TTL : (int) $data['cache_ttl'],
                    'attr'  => [
                        'tooltip' => $this->translator->trans('mautic.dashboard.warm.cache_ttl.tooltip'),
                    ],
                ]
            );
            $builder->add(
                'share_caches',
                'yesno_button_group',
                [
                    'label' => $this->translator->trans('mautic.dashboard.warm.share_caches'),
                    'data'  => !isset($data['share_caches']) ? self::SHARE_CACHES : (bool) $data['share_caches'],
                    'attr'  => [
                        'tooltip' => $this->translator->trans('mautic.dashboard.warm.share_caches.tooltip'),
                    ],
                ]
            );
        }
    }

    /**
     * @return array
     */
    public function getSupportedFeatures()
    {
        return [];
    }
}
