<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EventAttendee;
use App\Models\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
class EventController extends CrudController
{
    //
    protected $table = 'events';
    protected $modelClass = Event::class;

    protected function getTable()
    {
        return $this->table;
    }
    protected function getModelClass()
    {
        return $this->modelClass;
    }



    // public function createOne(Request $request)
    // {
    //     try {
    //         // $request->merge(['password' => Hash::make($request->password)]);

    //         return parent::createOne($request);
    //     } catch (\Exception $e) {
    //         Log::error('Error caught in function UserController.createOne : '.$e->getMessage());
    //         Log::error($e->getTraceAsString());

    //         return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
    //     }
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
                $items = $query->where('user_id', $user->id)->get();
            } else {
                $items = $query->where('user_id', $user->id)->paginate($request->input('per_page', 50));
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

    public function readAllEvents(Request $request)
    {
        try {
            $items = Event::withCount('attendees')->get();
        
            // If you want to log the count
            Log::info('Events with attendee counts:', $items->toArray());

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

    public function deleteOne($id, Request $request)
    {
        try {
            return DB::transaction(
                function () use ($id, $request) {
                    if (in_array('delete', $this->restricted)) {
                        $user = $request->user();
                        if (! $user->hasPermission($this->getTable(), 'delete', $id)) {
                            return response()->json(
                                [
                                    'success' => false,
                                    'errors' => [__('common.permission_denied')],
                                ]
                            );
                        }
                    }

                    $model = $this->model()->find($id);

                    if (! $model) {
                        return response()->json(
                            [
                                'success' => false,
                                'errors' => [__($this->getTable().'.not_found')],
                            ]
                        );
                    }

                    if (method_exists($this, 'beforeDeleteOne')) {
                        $this->beforeDeleteOne($model, $request);
                    }
                    $attendees = EventAttendee::where('event_id', $id)->get();
                    foreach ($attendees as $attendee) {
                        Mail::send('vendor.mail.html.event-deleted', [
                            'eventTitle' => $model->title,
                            'eventDate' => $model->date,
                            'eventLocation' => $model->location,
                        ], function ($message) use ($attendee) {
                            $message->to($attendee->user->email)
                                   ->subject('Event Cancelled');
                        });
                    }

                    $model->delete();

                    // foreach ($attendees as $attendee) {
                    //     $attendee->delete();
                    // }

                    if (method_exists($this, 'afterDeleteOne')) {
                        $this->afterDeleteOne($model, $request);
                    }

                    return response()->json(
                        [
                            'success' => true,
                            'message' => __($this->getTable().'.deleted'),
                        ]
                    );
                }
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function CrudController.deleteOne: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            return DB::transaction(
                function () use ($id, $request) {
                    if (in_array('update', $this->restricted)) {
                        $user = $request->user();
                        if (! $user->hasPermission($this->getTable(), 'update', $id)) {
                            return response()->json(
                                [
                                    'success' => false,
                                    'errors' => [__('common.permission_denied')],
                                ]
                            );
                        }
                    }

                    $model = app($this->getModelClass());
                    $customValidationMsgs = method_exists($model, 'validationMessages') ? $model->validationMessages() : [];
                    $validated = $request->validate(app($this->getModelClass())->rules(), $customValidationMsgs);

                    $model = $this->model()->find($id);

                    if (! $model) {
                        return response()->json(
                            [
                                'success' => false,
                                'errors' => [__($this->getTable().'.not_found')],
                            ]
                        );
                    }

                    $oldTitle = $model->title;
                    $oldDate = $model->date;
                    $oldLocation = $model->location;

                    $model->update($validated);

                    $changes = [];
                    if ($oldTitle !== $model->title) {
                        $changes[] = "Title changed from '{$oldTitle}' to '{$model->title}'";
                    }
                    if ($oldDate !== $model->date) {
                        $changes[] = "Date changed from '" . \Carbon\Carbon::parse($oldDate)->format('F j, Y, g:i a') . 
                                    "' to '" . \Carbon\Carbon::parse($model->date)->format('F j, Y, g:i a') . "'";
                    }
                    if ($oldLocation !== $model->location) {
                        $changes[] = "Location changed from '{$oldLocation}' to '{$model->location}'";
                    }


                    if (!empty($changes)) {

                        $attendees = EventAttendee::where('event_id', $id)->get();

                        foreach ($attendees as $attendee) {
                            Mail::send('vendor.mail.html.event-updated', [
                                'eventTitle' => $model->title,
                                'eventDate' => $model->date,
                                'eventLocation' => $model->location,
                                'changes' => $changes
                            ], function ($message) use ($attendee) {
                                $message->to($attendee->user->email)
                                       ->subject('Event Details Updated');
                            });

                            Notification::create([
                                'user_id' => $attendee->user->id,
                                'title' => 'Event Details Updated',
                                'message' => 'The event "' . $model->title . '" has been updated: ' . implode(', ', $changes),
                                'data' => ['event_id' => $model->id]
                            ]);
                        }
                    }

                    if (method_exists($this, 'afterUpdateOne')) {
                        $this->afterUpdateOne($model, $request);
                    }

                    return response()->json(
                        [
                            'success' => true,
                            'data' => ['item' => $model],
                            'validated' => $validated,
                            'message' => __($this->getTable().'.updated'),
                        ]
                    );
                }
            );
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CrudController.updateOne: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }




}
