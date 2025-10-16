<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactsController extends Controller
{
    /**
     * Display a listing of contact messages.
     */
    public function index(Request $request): Response
    {
        $query = DB::table('contact_submissions');
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        // Filter by read status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'unread') {
                $query->where('is_read', false);
            } elseif ($status === 'read') {
                $query->where('is_read', true);
            }
        }
        
        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        
        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        // Get statistics
        $stats = [
            'total_messages' => DB::table('contact_submissions')->count(),
            'unread_messages' => DB::table('contact_submissions')->where('is_read', false)->count(),
            'today_messages' => DB::table('contact_submissions')->whereDate('created_at', today())->count(),
            'this_week' => DB::table('contact_submissions')->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
        
        return Inertia::render('Admin/Contacts/Index', [
            'messages' => $messages,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }
    
    /**
     * Display the specified contact message.
     */
    public function show($id): Response
    {
        $message = DB::table('contact_submissions')->where('id', $id)->first();
        
        if (!$message) {
            abort(404);
        }
        
        // Mark as read if not already
        if (!$message->is_read) {
            DB::table('contact_submissions')
                ->where('id', $id)
                ->update([
                    'is_read' => true,
                    'updated_at' => now(),
                ]);
        }
        
        return Inertia::render('Admin/Contacts/Show', [
            'message' => $message,
        ]);
    }
    
    /**
     * Mark message as read/unread.
     */
    public function toggleRead($id)
    {
        $message = DB::table('contact_submissions')->where('id', $id)->first();
        
        if (!$message) {
            abort(404);
        }
        
        DB::table('contact_submissions')
            ->where('id', $id)
            ->update([
                'is_read' => !$message->is_read,
                'updated_at' => now(),
            ]);
        
        return back()->with('success', 'Message status updated.');
    }
    
    /**
     * Delete the specified contact message.
     */
    public function destroy($id)
    {
        DB::table('contact_submissions')->where('id', $id)->delete();
        
        return redirect()->route('admin.contacts.index')
            ->with('success', 'Message deleted successfully.');
    }
    
    /**
     * Delete multiple messages.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contact_submissions,id',
        ]);
        
        DB::table('contact_submissions')
            ->whereIn('id', $validated['ids'])
            ->delete();
        
        return back()->with('success', count($validated['ids']) . ' messages deleted successfully.');
    }
    
    /**
     * Mark multiple messages as read.
     */
    public function bulkMarkRead(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contact_submissions,id',
        ]);
        
        DB::table('contact_submissions')
            ->whereIn('id', $validated['ids'])
            ->update([
                'is_read' => true,
                'updated_at' => now(),
            ]);
        
        return back()->with('success', count($validated['ids']) . ' messages marked as read.');
    }
    
    /**
     * Reply to contact message via email.
     */
    public function reply(Request $request, $id)
    {
        $message = DB::table('contact_submissions')->where('id', $id)->first();
        
        if (!$message) {
            abort(404);
        }
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'reply_message' => 'required|string|max:5000',
        ]);
        
        // Send email reply
        try {
            Mail::send('emails.contact-reply', [
                'originalMessage' => $message,
                'replyMessage' => $validated['reply_message'],
                'subject' => $validated['subject'],
            ], function ($mail) use ($message, $validated) {
                $mail->to($message->email, $message->first_name . ' ' . $message->last_name)
                    ->subject($validated['subject'])
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            // Store reply record
            DB::table('contact_replies')->insert([
                'contact_submission_id' => $id,
                'subject' => $validated['subject'],
                'message' => $validated['reply_message'],
                'sent_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return back()->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send reply. Please try again.']);
        }
    }
}