<?php

namespace App\Http\Controllers;
// namespace App\Http\CrudController;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;


use Illuminate\Http\Request;

class NotificationController extends CrudController
{
    protected $modelClass = Notification::class;
    protected $table = 'notifications';


    protected function getTable()
    {
        return 'notifications';
    }

    protected function getModelClass()
    {
        return Notification::class;
    }

    // protected function getReadAllQuery()
    // {
    //     return $this->model()
    //         ->forUser(Auth::id())
    //         ->unread()
    //         ->latest();
    // }

    public function markAsRead($id, Request $request)
    {
        try {
            $notification = $this->model()
                ->forUser($request->user()->id)
                ->findOrFail($id);

            $notification->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => __('notifications.marked_as_read'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')]
            ]);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $this->model()
                ->forUser($request->user()->id)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => __('notifications.all_marked_as_read'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')]
            ]);
        }
    }

    public function getUnreadCount(Request $request)
    {
        try {
            $count = $this->model()
                ->forUser($request->user()->id)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['count' => $count]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')]
            ]);
        }
    }
}
