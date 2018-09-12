<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDashboardWarmBundle\Helper;

use Mautic\PluginBundle\Helper\IntegrationHelper;

/**
 * Class SettingsHelper.
 */
class SettingsHelper
{
    /** @var int */
    const CACHE_TTL = 60;

    /** @var int */
    const SHARE_CACHES = 1;

    /** @var IntegrationHelper */
    protected $integrationHelper;

    /** @var bool */
    protected $shareCaches;

    /** @var int */
    protected $cacheTTL;

    /**
     * DashboardWarmHelper constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
        $this->cacheTTL          = self::CACHE_TTL;
        $this->shareCaches       = self::SHARE_CACHES;
        /** @var \Mautic\PluginBundle\Integration\AbstractIntegration $integration */
        $integration = $this->integrationHelper->getIntegrationObject('DashboardWarm');
        if ($integration) {
            $settings = $integration->getIntegrationSettings()->getFeatureSettings();
            if (isset($settings['share_caches'])) {
                $this->shareCaches = $settings['share_caches'];
            }
            if (isset($settings['cache_ttl'])) {
                $this->cacheTTL = $settings['cache_ttl'];
            }
        }
    }

    /**
     * @return int
     */
    public function getCacheTTL()
    {
        return $this->cacheTTL;
    }

    /**
     * @return bool
     */
    public function getShareCaches()
    {
        return $this->shareCaches;
    }
}
