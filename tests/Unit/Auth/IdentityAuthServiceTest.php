<?php

namespace Tests\Unit\Auth;

use App\Models\Admin;
use App\Repositories\IdentityRepository;
use App\Services\IdentityAuthService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IdentityAuthServiceTest extends TestCase
{
    public function test_authenticate_returns_matching_active_identity(): void
    {
        $identity = new Admin([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ]);

        $identities = $this->createMock(IdentityRepository::class);
        $identities
            ->expects($this->once())
            ->method('findActiveCandidateByEmail')
            ->with('admin', 'admin@example.com')
            ->willReturn($identity);

        $service = new IdentityAuthService($identities);

        $this->assertSame($identity, $service->authenticate('admin', 'admin@example.com', 'secret-password'));
    }
}
