<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDashboardWarmBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Mautic\DashboardBundle\DashboardEvents;
use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticDashboardWarmBundle\Integration\DashboardWarmIntegration;

/**
 * Class DashboardSubscriber.
 */
class DashboardSubscriber extends CommonSubscriber
{
    /** @var CoreParametersHelper */
    protected $coreParametersHelper;

    /** @var PathsHelper */
    protected $pathsHelper;

    /** @var IntegrationHelper */
    protected $integrationHelper;

    /**
     * DashboardSubscriber constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param PathsHelper          $pathsHelper
     * @param IntegrationHelper    $integrationHelper
     */
    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        PathsHelper $pathsHelper,
        IntegrationHelper $integrationHelper
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->pathsHelper          = $pathsHelper;
        $this->integrationHelper    = $integrationHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DashboardEvents::DASHBOARD_ON_MODULE_DETAIL_GENERATE => ['onWidgetDetailGenerate', 0],
        ];
    }

    /**
     * Set a widget detail when needed.
     *
     * @param WidgetDetailEvent $event
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        $cache_ttl = DashboardWarmIntegration::CACHE_TTL;
        $share_caches = DashboardWarmIntegration::SHARE_CACHES;
        /** @var \Mautic\PluginBundle\Integration\AbstractIntegration $integration */
        $integration = $this->integrationHelper->getIntegrationObject('DashboardWarm');
        if ($integration) {
            $this->integration = $integration;
            $this->settings    = $integration->getIntegrationSettings()->getFeatureSettings();
            if (isset($this->settings['share_caches'])) {
                $share_caches = $this->settings['share_caches'];
            }
            if (isset($this->settings['cache_ttl'])) {
                $cache_ttl = $this->settings['cache_ttl'];
            }
        }

        $cacheDir = $this->coreParametersHelper->getParameter(
            'cached_data_dir',
            $this->pathsHelper->getSystemPath('cache', true)
        );
        $event->setCacheDir($cacheDir, null);
    }
}
