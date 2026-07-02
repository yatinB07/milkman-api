<?php

namespace Tests\Unit\Foundation;

use App\Enums\IdentityType;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Events\OrderCompleted;
use App\Events\OrderPlaced;
use App\Events\PayoutRequested;
use App\Jobs\DispatchDomainNotificationJob;
use App\Models\Admin;
use App\Models\Order;
use App\Models\PayoutRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class ArchitectureFoundationTest extends TestCase
{
    public function test_identity_type_enum_maps_to_identity_models(): void
    {
        $this->assertSame(Admin::class, IdentityType::Admin->modelClass());
        $this->assertSame('customer', IdentityType::Customer->value);
    }

    public function test_domain_status_enums_define_shared_status_values(): void
    {
        $this->assertSame('completed', OrderStatus::Completed->value);
        $this->assertSame('approved', PayoutStatus::Approved->value);
    }

    public function test_domain_events_carry_their_domain_models(): void
    {
        $order = new Order(['status' => OrderStatus::Pending->value]);
        $payout = new PayoutRequest(['status' => PayoutStatus::Pending->value]);

        $this->assertSame($order, new OrderPlaced($order)->order);
        $this->assertSame($order, new OrderCompleted($order)->order);
        $this->assertSame($payout, new PayoutRequested($payout)->payout);
    }

    public function test_domain_notification_job_is_queueable(): void
    {
        $job = new DispatchDomainNotificationJob('customer', 10, 'Title', 'Body');

        $this->assertInstanceOf(ShouldQueue::class, $job);
        $this->assertSame('customer', $job->channel);
        $this->assertSame(10, $job->recipientId);
    }
}
