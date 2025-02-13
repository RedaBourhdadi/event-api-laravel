<?php

namespace App\Http\Controllers;

use App\Models\EventAttendee;
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


                    
                    if ($cont<1){
                            $model = $this->model()->create($validated);

                            if (method_exists($this, 'afterCreateOne')) {
                                $this->afterCreateOne($model, $request);
                            }
                            try {
                                Mail::send('vendor.mail.html.registration-confirmed', [
                                    'userName' => $request->user()->name,
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


}
