<?php

namespace App\Actions\Admin\Profile;

use App\Models\Admin;

class ShowAdminProfileAction
{
    public function execute(Admin $admin): Admin
    {
        return $admin;
    }
}
