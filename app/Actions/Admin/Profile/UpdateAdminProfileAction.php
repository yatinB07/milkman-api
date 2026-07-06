<?php

namespace App\Actions\Admin\Profile;

use App\Data\Admin\AdminProfileData;
use App\Models\Admin;
use App\Repositories\AdminRepository;

class UpdateAdminProfileAction
{
    public function __construct(private readonly AdminRepository $admins) {}

    public function execute(Admin $admin, AdminProfileData $data): Admin
    {
        return $this->admins->update($admin, $data->toArray());
    }
}
