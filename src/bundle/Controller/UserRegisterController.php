<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\Controller;

use Ibexa\Bundle\Core\Controller;
use Ibexa\Bundle\User\Controller\UserRegisterController as BaseUserRegisterController;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\User\View\Register\ConfirmView;
use Ibexa\User\View\Register\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @deprecated Deprecated in 2.5 and will be removed in 3.0. Please use \Ibexa\Bundle\User\Controller\UserRegisterController instead.
 */
class UserRegisterController extends Controller
{
    /** @var BaseUserRegisterController */
    private $userRegisterController;

    /**
     * @param BaseUserRegisterController $userRegisterController
     */
    public function __construct(BaseUserRegisterController $userRegisterController)
    {
        $this->userRegisterController = $userRegisterController;
    }

    /**
     * @param Request $request
     *
     * @return FormView|Response|null
     *
     * @throws InvalidArgumentType
     * @throws UnauthorizedHttpException
     */
    public function registerAction(Request $request)
    {
        return $this->userRegisterController->registerAction($request);
    }

    /**
     * @return ConfirmView
     *
     * @throws InvalidArgumentType
     */
    public function registerConfirmAction()
    {
        return $this->userRegisterController->registerConfirmAction();
    }
}

class_alias(UserRegisterController::class, 'EzSystems\EzPlatformContentFormsBundle\Controller\UserRegisterController');
