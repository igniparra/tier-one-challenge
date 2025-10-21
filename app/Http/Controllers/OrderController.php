<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Auth;
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

    public function store(StoreOrderRequest $request, int $client_id): JsonResponse
    {
        $clients=Auth::user()->clients()->pluck('id')->toArray();

        // Check if the given client_id belongs to this user
        if (!in_array($client_id, $clients)) {
            return response()->json([
                'error' => 'Unauthorized: You do not have access to this client.'
            ], 403);
        }

        $order = $this->orderService->createOrder($request->validated(), $client_id);

        return response()->json([
            'message' => 'Order created successfully. Invoice generation queued.',
            'order' => $order,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        $clients = Auth::user()->clients()->pluck('id')->toArray();

        // Check if the client_id of the order belongs to this user
        if (!in_array($order->client_id, $clients)) {
            return response()->json([
                'error' => 'Unauthorized: You do not have access to this client.'
            ], 403);
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    public function byClient(Request $request, int $id): JsonResponse
    {

        if(!Auth::user()->hasClient($id)){
            return response()->json([
                'error' => 'Unauthorized: You do not have access to this client.'
            ], 403);
        }

        $perPage = (int) ($request->query('per_page', 15));
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 15; //Default page size
        }

        $orders = $this->orderService->listOrdersByClient($id, $perPage);
        

        return response()->json($orders);
    }
}
