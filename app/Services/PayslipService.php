<?php

namespace App\Services;

use App\Mail\CustomerOrderPayslipMail;
use App\Mail\RiderOrderPayslipMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PayslipService
{
    /**
     * Send official Foodpanda-style payslips to both customer and rider upon order acceptance.
     */
    public static function sendOrderAcceptedPayslips(Order $order): array
    {
        $order->loadMissing(['user', 'rider', 'orderItems.menuItem']);

        $customerSent = false;
        $riderSent = false;
        $messages = [];

        // 1. Send Customer Payslip & Tax Invoice Email
        if ($order->user && !empty($order->user->email)) {
            try {
                Mail::to($order->user->email)->send(new CustomerOrderPayslipMail($order));
                $customerSent = true;
                $messages[] = "Customer payslip emailed to {$order->user->email}";
                Log::info("PayslipService: Customer payslip sent for Order #{$order->order_number} to {$order->user->email}");
            } catch (\Throwable $e) {
                Log::error("PayslipService: Failed sending customer payslip for Order #{$order->order_number}: " . $e->getMessage());
                $messages[] = "Customer email failed: " . $e->getMessage();
            }
        } else {
            $messages[] = "Customer email not available";
        }

        // 2. Send Rider Delivery Slip Email (if rider is assigned)
        $rider = $order->rider;
        if ($rider && !empty($rider->email)) {
            try {
                Mail::to($rider->email)->send(new RiderOrderPayslipMail($order, $rider));
                $riderSent = true;
                $messages[] = "Rider delivery slip emailed to {$rider->email}";
                Log::info("PayslipService: Rider delivery slip sent for Order #{$order->order_number} to {$rider->email}");
            } catch (\Throwable $e) {
                Log::error("PayslipService: Failed sending rider delivery slip for Order #{$order->order_number}: " . $e->getMessage());
                $messages[] = "Rider email failed: " . $e->getMessage();
            }
        } else {
            $messages[] = "Order waiting for rider assignment (rider will receive payslip upon pickup/assignment)";
        }

        return [
            'customer_sent' => $customerSent,
            'rider_sent' => $riderSent,
            'messages' => $messages,
        ];
    }

    /**
     * Send customer payslip email specifically.
     */
    public static function sendCustomerPayslip(Order $order): bool
    {
        $order->loadMissing(['user', 'rider', 'orderItems.menuItem']);

        if (!$order->user || empty($order->user->email)) {
            return false;
        }

        try {
            Mail::to($order->user->email)->send(new CustomerOrderPayslipMail($order));
            Log::info("PayslipService: Customer payslip sent for Order #{$order->order_number} to {$order->user->email}");
            return true;
        } catch (\Throwable $e) {
            Log::error("PayslipService: Customer payslip failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rider delivery & payslip slip email specifically.
     */
    public static function sendRiderPayslip(Order $order, ?User $rider = null): bool
    {
        $order->loadMissing(['user', 'rider', 'orderItems.menuItem']);
        $targetRider = $rider ?? $order->rider;

        if (!$targetRider || empty($targetRider->email)) {
            return false;
        }

        try {
            Mail::to($targetRider->email)->send(new RiderOrderPayslipMail($order, $targetRider));
            Log::info("PayslipService: Rider delivery slip sent for Order #{$order->order_number} to {$targetRider->email}");
            return true;
        } catch (\Throwable $e) {
            Log::error("PayslipService: Rider delivery slip failed: " . $e->getMessage());
            return false;
        }
    }
}
