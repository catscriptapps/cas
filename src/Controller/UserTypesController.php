<?php
// /src/Controller/UserTypesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\UserType;

class UserTypesController
{
    /**
     * Return all user types from the database
     */
    public static function list()
    {
        // This will return: user_type_id and user_type
        return UserType::orderBy('user_type')->get();
    }
}
