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
use MauticPlugin\MauticHealthBundle\Model\HealthModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command : Warms the dashboard caches for all users.
 *
 * php app/console mautic:dashboard:boost
 */
class WarmCommand extends ModeratedCommand
{
    /**
     * Maintenance command line task.
     */
    protected function configure()
    {
        $this->setName('mautic:dashboard:warm')
            ->setDescription('Warm the dashboard widget caches.');
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

        /** @var HealthModel $healthModel */
        $model = $container->get('mautic.dashboardwarm.model.warm');

        $model->warm();

        $this->completeRun();

        return 0;
    }
}
