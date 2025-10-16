<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Log;

/**
 * Order management controller
 *
 * @author igniparra
 */
class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return response()->json([
            'message' => 'Order created successfully. Invoice generation queued.',
            'order' => $order,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function byClient(Request $request, int $id): JsonResponse
    {
        $perPage = (int) ($request->query('per_page', 15));
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15; //Default page size
        }

        $orders = $this->orderService->listOrdersByClient($id, $perPage);

        return response()->json($orders);
    }
}
