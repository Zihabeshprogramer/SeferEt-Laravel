<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\User;
use App\Notifications\AdApprovedNotification;
use App\Notifications\AdRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AdManagementController extends Controller
{
    /**
     * Display list of ads with filtering
     */
    public function index(Request $request): View
    {
        $query = Ad::with(['owner', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by placement
        if ($request->filled('placement')) {
            $query->where('placement', $request->placement);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Search - enhanced to include owner name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($ownerQuery) use ($search) {
                      $ownerQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'priority':
                $query->orderBy('priority', 'desc')->latest();
                break;
            case 'impressions':
                $query->orderBy('impressions_count', 'desc');
                break;
            case 'clicks':
                $query->orderBy('clicks_count', 'desc');
                break;
            case 'ctr':
                $query->orderByRaw('CASE WHEN impressions_count > 0 THEN (clicks_count / impressions_count * 100) ELSE 0 END DESC');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $ads = $query->paginate(20)->appends($request->except('page'));

        $stats = [
            'total' => Ad::count(),
            'pending' => Ad::pending()->count(),
            'approved' => Ad::approved()->count(),
            'active' => Ad::active()->count(),
            'rejected' => Ad::rejected()->count(),
            'draft' => Ad::where('status', 'draft')->count(),
        ];

        return view('admin.ads.index', compact('ads', 'stats'));
    }

    /**
     * Display pending ads for approval
     */
    public function pending(): View
    {
        $ads = Ad::with(['owner', 'product'])
            ->pending()
            ->orderBy('created_at')
            ->paginate(20);

        $stats = [
            'pending' => $ads->total(),
            'today' => Ad::pending()->whereDate('created_at', today())->count(),
        ];

        return view('admin.ads.pending', compact('ads', 'stats'));
    }

    /**
     * Show ad details
     */
    public function show(int $id): View
    {
        $ad = Ad::with(['owner', 'product', 'approver', 'auditLogs.user'])
            ->findOrFail($id);

        return view('admin.ads.show', compact('ad'));
    }

    /**
     * Approve an ad
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $ad = Ad::pending()->findOrFail($id);

        // Check policy
        $this->authorize('approve', $ad);

        DB::beginTransaction();
        try {
            $ad->approve(auth()->user());
            
            // Update scheduling if provided
            if ($request->filled('start_at')) {
                $ad->start_at = $request->start_at;
            }
            if ($request->filled('end_at')) {
                $ad->end_at = $request->end_at;
            }
            if ($request->filled('priority')) {
                $ad->priority = $request->priority;
            }
            if ($request->filled('admin_notes')) {
                $ad->admin_notes = $request->admin_notes;
            }
            
            $ad->save();

            // Log audit
            $ad->logAudit('approved', [
                'approved_by' => auth()->user()->name,
                'approved_at' => now()->toISOString(),
            ], [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Notify owner
            if ($ad->owner) {
                $ad->owner->notify(new AdApprovedNotification($ad));
            }

            DB::commit();

            return redirect()->back()->with('success', "Advertisement '{$ad->title}' has been approved.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve advertisement: ' . $e->getMessage());
        }
    }

    /**
     * Reject an ad
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $ad = Ad::pending()->findOrFail($id);

        // Check policy
        $this->authorize('reject', $ad);

        DB::beginTransaction();
        try {
            $ad->reject(auth()->user(), $request->reason);

            if ($request->filled('admin_notes')) {
                $ad->admin_notes = $request->admin_notes;
                $ad->save();
            }

            // Log audit
            $ad->logAudit('rejected', [
                'rejected_by' => auth()->user()->name,
                'rejection_reason' => $request->reason,
                'rejected_at' => now()->toISOString(),
            ], [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Notify owner
            if ($ad->owner) {
                $ad->owner->notify(new AdRejectedNotification($ad));
            }

            DB::commit();

            return redirect()->back()->with('success', "Advertisement '{$ad->title}' has been rejected.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject advertisement: ' . $e->getMessage());
        }
    }

    /**
     * Update ad scheduling
     */
    public function updateScheduling(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
        ]);

        $ad = Ad::findOrFail($id);

        $ad->start_at = $request->start_at;
        $ad->end_at = $request->end_at;
        $ad->save();

        $ad->logAudit('scheduling_updated', [
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        return redirect()->back()->with('success', 'Ad scheduling updated successfully.');
    }

    /**
     * Update ad priority
     */
    public function updatePriority(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'priority' => 'required|integer|min:0|max:100',
            'is_local_owner' => 'nullable|boolean',
        ]);

        $ad = Ad::findOrFail($id);

        $this->authorize('setPriority', $ad);

        $ad->priority = $request->priority;
        if ($request->has('is_local_owner')) {
            $ad->is_local_owner = $request->boolean('is_local_owner');
        }
        $ad->save();

        $ad->logAudit('priority_updated', [
            'priority' => $request->priority,
            'is_local_owner' => $request->boolean('is_local_owner'),
        ]);

        return redirect()->back()->with('success', 'Ad priority updated successfully.');
    }

    /**
     * Toggle ad active status
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $ad = Ad::findOrFail($id);

        $this->authorize('toggleActive', $ad);

        $ad->is_active = !$ad->is_active;
        $ad->save();

        $status = $ad->is_active ? 'activated' : 'deactivated';
        $ad->logAudit('status_toggled', ['is_active' => $ad->is_active]);

        return redirect()->back()->with('success', "Advertisement has been {$status}.");
    }

    /**
     * Bulk approve ads
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id',
        ]);

        $ads = Ad::pending()->whereIn('id', $request->ad_ids)->get();

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($ads as $ad) {
                if ($ad->approve(auth()->user())) {
                    $ad->logAudit('bulk_approved');
                    if ($ad->owner) {
                        $ad->owner->notify(new AdApprovedNotification($ad));
                    }
                    $count++;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "{$count} advertisements approved successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Bulk approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reject ads
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'exists:ads,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ads = Ad::pending()->whereIn('id', $request->ad_ids)->get();

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($ads as $ad) {
                if ($ad->reject(auth()->user(), $request->reason)) {
                    $ad->logAudit('bulk_rejected', ['reason' => $request->reason]);
                    if ($ad->owner) {
                        $ad->owner->notify(new AdRejectedNotification($ad));
                    }
                    $count++;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "{$count} advertisements rejected successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Bulk rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete an ad
     */
    public function destroy(int $id): RedirectResponse
    {
        $ad = Ad::findOrFail($id);

        $this->authorize('delete', $ad);

        $ad->logAudit('deleted');
        $ad->delete();

        return redirect()->route('admin.ads.index')->with('success', 'Advertisement deleted successfully.');
    }

    /**
     * View ad analytics
     */
    public function analytics(int $id): View
    {
        $ad = Ad::with(['impressions', 'clicks'])->findOrFail($id);

        $analytics = [
            'total_impressions' => $ad->impressions_count,
            'total_clicks' => $ad->clicks_count,
            'ctr' => $ad->ctr,
            'impressions_by_day' => $ad->impressions()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->limit(30)
                ->get(),
            'clicks_by_day' => $ad->clicks()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->limit(30)
                ->get(),
            'impressions_by_device' => $ad->impressions()
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get(),
            'clicks_by_device' => $ad->clicks()
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get(),
        ];

        return view('admin.ads.analytics', compact('ad', 'analytics'));
    }
}
