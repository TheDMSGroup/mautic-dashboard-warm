<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticDashboardWarmBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use MauticPlugin\MauticDashboardWarmBundle\Helper\SettingsHelper;
use MauticPlugin\MauticDashboardWarmBundle\Model\DashboardWarmModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command : Warms the dashboard caches for all users.
 *
 * php app/console mautic:dashboard:boost
 */
class DashboardWarmCommand extends ModeratedCommand
{
    /**
     * Maintenance command line task.
     */
    protected function configure()
    {
        $this->setName('mautic:dashboard:warm')
            ->setDescription('Warm the dashboard widget caches.')
            ->addOption(
                '--limit',
                '-l',
                InputOption::VALUE_OPTIONAL,
                'Maximum number of widgets to warm up for this script execution.',
                50
            );
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        if (!$this->checkRunStatus($input, $output)) {
            return 0;
        }
        $limit = $input->getOption('limit');

        /** @var SettingsHelper $settingsHelper */
        $settingsHelper = $container->get('mautic.dashboardwarm.helper.settings');
        $sharedCache    = (bool) $settingsHelper->getShareCaches();

        /** @var DashboardWarmModel $model */
        $model = $container->get('mautic.dashboardwarm.model.warm');
        $model->warm($limit, $sharedCache);

        $this->completeRun();

        return 0;
    }
}
