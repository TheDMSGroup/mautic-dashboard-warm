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
use MauticPlugin\MauticDashboardWarmBundle\Helper\SettingsHelper;

/**
 * Class DashboardSubscriber.
 */
class DashboardSubscriber extends CommonSubscriber
{
    /** @var CoreParametersHelper */
    protected $coreParametersHelper;

    /** @var PathsHelper */
    protected $pathsHelper;

    /** @var SettingsHelper */
    protected $settingsHelper;

    /**
     * DashboardSubscriber constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param PathsHelper          $pathsHelper
     * @param SettingsHelper       $settingsHelper
     */
    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        PathsHelper $pathsHelper,
        SettingsHelper $settingsHelper
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->pathsHelper          = $pathsHelper;
        $this->settingsHelper       = $settingsHelper;
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
        // Share cache directories between users if appropriate.
        if ($this->settingsHelper->getShareCaches()) {
            $cacheDir = $this->coreParametersHelper->getParameter(
                'cached_data_dir',
                $this->pathsHelper->getSystemPath('cache', true)
            );
            $event->setCacheDir($cacheDir, 'shared_dashboard');
        }

        // Override the default cache timeout for the widget if appropriate.
        if ($this->settingsHelper->getCacheTTL()) {
            /** @var Widget $widget */
            $widget = $event->getWidget();
            if ($widget) {
                $defaultCacheTTL = $widget->getCacheTimeout();
                if ($defaultCacheTTL < $this->settingsHelper->getCacheTTL()) {
                    $event->setCacheTimeout($this->settingsHelper->getCacheTTL());
                }
            }
        }
    }
}
