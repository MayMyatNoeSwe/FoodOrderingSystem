<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'category',
        'priority',
        'subject',
        'description',
        'attachment_photo',
        'status',
        'admin_response',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * The customer who submitted the complaint.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The related order (if any).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * The administrator who resolved the complaint.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for pending complaints.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in review complaints.
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    /**
     * Scope for resolved complaints.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Category Label with icon helper.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'order_issue'     => '📦 Order Issue (Missing/Wrong items)',
            'food_quality'    => '🍲 Food Quality / Temperature',
            'rider_behavior'  => '🛵 Rider Delivery Issue',
            'payment_issue'   => '💳 Payment / Refund Issue',
            'app_issue'       => '📱 App / Technical Issue',
            default           => '💬 General Inquiry / Other',
        };
    }

    /**
     * Priority styling badge helper.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30',
            'high'   => 'bg-orange-500/15 text-orange-600 dark:text-orange-400 border-orange-500/30',
            'low'    => 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30',
            default  => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30',
        };
    }

    /**
     * Status styling badge helper.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'in_review' => 'bg-purple-500/15 text-purple-600 dark:text-purple-400 border-purple-500/30',
            'resolved'  => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
            'rejected'  => 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/30',
            default     => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30',
        };
    }
}
