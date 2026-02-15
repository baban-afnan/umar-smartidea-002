<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\EmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required|in:all,single',
            'user_id' => 'required_if:target,single|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $subject = $request->subject;
        $messageContent = $request->message;

        try {
            if ($request->target === 'all') {
                $users = User::where('status', 'active')->get();
                
                if ($users->isEmpty()) {
                    return back()->with('error', 'No active users found to notify.');
                }

                $count = 0;
                foreach ($users as $user) {
                    if ($user->email) {
                        // Use send() instead of queue() to ensure immediate delivery and feedback
                        Mail::to($user->email)->send(new EmailNotification($subject, $messageContent, $user->first_name));
                        $count++;
                    }
                }
                
                return back()->with('success', "Broadcast message successfully sent to $count users.");
            } else {
                $user = User::findOrFail($request->user_id);
                
                if (!$user->email) {
                    return back()->with('error', 'The selected user does not have a valid email address.');
                }

                Mail::to($user->email)->send(new EmailNotification($subject, $messageContent, $user->first_name));
                
                return back()->with('success', 'Notification successfully sent to ' . $user->email);
            }
        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while sending the notification: ' . $e->getMessage());
        }
    }

    public function searchUser(Request $request)
    {
        $search = $request->get('q');
        $users = User::where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->limit(10)
                    ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json($users);
    }
}
