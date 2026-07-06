<?php

namespace App\Repositories;

use App\Models\Admin;

class AdminRepository
{
    /** @param array<string, mixed> $attributes */
    public function update(Admin $admin, array $attributes): Admin
    {
        $admin->update($attributes);

        return $admin->refresh();
    }
}
