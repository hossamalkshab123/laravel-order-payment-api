<?php

namespace Tests\Feature;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_create_an_order(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/orders', [
            'customer_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'currency' => 'USD',
            'items' => [
                ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 999.99],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.customer_name', 'Jane Doe')
            ->assertJsonPath('data.total', 999.99);
    }

    public function test_a_payment_can_mark_an_order_as_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'currency' => 'USD',
            'status' => 'confirmed',
            'total' => 25.00,
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id' => $order->id,
            'amount' => 25.00,
            'currency' => 'USD',
            'payment_method' => 'credit_card',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'successful');

        $this->assertSame('successful', $order->fresh()->payments()->latest()->first()->status);
    }

    public function test_order_status_can_move_only_through_the_allowed_flow(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::PENDING->value,
            'total' => 50.00,
        ]);

        $response = $this->actingAs($user, 'api')->putJson('/api/orders/' . $order->id, [
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::CONFIRMED->value);
    }

    public function test_payment_for_a_non_confirmed_order_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::PENDING->value,
            'total' => 10.00,
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id' => $order->id,
            'amount' => 10.00,
            'currency' => 'USD',
            'payment_method' => 'paypal',
        ]);

        $response->assertStatus(400);
    }

    public function test_order_update_only_allows_status_modification(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Alan Turing',
            'email' => 'alan@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::CONFIRMED->value,
            'total' => 20.00,
        ]);

        // Cannot update customer details for confirmed orders
        $response = $this->actingAs($user, 'api')->putJson('/api/orders/' . $order->id, [
            'customer_name' => 'Changed Name',
        ]);

        $response->assertStatus(400);
        $this->assertSame('Alan Turing', $order->fresh()->customer_name);

        // Can update customer details for pending orders
        $pendingOrder = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'email' => 'john@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::PENDING->value,
            'total' => 30.00,
        ]);

        $response = $this->actingAs($user, 'api')->putJson('/api/orders/' . $pendingOrder->id, [
            'customer_name' => 'Jane Doe',
        ]);

        $response->assertStatus(200);
        $this->assertSame('Jane Doe', $pendingOrder->fresh()->customer_name);
    }

    public function test_cannot_delete_order_with_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Charles Babbage',
            'email' => 'charles@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::CONFIRMED->value,
            'total' => 100.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_id' => uniqid('pay_'),
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => 'successful',
            'payment_method' => 'credit_card',
        ]);

        $response = $this->actingAs($user, 'api')->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(400);
        $this->assertNull($order->fresh()->deleted_at);
    }

    public function test_invalid_payment_gateway_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Donald Knuth',
            'email' => 'donald@example.com',
            'currency' => 'USD',
            'status' => OrderStatus::CONFIRMED->value,
            'total' => 50.00,
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id' => $order->id,
            'amount' => 50.00,
            'currency' => 'USD',
            'payment_method' => 'bitcoin',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_access_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/me');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_missing_token_returns_unauthenticated_message(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
