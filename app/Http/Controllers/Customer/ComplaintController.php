<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * Display the Help Center & Customer's Complaints.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $complaints = Complaint::where('user_id', $user->id)
            ->with(['order', 'resolver'])
            ->latest()
            ->paginate(10);

        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(15)
            ->get();

        $stats = [
            'total'    => Complaint::where('user_id', $user->id)->count(),
            'pending'  => Complaint::where('user_id', $user->id)->whereIn('status', ['pending', 'in_review'])->count(),
            'resolved' => Complaint::where('user_id', $user->id)->where('status', 'resolved')->count(),
        ];

        return view('user.complaints.index', compact('complaints', 'recentOrders', 'stats'));
    }

    /**
     * Show form to submit a new complaint.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $selectedOrderId = $request->query('order_id');
        $selectedOrder = null;

        if ($selectedOrderId) {
            $selectedOrder = Order::where('id', $selectedOrderId)
                ->where('user_id', $user->id)
                ->first();
        }

        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        return view('user.complaints.create', compact('recentOrders', 'selectedOrder', 'selectedOrderId'));
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'category'         => 'required|string|in:order_issue,food_quality,rider_behavior,payment_issue,app_issue,other',
            'priority'         => 'required|string|in:low,medium,high,urgent',
            'subject'          => 'required|string|max:150',
            'description'      => 'required|string|max:3000',
            'order_id'         => 'nullable|exists:orders,id',
            'attachment_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Validate order ownership if order_id is present
        if (!empty($validated['order_id'])) {
            $orderBelongsToUser = Order::where('id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->exists();

            if (!$orderBelongsToUser) {
                return back()->withInput()->with('error', 'Selected order does not belong to your account.');
            }
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment_photo')) {
            $attachmentPath = $request->file('attachment_photo')->store('complaints', 'public');
        }

        // Generate unique ticket number (e.g. CMP-20260821-4F9A)
        $ticketNumber = 'CMP-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $complaint = Complaint::create([
            'ticket_number'    => $ticketNumber,
            'user_id'          => $user->id,
            'order_id'         => $validated['order_id'] ?? null,
            'category'         => $validated['category'],
            'priority'         => $validated['priority'],
            'subject'          => trim($validated['subject']),
            'description'      => trim($validated['description']),
            'attachment_photo' => $attachmentPath,
            'status'           => 'pending',
        ]);

        return redirect()->route('customer.complaints.show', $complaint)
            ->with('success', "Your complaint ticket #{$complaint->ticket_number} has been submitted to the Admin team. We will review and update you shortly.");
    }

    /**
     * Display a specific complaint ticket details & resolution history.
     */
    public function show(Complaint $complaint)
    {
        $user = Auth::user();

        if ($complaint->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized access to this complaint ticket.');
        }

        $complaint->load(['order.orderItems.menuItem', 'order.rider', 'resolver']);

        return view('user.complaints.show', compact('complaint'));
    }
}
