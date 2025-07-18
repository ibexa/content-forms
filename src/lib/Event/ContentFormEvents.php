<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

final class ContentFormEvents
{
    /**
     * Base name for Content edit processing events.
     */
    public const string CONTENT_EDIT = 'content.edit';

    /**
     * Triggered when saving a content draft.
     */
    public const string CONTENT_SAVE_DRAFT = 'content.edit.saveDraft';

    /**
     * Triggered when saving a content draft and closing edit.
     */
    public const string CONTENT_SAVE_DRAFT_AND_CLOSE = 'content.edit.saveDraftAndClose';

    /**
     * Triggered when creating a content draft.
     */
    public const string CONTENT_CREATE_DRAFT = 'content.edit.createDraft';

    /**
     * Triggered when publishing a content.
     */
    public const string CONTENT_PUBLISH = 'content.edit.publish';

    /**
     * Triggered when publishing a content and opening new edit.
     */
    public const string CONTENT_PUBLISH_AND_EDIT = 'content.edit.publishAndEdit';

    /**
     * Triggered when canceling a content edition.
     */
    public const string CONTENT_CANCEL = 'content.edit.cancel';

    /**
     * Base name for User edit processing events.
     */
    public const string USER_EDIT = 'user.edit';

    /**
     * Triggered when saving an user.
     */
    public const string USER_UPDATE = 'user.edit.update';

    /**
     * Triggered when creating an user.
     */
    public const string USER_CREATE = 'user.edit.create';

    /**
     * Triggered when canceling a user edition.
     */
    public const string USER_CANCEL = 'user.edit.cancel';

    /**
     * Triggered when resolving Field Type options for content edit form.
     */
    public const string CONTENT_EDIT_FIELD_OPTIONS = 'content.edit.field.options';

    /**
     * Triggered when resolving Field Type options for content create form.
     */
    public const string CONTENT_CREATE_FIELD_OPTIONS = 'content.create.field.options';

    /**
     * Triggered when resolving Field Type options for user edit form.
     */
    public const string USER_EDIT_FIELD_OPTIONS = 'user.edit.field.options';

    /**
     * Triggered when resolving Field Type options for user create form.
     */
    public const string USER_CREATE_FIELD_OPTIONS = 'user.create.field.options';
}
