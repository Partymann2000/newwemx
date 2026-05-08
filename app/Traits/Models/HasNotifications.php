<?php

namespace App\Traits\Models;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasNotifications
{
    /**
     * Communication with notifications.
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Adds a new notification to the user.
     */
    public function notify(string $title, string $description, ?string $url = null): void
    {
        $this->notifications()->create([
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'is_read' => false,
        ]);
    }

    /**
     * Marks all notifications as read.
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->notifications()->update(['is_read' => true]);
    }

    /**
     * Returns unread notifications.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false)->get();
    }
}
