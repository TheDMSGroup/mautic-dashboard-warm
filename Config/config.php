<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Dashboard Warm',
    'description' => 'Improves the performance of the dashboard by sharing/extending/warming caches.',
    'version'     => '1.0',
    'author'      => 'Mautic',

    'services' => [
        'models'  => [
            'mautic.dashboardwarm.model.warm' => [
                'class'     => 'MauticPlugin\MauticDashboardWarmBundle\Model\DashboardWarmModel',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.helper.integration',
                    'mautic.dashboard.model.dashboard',
                    'mautic.user.model.user',
                    'monolog.logger.mautic',
                    'mautic.dashboardwarm.helper.user',
                ],
            ],
        ],
        'events'  => [
            'mautic.dashboardwarm.subscriber' => [
                'class'     => 'MauticPlugin\MauticDashboardWarmBundle\EventListener\DashboardSubscriber',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.paths',
                    'mautic.dashboardwarm.helper.settings',
                ],
            ],
        ],
        'helpers' => [
            'mautic.dashboardwarm.helper.user'     => [
                'class'     => 'MauticPlugin\MauticDashboardWarmBundle\Helper\UserHelper',
                'arguments' => [
                    'security.token_storage',
                ],
            ],
            'mautic.dashboardwarm.helper.settings' => [
                'class'     => 'MauticPlugin\MauticDashboardWarmBundle\Helper\SettingsHelper',
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
        ],
    ],
];
