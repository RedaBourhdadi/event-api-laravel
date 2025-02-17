<?php

namespace App\Http\Controllers;

use App\Models\EventAttendee;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\Mail;

class AttendeeController extends CrudController
{
    protected $table = 'event_attendees';
    protected $modelClass = EventAttendee::class;

    protected function getTable()
    {
        return $this->table;
    }
    protected function getModelClass()
    {
        return $this->modelClass;
    }
    public function createOne(Request $request)
    {
        try {
            return DB::transaction(
                function () use ($request) {
                    if (in_array('create', $this->restricted)) {
                        $user = $request->user();
                        if (! $user->hasPermission($this->getTable(), 'create')) {

                           
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
                    $cont = DB::table('event_attendees')->where('user_id',$request->user()->id)->where('event_id',$request->event_id)->count();
                    $name = explode('@', $request->user()->email)[0];

                    
                    if ($cont<1){
                            $model = $this->model()->create($validated);

                            if (method_exists($this, 'afterCreateOne')) {
                                $this->afterCreateOne($model, $request);
                            }
                            try {
                                Mail::send('vendor.mail.html.registration-confirmed', [
                                    'userName' => $name,
                                    'eventTitle' => $model->event->title,
                                    'eventDate' => $model->event->date,
                                    'eventLocation' => $model->event->location,
                                ], function ($message) use ($request) {
                                    $message->to($request->user()->email)
                                           ->subject('Event Registration Confirmed');
                                });



                            } catch (\Exception $e) {
                                Log::error('Failed to send registration email: ' . $e->getMessage());
                            }
                            Notification::create([
                                'user_id' => $model->event->user_id,
                                'title' => 'New Event Participant',
                                'message' => $name . ' has joined your event: ' . $model->event->title,
                                'data' => ['event_id' => $model->id]
                            ]);
        
                            return response()->json(
                                [
                                    'success' => true,
                                    'data' => ['item' => $model],
                                    'message' => __('You have successfully registered for the event'),
                                ]
                            );
                        
                    }else{
                        return response()->json(['success' => false, 'errors' => [__('You are allredy registered for the event')]]);
                    }
                   
                }
            );
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CrudController.createOne: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
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
                $items = $query->where('user_id', $user->id)->with('event')->get();
            } else {
                $items = $query->where('user_id', $user->id)->with('event')->paginate($request->input('per_page', 50));
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


}
