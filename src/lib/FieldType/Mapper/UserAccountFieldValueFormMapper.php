<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Data\ContentTranslationData;
use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\Form\Type\FieldType\UserAccountFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\FieldType\User\Value as ApiUserValue;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Maps a user FieldType.
 */
final readonly class UserAccountFieldValueFormMapper implements FieldValueFormMapperInterface
{
    /**
     * Maps Field form to current FieldType based on the configured form type (self::$formType).
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $rootForm = $fieldForm->getRoot()->getRoot();
        $formIntent = $rootForm->getConfig()->getOption('intent');
        $isTranslation = $rootForm->getData() instanceof ContentTranslationData;
        $formBuilder = $formConfig->getFormFactory()->createBuilder()
            ->create('value', UserAccountFieldType::class, [
                'required' => true,
                'label' => $fieldDefinition->getName(),
                'intent' => $formIntent,
            ]);

        if ($isTranslation) {
            $formBuilder->addModelTransformer(
                $this->getModelTransformerForTranslation($fieldDefinition)
            );
        } else {
            $formBuilder->addModelTransformer($this->getModelTransformer());
        }

        $formBuilder->setAutoInitialize(false);

        $fieldForm->add($formBuilder->getForm());
    }

    /**
     * Fake method to set the translation domain for the extractor.
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_forms_content',
            ]);
    }

    public function getModelTransformerForTranslation(FieldDefinition $fieldDefinition): CallbackTransformer
    {
        return new CallbackTransformer(
            static function (ApiUserValue $data): UserAccountFieldData {
                return new UserAccountFieldData($data->login, null, $data->email, $data->enabled);
            },
            static function (UserAccountFieldData $submittedData) use ($fieldDefinition) {
                $userValue = clone $fieldDefinition->defaultValue;
                $userValue->login = $submittedData->username;
                $userValue->email = $submittedData->email;
                $userValue->enabled = $submittedData->enabled;

                return $userValue;
            }
        );
    }

    public function getModelTransformer(): CallbackTransformer
    {
        return new CallbackTransformer(
            static function (ApiUserValue $data): UserAccountFieldData {
                return new UserAccountFieldData($data->login, null, $data->email, $data->enabled);
            },
            static function (UserAccountFieldData $submittedData): UserAccountFieldData {
                return $submittedData;
            }
        );
    }
}
