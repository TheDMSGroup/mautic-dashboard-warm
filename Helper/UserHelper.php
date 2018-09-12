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

use Mautic\CoreBundle\Helper\UserHelper as OriginalUserHelper;
use Mautic\UserBundle\Entity\User;

/**
 * Class UserHelper.
 *
 * Modifies original so that we can set the User without mock.
 */
class UserHelper extends OriginalUserHelper
{
    /** @var User */
    protected $user = null;

    /**
     * @param bool $nullIfGuest
     *
     * @return User|null
     */
    public function getUser($nullIfGuest = false)
    {
        $user  = $this->user;
        $token = $this->tokenStorage->getToken();

        if (!$user && null !== $token) {
            $user = $token->getUser();
        }

        if (!$user instanceof User) {
            if ($nullIfGuest) {
                return null;
            }

            $user = new User(true);
        }

        return $user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
