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
use Mautic\DashboardBundle\Entity\Widget;
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
            DashboardEvents::DASHBOARD_ON_MODULE_DETAIL_GENERATE => ['onWidgetDetailGenerate', 10],
        ];
    }

    /**
     * Set a widget detail when needed.
     *
     * @param WidgetDetailEvent $event
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event)
    {
        $cacheTTL    = DashboardWarmIntegration::CACHE_TTL;
        $shareCaches = DashboardWarmIntegration::SHARE_CACHES;
        /** @var \Mautic\PluginBundle\Integration\AbstractIntegration $integration */
        $integration = $this->integrationHelper->getIntegrationObject('DashboardWarm');
        if ($integration) {
            $settings = $integration->getIntegrationSettings()->getFeatureSettings();
            if (isset($settings['share_caches'])) {
                $shareCaches = $settings['share_caches'];
            }
            if (isset($settings['cache_ttl'])) {
                $cacheTTL = $settings['cache_ttl'];
            }
        }

        // Share cache directories between users if appropriate.
        if ($shareCaches) {
            $cacheDir = $this->coreParametersHelper->getParameter(
                'cached_data_dir',
                $this->pathsHelper->getSystemPath('cache', true)
            );
            $event->setCacheDir($cacheDir, 'shared_dashboard');
        }

        // Override the default cache timeout for the widget if appropriate.
        if ($cacheTTL) {
            /** @var Widget $widget */
            $widget = $event->getWidget();
            if ($widget) {
                $defaultCacheTTL = $widget->getCacheTimeout();
                if ($defaultCacheTTL < $cacheTTL) {
                    $event->setCacheTimeout($cacheTTL);
                }
            }
        }
    }
}
