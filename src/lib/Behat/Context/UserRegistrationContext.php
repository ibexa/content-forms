<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Ibexa\Bundle\Core\Features\Context\YamlConfigurationContext;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;
use Ibexa\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Core\Repository\Values\User\UserReference;
use PHPUnit\Framework\Assert as Assertion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class UserRegistrationContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Regex matching the way the Twig template name is inserted in debug mode.
     */
    public const string TWIG_DEBUG_STOP_REGEX = '<!-- STOP .*%s.* -->';

    private static string $password = 'PassWord42';

    private static string $language = 'eng-GB';

    private static int $groupId = 4;

    private ?string $registrationUsername = null;

    /**
     * Used to cover registration group customization.
     */
    private UserGroup $customUserGroup;

    private YamlConfigurationContext $yamlConfigurationContext;

    public function __construct(
        readonly PermissionResolver $permissionResolver,
        private readonly RoleService $roleService,
        private readonly UserService $userService,
        private readonly ContentTypeService $contentTypeService
    ) {
        $permissionResolver->setCurrentUserReference(new UserReference(14));
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->yamlConfigurationContext = $scope->getEnvironment()->getContext(YamlConfigurationContext::class);
    }

    /**
     * @Given /^I do not have the user\/register policy$/
     */
    public function loginAsUserWithoutRegisterPolicy(): void
    {
        $role = $this->createRegistrationRole(false);
        $user = $this->createUserWithRole($role);
        $this->loginAs($user);
    }

    /**
     * @Given /^I do have the user\/register policy$/
     */
    public function loginAsUserWithUserRegisterPolicy(): void
    {
        $role = $this->createRegistrationRole(true);
        $user = $this->createUserWithRole($role);
        $this->loginAs($user);
    }

    private function createUserWithRole(Role $role): User
    {
        $username = uniqid($role->identifier, true);
        $createStruct = $this->userService->newUserCreateStruct(
            $username,
            substr($username, 0, 64) . '@example.com',
            self::$password,
            'eng-GB'
        );
        $createStruct->setField('first_name', $username);
        $createStruct->setField('last_name', 'The first');
        $user = $this->userService->createUser($createStruct, [$this->userService->loadUserGroup(self::$groupId)]);

        $this->roleService->assignRoleToUser($role, $user);

        return $user;
    }

    private function createRegistrationRole(bool $withUserRegisterPolicy = true): Role
    {
        $roleIdentifier = uniqid(
            'anonymous_role_' . ($withUserRegisterPolicy ? 'with' : 'without') . '_register',
            true
        );

        $roleCreateStruct = new RoleCreateStruct(['identifier' => $roleIdentifier]);

        $policiesSet = ['user/login', 'content/read'];
        foreach ($policiesSet as $policy) {
            [$module, $function] = explode('/', $policy);
            $roleCreateStruct->addPolicy($this->roleService->newPolicyCreateStruct($module, $function));
        }

        if ($withUserRegisterPolicy === true) {
            $roleCreateStruct->addPolicy($this->roleService->newPolicyCreateStruct('user', 'register'));
        }

        $this->roleService->publishRoleDraft(
            $this->roleService->createRole($roleCreateStruct)
        );

        return $this->roleService->loadRoleByIdentifier($roleIdentifier);
    }

    /**
     * @Then /^I see an error message saying that I can not register$/
     */
    public function iSeeAnErrorMessageSayingThatICanNotRegister(): void
    {
        $this->assertSession()->pageTextContains('You are not allowed to register a new account');
    }

    /**
     * @throws \Behat\Mink\Exception\ExpectationException
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    private function loginAs(User $user): void
    {
        $this->visitPath('/login');
        $page = $this->getSession()->getPage();
        $page->fillField('_username', $user->login);
        $page->fillField('_password', self::$password);
        $this->getSession()->getPage()->find('css', 'form')->submit();
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then /^I can see the registration form$/
     */
    public function iCanSeeTheRegistrationForm(): void
    {
        $this->assertSession()->pageTextNotContains('You are not allowed to register a new account');
        $this->assertSession()->elementExists('css', 'form[name=ezplatform_content_forms_user_register]');
    }

    /**
     * @Given /^it matches the structure of the configured registration user content type$/
     */
    public function itMatchesTheStructureOfTheConfiguredRegistrationUserContentType(): void
    {
        $userContentType = $this->contentTypeService->loadContentTypeByIdentifier('user');
        foreach ($userContentType->getFieldDefinitions() as $fieldDefinition) {
            $this->assertSession()->elementExists(
                'css',
                sprintf(
                    '#ezplatform_content_forms_user_register_fieldsData_%s',
                    $fieldDefinition->identifier
                )
            );
            /** @todo It should also check if there is corresponding input created once all types are implemented */
        }
    }

    /**
     * @Given /^it has a register button$/
     */
    public function itHasARegisterButton(): void
    {
        $this->assertSession()->elementExists(
            'css',
            'form[name=ezplatform_content_forms_user_register] button[type=submit]'
        );
    }

    /**
     * @When /^I fill in the form with valid values$/
     */
    public function iFillInTheFormWithValidValues(): void
    {
        $page = $this->getSession()->getPage();

        $this->registrationUsername = uniqid('registration_username_', true);

        $page->fillField('ezplatform_content_forms_user_register[fieldsData][first_name][value]', 'firstname');
        $page->fillField('ezplatform_content_forms_user_register[fieldsData][last_name][value]', 'firstname');
        $page->fillField('ezplatform_content_forms_user_register[fieldsData][user_account][value][username]', $this->registrationUsername);
        $page->fillField('ezplatform_content_forms_user_register[fieldsData][user_account][value][email]', $this->registrationUsername . '@example.com');
        $page->fillField('ezplatform_content_forms_user_register[fieldsData][user_account][value][password][first]', self::$password);
        $page->fillField('ezplatform_content_forms_user_register[fieldsData][user_account][value][password][second]', self::$password);
    }

    /**
     * @When /^I click on the register button$/
     */
    public function iClickOnTheRegisterButton(): void
    {
        $this->getSession()->getPage()->pressButton('ezplatform_content_forms_user_register[register]');
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then /^I am on the registration confirmation page$/
     */
    public function iAmOnTheRegistrationConfirmationPage(): void
    {
        $this->assertSession()->addressEquals('/register-confirm');
    }

    /**
     * @Given /^I see a registration confirmation message$/
     */
    public function iSeeARegistrationConfirmationMessage(): void
    {
        $this->assertSession()->pageTextContains('Your account has been created');
    }

    /**
     * @Given /^the user account has been created$/
     */
    public function theUserAccountHasBeenCreated(): void
    {
        if (null === $this->registrationUsername) {
            throw new \LogicException('You need to call iFillInTheFormWithValidValues before this step');
        }

        $this->userService->loadUserByLogin($this->registrationUsername);
    }

    /**
     * @Given a User Group :userGroupName
     */
    public function createUserGroup(string $userGroupName): void
    {
        $groupCreateStruct = $this->userService->newUserGroupCreateStruct(self::$language);
        $groupCreateStruct->setField('name', $userGroupName);
        $this->customUserGroup = $this->userService->createUserGroup(
            $groupCreateStruct,
            $this->userService->loadUserGroup(self::$groupId)
        );
    }

    /**
     * @Given /^the following user registration group configuration:$/
     */
    public function addUserRegistrationConfiguration(PyStringNode $extraConfigurationString): void
    {
        $this->yamlConfigurationContext->addConfiguration(Yaml::parse(
            str_replace(
                '<userGroupContentRemoteId>',
                $this->customUserGroup->getContentInfo()->remoteId,
                $extraConfigurationString->getRaw()
            )
        ));
    }

    /**
     * @When /^I register a user account$/
     */
    public function iRegisterAUserAccount(): void
    {
        $this->loginAsUserWithUserRegisterPolicy();
        $this->visitPath('/register');
        $this->assertSession()->statusCodeEquals(200);
        $this->iFillInTheFormWithValidValues();
        $this->iClickOnTheRegisterButton();
        $this->iAmOnTheRegistrationConfirmationPage();
        $this->iSeeARegistrationConfirmationMessage();
    }

    /**
     * @Then /^the user is created in :userGroupName user group$/
     */
    public function theUserIsCreatedInThisUserGroup(string $userGroupName): void
    {
        if (null === $this->registrationUsername) {
            throw new \LogicException('You need to call iFillInTheFormWithValidValues before this step');
        }

        $user = $this->userService->loadUserByLogin($this->registrationUsername);
        $userGroups = $this->userService->loadUserGroupsOfUser($user);

        Assertion::assertEquals(
            $userGroupName,
            $userGroups[0]->getName()
        );
    }

    /**
     * @Given /^the following user registration templates configuration:$/
     */
    public function addRegistrationTemplatesConfiguration(PyStringNode $pyStringNode): void
    {
        $this->yamlConfigurationContext->addConfiguration(Yaml::parse((string) $pyStringNode));
    }

    /**
     * @Given /^the following template in "([^"]*)":$/
     */
    public function createTemplateAt(string $path, PyStringNode $contents): void
    {
        $fs = new Filesystem();
        $fs->mkdir(dirname($path));
        $fs->dumpFile($path, (string) $contents);
    }

    /**
     * @Then /^the confirmation page is rendered using the "([^"]*)" template$/
     * @Then /^the form is rendered using the "([^"]*)" template$/
     *
     *        The template path to look for.
     *        If relative to app/Resources/views (example: user/register.html.twig),
     *        the path is checked with the :path:file.html.twig syntax as well.
     */
    public function thePageIsRenderedUsingTheTemplateConfiguredIn(string $template): void
    {
        $html = $this->getSession()->getPage()->getOuterHtml();
        $searchedPattern = sprintf(self::TWIG_DEBUG_STOP_REGEX, preg_quote($template));
        $found = preg_match($searchedPattern, $html) === 1;

        if (!$found && !str_contains($template, ':')) {
            $alternativeTemplate = sprintf(
                ':%s:%s',
                dirname($template),
                basename($template)
            );
            $searchedPattern = sprintf(self::TWIG_DEBUG_STOP_REGEX, preg_quote($alternativeTemplate));
            $found = preg_match($searchedPattern, $html) === 1;
        }

        Assertion::assertTrue(
            $found,
            "Couldn't find $template " .
            (isset($alternativeTemplate) ? "nor $alternativeTemplate " : ' ') .
            "in HTML:\n\n$html"
        );
    }
}
