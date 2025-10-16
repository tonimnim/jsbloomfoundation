<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DonationsController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $tab = $request->get('tab', 'donations');
        
        // Build donations query
        $donationsQuery = DB::table('donations')
            ->orderBy('created_at', 'desc');
            
        // Apply filters
        if ($search) {
            $donationsQuery->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_email', 'like', "%{$search}%")
                  ->orWhere('donation_number', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $donationsQuery->where('status', $status);
        }
        
        if ($paymentMethod) {
            $donationsQuery->where('payment_method', $paymentMethod);
        }
        
        if ($dateFrom) {
            $donationsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $donationsQuery->whereDate('created_at', '<=', $dateTo);
        }
        
        // Get paginated donations
        $donations = $donationsQuery->paginate(20);
        
        // Format donations for frontend
        $formattedDonations = collect($donations->items())->map(function ($donation) {
            return [
                'id' => $donation->id,
                'donor_name' => $donation->donor_name ?: 'Anonymous',
                'donor_email' => $donation->donor_email,
                'donor_phone' => $donation->donor_phone,
                'amount' => $donation->amount,
                'currency' => $donation->currency,
                'payment_method' => $this->formatPaymentMethod($donation->payment_method),
                'transaction_id' => $donation->gateway_transaction_id ?: $donation->donation_number,
                'status' => $donation->status,
                'purpose' => $donation->project_designation ?: 'General Support',
                'date' => $donation->created_at,
                'anonymous' => $donation->is_anonymous ?? false,
                'donation_number' => $donation->donation_number,
                'processed_at' => $donation->processed_at,
            ];
        });
        
        // Get donors statistics
        $donorsQuery = DB::table('donations')
            ->select(
                DB::raw("COALESCE(donor_email, 'anonymous') as email"),
                DB::raw('MAX(donor_name) as name'),
                DB::raw('MAX(donor_phone) as phone'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_donated"),
                DB::raw("COUNT(CASE WHEN status = 'completed' THEN 1 END) as donation_count"),
                DB::raw('MIN(created_at) as first_donation'),
                DB::raw('MAX(created_at) as last_donation'),
                DB::raw('MAX(donor_organization) as organization')
            )
            ->groupBy('donor_email')
            ->havingRaw("COUNT(CASE WHEN status = 'completed' THEN 1 END) > 0")
            ->orderBy('total_donated', 'desc');
            
        if ($search && $tab === 'donors') {
            $donorsQuery->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_email', 'like', "%{$search}%")
                  ->orWhere('donor_organization', 'like', "%{$search}%");
            });
        }
        
        $donors = $donorsQuery->get();
        
        // Format donors for frontend
        $formattedDonors = $donors->map(function ($donor) {
            $lastDonationDate = Carbon::parse($donor->last_donation);
            $isActive = $lastDonationDate->diffInDays(now()) <= 90; // Active if donated in last 90 days
            
            return [
                'id' => md5($donor->email), // Generate a consistent ID
                'name' => $donor->name ?: 'Anonymous',
                'email' => $donor->email !== 'anonymous' ? $donor->email : null,
                'phone' => $donor->phone,
                'total_donated' => $donor->total_donated,
                'donation_count' => $donor->donation_count,
                'first_donation' => $donor->first_donation,
                'last_donation' => $donor->last_donation,
                'status' => $isActive ? 'active' : 'inactive',
                'type' => $donor->organization ? 'organization' : 'individual',
                'organization' => $donor->organization,
            ];
        });
        
        // Calculate statistics
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $stats = [
            'total_donations' => DB::table('donations')
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_donations' => DB::table('donations')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'total_donors' => DB::table('donations')
                ->where('status', 'completed')
                ->distinct('donor_email')
                ->count('donor_email'),
            'active_donors' => DB::table('donations')
                ->where('status', 'completed')
                ->where('created_at', '>=', Carbon::now()->subDays(90))
                ->distinct('donor_email')
                ->count('donor_email'),
            'pending_donations' => DB::table('donations')
                ->where('status', 'pending')
                ->count(),
            'completed_donations' => DB::table('donations')
                ->where('status', 'completed')
                ->count(),
        ];
        
        // Get recent payment transactions for more details
        $recentTransactions = DB::table('payment_transactions')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return Inertia::render('Admin/Donations/Index', [
            'donations' => $formattedDonations,
            'donors' => $formattedDonors,
            'stats' => $stats,
            'pagination' => [
                'total' => $donations->total(),
                'per_page' => $donations->perPage(),
                'current_page' => $donations->currentPage(),
                'last_page' => $donations->lastPage(),
                'from' => $donations->firstItem(),
                'to' => $donations->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'activeTab' => $tab,
            'paymentMethods' => [
                'mpesa' => 'M-Pesa',
                'card' => 'Credit/Debit Card',
                'paypal' => 'PayPal',
                'bank_transfer' => 'Bank Transfer',
            ],
            'statusOptions' => [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
            ],
        ]);
    }
    
    /**
     * View a specific donation
     */
    public function show($id)
    {
        $donation = DB::table('donations')->find($id);
        
        if (!$donation) {
            return redirect()->route('admin.donations.index')
                ->with('error', 'Donation not found');
        }
        
        // Get related transactions
        $transactions = DB::table('payment_transactions')
            ->where('donation_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Admin/Donations/Show', [
            'donation' => $donation,
            'transactions' => $transactions,
        ]);
    }
    
    /**
     * Generate receipt for a donation
     */
    public function receipt($id)
    {
        $donation = DB::table('donations')->find($id);
        
        if (!$donation || $donation->status !== 'completed') {
            return redirect()->route('admin.donations.index')
                ->with('error', 'Receipt not available for this donation');
        }
        
        // TODO: Generate PDF receipt
        return response()->json([
            'message' => 'Receipt generation will be implemented',
            'donation' => $donation
        ]);
    }
    
    /**
     * Export donations data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth());
        $dateTo = $request->get('date_to', Carbon::now());
        
        $donations = DB::table('donations')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="donations-' . now()->format('Y-m-d') . '.csv"',
            ];
            
            $callback = function() use ($donations) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, [
                    'Donation Number',
                    'Date',
                    'Donor Name',
                    'Donor Email',
                    'Amount',
                    'Currency',
                    'Payment Method',
                    'Status',
                    'Project Designation',
                    'Transaction ID'
                ]);
                
                // Add data
                foreach ($donations as $donation) {
                    fputcsv($file, [
                        $donation->donation_number,
                        $donation->created_at,
                        $donation->donor_name ?: 'Anonymous',
                        $donation->donor_email,
                        $donation->amount,
                        $donation->currency,
                        $donation->payment_method,
                        $donation->status,
                        $donation->project_designation ?: 'General Support',
                        $donation->gateway_transaction_id
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return redirect()->route('admin.donations.index')
            ->with('error', 'Export format not supported');
    }
    
    /**
     * Format payment method for display
     */
    private function formatPaymentMethod($method)
    {
        $methods = [
            'mpesa' => 'M-Pesa',
            'card' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
        ];
        
        return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}