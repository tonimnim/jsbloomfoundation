<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    /**
     * Display newsletter subscribers list.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('newsletter_subscribers')
            ->orderBy('created_at', 'desc');
            
        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $subscribers = $query->paginate(20);
        
        // Get statistics
        $stats = [
            'total_subscribers' => DB::table('newsletter_subscribers')->count(),
            'active_subscribers' => DB::table('newsletter_subscribers')->where('is_active', true)->count(),
            'unsubscribed' => DB::table('newsletter_subscribers')->where('is_active', false)->count(),
            'this_month' => DB::table('newsletter_subscribers')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return Inertia::render('Admin/Newsletter/Index', [
            'subscribers' => $subscribers,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    /**
     * Export subscribers to CSV.
     */
    public function export(Request $request)
    {
        $subscribers = DB::table('newsletter_subscribers')
            ->select('email', 'name', 'is_active', 'subscribed_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $csv = "Email,Name,Status,Subscribed At,Created At\n";
        
        foreach ($subscribers as $subscriber) {
            $status = $subscriber->is_active ? 'Active' : 'Unsubscribed';
            $csv .= "\"{$subscriber->email}\",\"{$subscriber->name}\",\"{$status}\",\"{$subscriber->subscribed_at}\",\"{$subscriber->created_at}\"\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
    }
    
    /**
     * Delete a subscriber.
     */
    public function destroy($id)
    {
        DB::table('newsletter_subscribers')->where('id', $id)->delete();
        
        return redirect()->back()->with('success', 'Subscriber deleted successfully.');
    }
    
    /**
     * Toggle subscriber status.
     */
    public function toggleStatus($id)
    {
        $subscriber = DB::table('newsletter_subscribers')->where('id', $id)->first();
        
        if (!$subscriber) {
            abort(404);
        }
        
        DB::table('newsletter_subscribers')
            ->where('id', $id)
            ->update([
                'is_active' => !$subscriber->is_active,
                'updated_at' => now(),
            ]);
            
        return redirect()->back()->with('success', 'Subscriber status updated.');
    }
}