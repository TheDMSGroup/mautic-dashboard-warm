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
use Mautic\DashboardBundle\Model\DashboardModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\UserBundle\Model\UserModel;
use MauticPlugin\MauticDashboardWarmBundle\Helper\UserHelper;
use Monolog\Logger;

/**
 * Class WarmModel.
 */
class DashboardWarmModel
{
    /** @var EntityManager */
    protected $em;

    /** @var IntegrationHelper */
    protected $integrationHelper;

    /** @var array */
    protected $settings;

    /** @var DashboardModel */
    protected $dashboardModel;

    /** @var UserModel */
    protected $userModel;

    /** @var Logger */
    protected $logger;

    /** @var UserHelper */
    protected $userHelper;

    /** @var array These widgets will be excluded from the warming process. */
    protected $excludedWidgetTypes = [
        'recent.activity', // Causes an exception due to icons depending on session request.
        'leads.added.in.time', // Creates exception due to 'campaign:campaigns:viewother' permission check
    ];

    /** @var array */
    protected $widgetsBuilt = [];

    /**
     * DashboardWarmModel constructor.
     *
     * @param EntityManager     $em
     * @param IntegrationHelper $integrationHelper
     * @param DashboardModel    $dashboardModel
     * @param UserModel         $userModel
     * @param Logger            $logger
     * @param UserHelper        $userHelper
     */
    public function __construct(
        EntityManager $em,
        IntegrationHelper $integrationHelper,
        DashboardModel $dashboardModel,
        UserModel $userModel,
        Logger $logger,
        UserHelper $userHelper
    ) {
        $this->em                = $em;
        $this->integrationHelper = $integrationHelper;
        $this->dashboardModel    = $dashboardModel;
        $this->userModel         = $userModel;
        $this->logger            = $logger;
        $this->userHelper        = $userHelper;
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
     *
     * @param int  $limit
     * @param bool $sharedCache
     *
     * @return null|void
     */
    public function warm($limit = 50, $sharedCache = false)
    {
        // Find appropriate users.
        $userRepo   = $this->userModel->getRepository();
        $userPrefix = $userRepo->getTableAlias();
        if (!empty($userPrefix)) {
            $userPrefix .= '.';
        }
        $users = $this->userModel->getEntities(
            [
                'limit'            => $limit,
                'filter'           => [
                    'force' => [
                        [
                            'column' => $userPrefix.'lastLogin',
                            'expr'   => 'isNotNull',
                        ],
                    ],
                ],
                'orderBy'          => $userPrefix.'lastLogin',
                'orderByDir'       => 'DESC',
                'ignore_paginator' => true,
            ]
        );
        if (!$users) {
            return;
        }

        // Find appropriate widgets for those users.
        $widgetFilter = $this->dashboardModel->getDefaultFilter();
        $widgetRepo   = $this->dashboardModel->getRepository();
        $widgetPrefix = $widgetRepo->getTableAlias();
        if (!empty($widgetPrefix)) {
            $widgetPrefix .= '.';
        }
        $i = 0;
        foreach ($users as $user) {
            $widgets = $this->dashboardModel->getEntities(
                [
                    'limit'            => ($limit - $i),
                    'filter'           => [
                        'force' => [
                            [
                                'column' => $widgetPrefix.'isPublished',
                                'expr'   => 'eq',
                                'value'  => 1,
                            ],
                            [
                                'column' => $widgetPrefix.'type',
                                'expr'   => 'notIn',
                                'value'  => $this->excludedWidgetTypes,
                            ],
                            [
                                'column' => $widgetPrefix.'createdBy',
                                'expr'   => 'eq',
                                'value'  => $user->getId(),
                            ],
                        ],
                    ],
                    'orderBy'          => $widgetPrefix.'ordering',
                    'orderByDir'       => 'ASC',
                    'ignore_paginator' => true,
                ]
            );
            if (!$widgets) {
                continue;
            }
            // Generate widget content (including cache).
            $widgetTypes = [];
            foreach ($widgets as $id => $widget) {
                $key = $widget->getType().'_'.md5(json_encode($widget->getParams()));
                // Do not process the same widget and params twice.
                if ($sharedCache && isset($this->widgetsBuilt[$key])) {
                    unset($widgets[$id]);
                    continue;
                }
                $widgetTypes[]            = $widget->getType();
                $this->widgetsBuilt[$key] = $widget->getType();
            }
            if (!$widgetTypes) {
                continue;
            }
            $this->logger->info(
                'Warming dashboard widget cache for '.$user->getFirstName().' '.$user->getLastName().
                ' Including: '.implode(', ', $widgetTypes)
            );
            $i += count($widgets);
            try {
                if ($sharedCache) {
                    $user->getRole()->setIsAdmin(1);
                }
                $this->userHelper->setUser($user);
                $this->dashboardModel->setUserHelper($this->userHelper);
                $this->dashboardModel->populateWidgetsContent($widgets, $widgetFilter);
            } catch (\Exception $e) {
                $this->logger->error(
                    'Unable to warm dashboard widget cache for '.$user->getFirstName().' '.$user->getLastName().
                    ' Including: '.implode(', ', $widgetTypes).' Error: '.$e->getMessage()
                );
            }

            // Do not process more widgets than the batch limit.
            if ($i >= $limit) {
                $this->logger->debug('Batch limit hit, stopping dashboard warm run.');
                break;
            }
        }
    }
}
