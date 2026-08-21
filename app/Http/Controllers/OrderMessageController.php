<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderMessageController extends Controller
{
    /**
     * Authorize that the current user is a participant of this order.
     */
    protected function authorizeOrderParticipant(Order $order): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Customer who placed order
        if ($order->user_id === $user->id) {
            return true;
        }

        // Assigned Rider or any active rider in delivery portal
        if ($order->rider_id === $user->id || $user->role === 'rider') {
            return true;
        }

        // Admin
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * List all chat messages for the order (JSON endpoint for real-time polling).
     */
    public function index(Order $order)
    {
        if (!$this->authorizeOrderParticipant($order)) {
            return response()->json(['error' => 'Unauthorized access to order messages.'], 403);
        }

        $userId = Auth::id();

        // Mark unread messages sent by others as read
        OrderMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = OrderMessage::where('order_id', $order->id)
            ->with('sender:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name ?? ($msg->sender_role === 'rider' ? 'Rider' : 'Customer'),
                    'sender_role' => $msg->sender_role,
                    'message' => $msg->message,
                    'time_formatted' => $msg->created_at->format('h:i A'),
                    'created_at' => $msg->created_at->toISOString(),
                    'is_me' => ($msg->sender_id === $userId),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count(),
            'rider' => $order->rider ? [
                'name' => $order->rider->name,
                'phone' => $order->rider->phone_number ?? $order->rider->phone ?? null,
            ] : null,
            'customer' => [
                'name' => $order->user->name ?? 'Customer',
                'phone' => $order->delivery_phone ?? null,
            ],
            'order_status' => $order->status,
        ]);
    }

    /**
     * Store a new chat message for the order.
     */
    public function store(Request $request, Order $order)
    {
        if (!$this->authorizeOrderParticipant($order)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized to send message.'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Determine role
        $role = 'customer';
        if ($user->isAdmin()) {
            $role = 'admin';
        } elseif ($order->rider_id === $user->id || $user->role === 'rider') {
            $role = 'rider';
        }

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'sender_role' => $role,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $user->name,
                    'sender_role' => $message->sender_role,
                    'message' => $message->message,
                    'time_formatted' => $message->created_at->format('h:i A'),
                    'created_at' => $message->created_at->toISOString(),
                    'is_me' => true,
                ],
            ]);
        }

        return back()->with('success', 'Message sent!');
    }

    /**
     * Get real-time notifications for rider across all active orders.
     */
    public function riderNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Active orders assigned to this rider
        $activeOrders = Order::where('rider_id', $user->id)
            ->whereIn('status', ['confirmed', 'preparing', 'delivering'])
            ->with('user:id,name')
            ->get();

        if ($activeOrders->isEmpty()) {
            return response()->json(['success' => true, 'notifications' => [], 'latest_id' => 0]);
        }

        $activeOrderIds = $activeOrders->pluck('id');

        $sinceId = $request->query('since_id');
        $query = OrderMessage::whereIn('order_id', $activeOrderIds)
            ->where('sender_id', '!=', $user->id)
            ->with(['order.user', 'sender:id,name,role'])
            ->orderBy('id', 'asc');

        if ($sinceId && is_numeric($sinceId) && $sinceId > 0) {
            $query->where('id', '>', (int)$sinceId);
        } else {
            // First load: fetch unread or recent in last 30 minutes
            $query->where('is_read', false);
        }

        $messages = $query->limit(20)->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'order_id' => $msg->order_id,
                'order_number' => '#' . $msg->order->order_number,
                'customer_name' => $msg->order->user->name ?? $msg->sender->name ?? 'Customer',
                'customer_phone' => $msg->order->delivery_phone ?? null,
                'delivery_address' => ($msg->order->delivery_township ? $msg->order->delivery_township . ' - ' : '') . $msg->order->delivery_address,
                'sender_name' => $msg->sender->name ?? 'Customer',
                'sender_role' => $msg->sender_role,
                'message' => $msg->message,
                'time_formatted' => $msg->created_at->format('h:i A'),
                'created_at' => $msg->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $messages,
            'latest_id' => $messages->max('id') ?? (int)$sinceId,
        ]);
    }
}
