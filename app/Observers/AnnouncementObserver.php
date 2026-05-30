<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class AnnouncementObserver
{
    public function created(Announcement $announcement): void
    {
        if ($announcement->status === 'published' && $announcement->send_notification) {
            $this->dispatchNotifications($announcement);
        }
    }

    public function updated(Announcement $announcement): void
    {
        // Only send notifications when status changes to published or send_notification is toggled on
        if ($announcement->wasChanged('status') && $announcement->status === 'published' && $announcement->send_notification) {
            $this->dispatchNotifications($announcement);
        }
    }

    private function dispatchNotifications(Announcement $announcement): void
    {
        $query = User::where('tenant_id', $announcement->tenant_id)
            ->where('is_active', true);

        // Map audience to user roles
        $audience = $announcement->target_audience ?? 'all';

        if ($audience !== 'all') {
            $roleMap = [
                'students' => 'student',
                'teachers' => 'teacher',
                'staff' => 'staff',
                'parents' => 'parent',
            ];

            if (isset($roleMap[$audience])) {
                $query->where('role', $roleMap[$audience]);
            }
        }

        $users = $query->get();

        $type = $announcement->is_urgent ? 'warning' : 'info';

        foreach ($users as $user) {
            Notification::create([
                'tenant_id' => $announcement->tenant_id,
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'title' => $announcement->title,
                'message' => Str::limit($announcement->content, 200),
                'type' => $type,
                'action_url' => null,
                'is_read' => false,
            ]);
        }
    }
}
