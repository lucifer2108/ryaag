<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thelia\Core\HttpKernel\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as BaseNotFountHttpException;

/**
 * Class NotFountHttpException
 * @author Manuel Raynaud <manu@raynaud.io>
 * @deprecated since 2.4 and will be removed in 2.6, please use Symfony\Component\HttpKernel\Exception\NotFoundHttpException
 */
class NotFountHttpException extends BaseNotFountHttpException
{
    protected $adminContext = false;

    public function __construct($message = null, \Exception $previous = null, $code = 0, $adminContext = false)
    {
        $this->adminContext = $adminContext;

        parent::__construct($message, $previous, $code);
    }

    public function isAdminContext()
    {
        return $this->adminContext === true;
    }
}
