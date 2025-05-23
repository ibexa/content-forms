Feature: User registration form setup
    In order to allow users to create an account on a site
    As a site owner
    I want to expose a user registration form

    Scenario: The user group where registered users are created can be customized
        Given a User Group "TestUserGroup"
        And the following user registration group configuration:
        """
        ibexa:
            system:
                default:
                    user_registration:
                        group_remote_id: <userGroupContentRemoteId>
                site_group:
                    user_registration:
                        group_remote_id: <userGroupContentRemoteId>
        """
