<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * عرض كل الإشعارات
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * تحديد الكل كمقروء
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديد الكل كمقروء.' : 'All notifications marked as read.');
    }

    /**
     * تحديد إشعار واحد كمقروء والتوجه للرابط
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id == auth()->id()) {
            $notification->markAsRead();

            $actionUrl = $notification->data['action_url'] ?? '#';
            if ($actionUrl && $actionUrl !== '#') {
                return redirect($actionUrl);
            }
        }

        return back();
    }
}
