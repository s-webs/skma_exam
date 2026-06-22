<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission registry grouped by admin section
    |--------------------------------------------------------------------------
    |
    | Used by RoleSeeder, roles UI, and permission management screens.
    |
    */

    'groups' => [
        'users' => [
            'label' => 'users',
            'permissions' => [
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
            ],
        ],
        'roles' => [
            'label' => 'roles',
            'permissions' => [
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
            ],
        ],
        'permissions' => [
            'label' => 'permissions',
            'permissions' => [
                'permissions.view',
                'permissions.create',
                'permissions.delete',
            ],
        ],
        'exam-types' => [
            'label' => 'examTypes',
            'permissions' => [
                'exam-types.view',
                'exam-types.create',
                'exam-types.edit',
                'exam-types.delete',
                'exam-types.manage-access',
            ],
        ],
        'exams' => [
            'label' => 'exams',
            'permissions' => [
                'exams.view',
                'exams.create',
                'exams.edit',
                'exams.delete',
            ],
        ],
        'questions' => [
            'label' => 'questions',
            'permissions' => [
                'questions.view',
                'questions.create',
                'questions.edit',
                'questions.delete',
            ],
        ],
        'applicants' => [
            'label' => 'applicants',
            'permissions' => [
                'applicants.view',
                'applicants.create',
                'applicants.edit',
                'applicants.delete',
            ],
        ],
        'exam-registrations' => [
            'label' => 'examRegistrations',
            'permissions' => [
                'exam-registrations.view',
                'exam-registrations.approve',
                'exam-registrations.unapprove',
                'exam-registrations.edit-date',
            ],
        ],
        'exam-attempts' => [
            'label' => 'examAttempts',
            'permissions' => [
                'exam-attempts.delete',
            ],
        ],
    ],

    'default_roles' => [
        'developer' => '*',

        'ktbo' => [
            'exam-types.view',
            'exam-types.edit',
            'exams.view',
            'exams.create',
            'exams.edit',
            'exams.delete',
            'questions.view',
            'questions.create',
            'questions.edit',
            'questions.delete',
            'applicants.view',
            'applicants.create',
            'applicants.edit',
            'applicants.delete',
            'exam-registrations.view',
            'exam-registrations.approve',
            'exam-registrations.edit-date',
            'exam-attempts.delete',
        ],

        'registrator' => [
            'exam-types.view',
            'exam-registrations.view',
            'exam-registrations.approve',
            'questions.view',
        ],
    ],

];
