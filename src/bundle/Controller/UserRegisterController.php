<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\Controller;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Bundle\User\Controller\UserRegisterController as BaseUserRegisterController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated Deprecated in 2.5 and will be removed in 3.0. Please use \Ibexa\Bundle\User\Controller\UserRegisterController instead.
 */
class UserRegisterController extends Controller
{
    /** @var \Ibexa\Bundle\User\Controller\UserRegisterController */
    private $userRegisterController;

    /**
     * @param \Ibexa\Bundle\User\Controller\UserRegisterController $userRegisterController
     */
    public function __construct(BaseUserRegisterController $userRegisterController)
    {
        $this->userRegisterController = $userRegisterController;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\User\View\Register\FormView|\Symfony\Component\HttpFoundation\Response|null
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function registerAction(Request $request)
    {
        return $this->userRegisterController->registerAction($request);
    }

    /**
     * @return \Ibexa\User\View\Register\ConfirmView
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function registerConfirmAction()
    {
        return $this->userRegisterController->registerConfirmAction();
    }
}

class_alias(UserRegisterController::class, 'EzSystems\EzPlatformContentFormsBundle\Controller\UserRegisterController');
