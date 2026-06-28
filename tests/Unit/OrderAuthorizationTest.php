<?php

namespace Tests\Unit;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Repositories\OrderRepository;
use App\Domain\Order\Services\OrderService;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'customer_name' => 'Alan Turing',
            'email' => 'alan@example.com',
            'currency' => 'USD',
            'status' => 'confirmed',
            'total' => 100.00,
        ]);

        $this->actingAs($other, 'api');

        $service = new OrderService(new OrderRepository(new Order()));

        $this->expectException(AuthorizationException::class);

        $service->findOrder($order->id);
    }
}
