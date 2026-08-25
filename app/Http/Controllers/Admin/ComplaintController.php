<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * Display a listing of all customer complaints with filtering.
     */
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'order', 'resolver']);

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by Priority
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Date Range Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search Query (Ticket #, subject, customer name, email, order #)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'priority_high' => $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")->latest(),
            'ticket_asc' => $query->orderBy('ticket_number', 'asc'),
            'ticket_desc' => $query->orderBy('ticket_number', 'desc'),
            default => $query->latest(),
        };

        $complaints = $query->paginate(15)->withQueryString();

        $statsRaw = Complaint::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_review' THEN 1 ELSE 0 END) as in_review,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        $stats = [
            'total'     => (int)($statsRaw->total ?? 0),
            'pending'   => (int)($statsRaw->pending ?? 0),
            'in_review' => (int)($statsRaw->in_review ?? 0),
            'resolved'  => (int)($statsRaw->resolved ?? 0),
            'rejected'  => (int)($statsRaw->rejected ?? 0),
        ];

        return view('admin.complaints.index', compact('complaints', 'stats'));
    }

    /**
     * Display investigation & resolution console for a complaint.
     */
    public function show(Complaint $complaint)
    {
        $complaint->load([
            'user',
            'order.orderItems.menuItem',
            'order.rider',
            'resolver'
        ]);

        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Update the status and admin resolution response.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status'         => 'required|string|in:pending,in_review,resolved,rejected',
            'admin_response' => 'nullable|string|max:4000',
        ]);

        $statusChanged = $complaint->status !== $validated['status'];

        $complaint->status = $validated['status'];
        $complaint->admin_response = $validated['admin_response'] ? trim($validated['admin_response']) : null;

        if (in_array($validated['status'], ['resolved', 'rejected'])) {
            $complaint->resolved_by = Auth::id();
            $complaint->resolved_at = now();
        } elseif ($validated['status'] === 'pending') {
            $complaint->resolved_by = null;
            $complaint->resolved_at = null;
        }

        $complaint->save();

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('success', "Complaint #{$complaint->ticket_number} has been updated successfully.");
    }

    /**
     * Remove the specified complaint from storage.
     */
    public function destroy(Complaint $complaint)
    {
        if ($complaint->attachment_photo && Storage::disk('public')->exists($complaint->attachment_photo)) {
            Storage::disk('public')->delete($complaint->attachment_photo);
        }

        $ticket = $complaint->ticket_number;
        $complaint->delete();

        return redirect()->route('admin.complaints.index')
            ->with('success', "Complaint ticket #{$ticket} has been deleted.");
    }
}
