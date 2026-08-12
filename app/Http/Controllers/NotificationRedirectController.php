<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationRedirectController extends Controller
{
    public function __invoke(string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $rawUrl = $notification->data['url']
            ?? $notification->data['action_url']
            ?? route('dashboard');

        // Extract just the path + query string (e.g., "/tasks/treat-you-better?workspace=...")
        $parsed = parse_url($rawUrl);
        $relativePath = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        return redirect()->to($relativePath);
    }
}
