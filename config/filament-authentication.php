<?php

return [
    'models' => [
        'User' => \App\Models\User::class,
        'Role' => \Spatie\Permission\Models\Role::class,
        'Permission' => \Spatie\Permission\Models\Permission::class,
    ],
    'resources' => [
        'UserResource' => App\Filament\Resources\UserResource::class,
        'RoleResource' => App\Filament\Resources\RoleResource::class,
        'PermissionResource' => App\Filament\Resources\PermissionResource::class,

    ],
    'pages' => [
        'Profile' => App\Filament\FilamentAuthentication\Pages\Profile::class,
    ],
    'Widgets' => [
        'LatestUsers' => [
            'enabled' => false,
            'limit' => 5,
            'sort' => 0,
            'paginate' => false,
        ],
    ],
    'preload_roles' => true,
    'impersonate' => [
        'enabled' => false,
        'guard' => 'web',
        'redirect' => '/',
    ],
];
