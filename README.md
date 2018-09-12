# Mautic Dashboard Warmer [![Latest Stable Version](https://poser.pugx.org/thedmsgroup/mautic-dashboard-warm/v/stable)](https://packagist.org/packages/thedmsgroup/mautic-dashboard-warm-bundle) [![License](https://poser.pugx.org/thedmsgroup/mautic-health-bundle/license)](https://packagist.org/packages/thedmsgroup/mautic-dashboard-warm-bundle) [![Build Status](https://travis-ci.com/TheDMSGroup/mautic-dashboard-warm.svg?branch=master)](https://travis-ci.com/TheDMSGroup/mautic-dashboard-warm)
![dashboard by Icon Island from the Noun Project](./Assets/img/dashboardwarm.png)

Ever felt like it takes forever to log in to Mautic? 
It's likely due to the complex queries involved in your personal dashboard widgets.
As Mautic scales up (or as you add more widgets) the login will feel slower because theres more data to crunch.

This plugin speeds up the dashboard by:
* Setting a higher default cache level for dashboard widgets (1 hour default).
* Sharing the dashboard widget cache between all users that have access to the dashboard (on by default).
* Warming the widget cache by a cron task (must be configured).

## Installation & Usage

Currently being used with Mautic `2.14.x`.
If you have success/issues with other versions please report.

1. Install by running `composer require thedmsgroup/mautic-dashboard-warm-bundle`
   (or by extracting this repo to `/plugins/MauticDashboardWarmBundle`)
2. Go to `/s/plugins/reload`
3. Click "Dashboard Warmer" and configure as desired.

## Cron task

To have this plugin warm the caches you'll also need to create a cron task like so:

*/30 * * * * php /path/to/mautic/app/console mautic:dashboard:warm
