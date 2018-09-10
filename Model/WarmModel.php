<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDashboardWarmBundle\Model;

use Doctrine\ORM\EntityManager;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticHealthBundle\Integration\HealthIntegration;

/**
 * Class HealthModel.
 */
class WarmModel
{
    /** @var EntityManager */
    protected $em;

    /** @var IntegrationHelper */
    protected $integrationHelper;

    /** @var array */
    protected $settings;

    /** @var HealthIntegration */
    protected $integration;

    /**
     * HealthModel constructor.
     *
     * @param EntityManager     $em
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(
        EntityManager $em,
        IntegrationHelper $integrationHelper
    ) {
        $this->em                = $em;
        $this->integrationHelper = $integrationHelper;

        /** @var \Mautic\PluginBundle\Integration\AbstractIntegration $integration */
        $integration = $this->integrationHelper->getIntegrationObject('Health');
        if ($integration) {
            $this->integration = $integration;
            $this->settings    = $integration->getIntegrationSettings()->getFeatureSettings();
        }
    }

    /**
     * @param $settings
     */
    public function setSettings($settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Warm the caches of dashboard widgets.
     */
    public function warm()
    {
        // @todo - Get a batch of widgets to be rendered...

        // Join users, order by the user most recent log in date.
    }
}
