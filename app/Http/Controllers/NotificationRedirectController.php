<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRedirectController extends Controller
{
    public function __invoke(
        DatabaseNotification $notification
    ) {

        abort_unless(
            $notification->notifiable_id === auth()->id(),
            403
        );

        $notification->markAsRead();

        return redirect(
            $notification->data['url']
        );
    }
}
