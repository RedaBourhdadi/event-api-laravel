<?php

namespace App\Http\Controllers;
// namespace App\Http\CrudController;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;



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
    public function readAll(Request $request)
    {
        try {
            $user = $request->user();
            if (in_array('read_all', $this->restricted)) {
                if (! $user->hasPermission($this->getTable(), 'read') && ! $user->hasPermission($this->getTable(), 'read_own')) {
                    return response()->json(
                        [
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]
                    );
                }
            }

            $items = [];

            $params = $this->getDatatableParams($request);

            $query = $this->getReadAllQuery()->dataTable($params);

            if ($request->input('per_page', 50) === 'all') {
                $items = $query->where('user_id',$request->user()->id )->get();
            } else {
                $items = $query->where('user_id',$request->user()->id )->paginate($request->input('per_page', 50));
            }

            if (method_exists($this, 'afterReadAll')) {
                $this->afterReadAll($items);
            }

            $items = collect(method_exists($items, 'items') ? $items->items() : $items);

            return response()->json(
                [
                    'success' => true,
                    'data' => [
                        'items' => $items,
                        'meta' => [
                            'current_page' => method_exists($items, 'currentPage') ? $items->currentPage() : 1,
                            'last_page' => method_exists($items, 'lastPage') ? $items->lastPage() : 1,
                            'total_items' => method_exists($items, 'total') ? $items->total() : $items->count(),
                        ],
                    ],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function CrudController.readAll: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }


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
