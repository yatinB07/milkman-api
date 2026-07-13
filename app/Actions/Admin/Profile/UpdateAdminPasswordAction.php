<?php

namespace App\Actions\Admin\Profile;

use App\Data\Admin\AdminPasswordData;
use App\Exceptions\Auth\CurrentPasswordMismatchException;
use App\Models\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Hash;

class UpdateAdminPasswordAction
{
    public function __construct(private readonly AdminRepository $admins) {}

    public function execute(Admin $admin, AdminPasswordData $data): Admin
    {
        if (! Hash::check($data->currentPassword, $admin->getAttribute('password'))) {
            throw new CurrentPasswordMismatchException();
        }

        return $this->admins->update($admin, ['password' => $data->password]);
    }
}
