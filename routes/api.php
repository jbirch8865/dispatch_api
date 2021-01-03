<?php

use App\Models\call;
use App\Models\employee;
use App\Models\job;
use App\Models\legacy_user;
use App\Models\log;
use App\Models\receivedsms;
use App\Models\shift;
use App\Models\sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use jbirch8865\AzureAuth\Http\Middleware\AzureAuth;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

 
*/

Route::group([], function () {
    Route::get('dispatchnumbers', function () {
        return response()->json(['message' => "hi"]);
    });
    Route::get('shifts', function () {
        request()->validate([
            'start_date' => 'required_without_all:date,dates,needs,need_id,dispatchable,shift_id,job_id,job_number|required_with:end_date|date',
            'end_date' => 'required_with:start_date|date',
            'date' => 'required_without_all:start_date,dates,needs,need_id,dispatchable,shift_id,job_id,job_number|date',
            'future' => 'boolean',
            'past' => 'boolean',
            'dates' => 'required_without_all:start_date,date,needs,need_id,dispatchable,shift_id,job_id,job_number|array',
            'dates.*' => 'date',
            'person_id' => 'integer|min:0|max:18446744073709551615',
            'need_id' => 'required_without_all:start_date,date,needs,dispatchable,shift_id,job_id,job_number|integer|min:0|max:18446744073709551615',
            'needs' => 'required_without_all:start_date,date,need_id,dispatchable,shift_id,job_id,job_number|array',
            'needs.*' => 'required_with:needs|integer|min:0|max:18446744073709551615',
            'dispatchable' => 'required_without_all:start_date,date,needs,need_id,shift_id,job_id,job_number|boolean',
            'shift_id' => 'required_without_all:start_date,date,needs,need_id,dispatchable,job_id,job_number|integer|min:0|max:18446744073709551615',
            'job_id' => 'required_without_all:start_date,date,needs,need_id,dispatchable,shift_id,job_number|integer|min:0|max:18446744073709551615',
            'Active_Status' => 'boolean',
            'customer' => 'integer|min:0|max:18446744073709551615',
            'contact' => 'integer|min:0|max:18446744073709551615',
            'missing_tcp' => 'boolean',
            'location_tbd' => 'boolean',
            'location' => 'string|max:255',
            'job_number' => 'required_without_all:start_date,date,needs,need_id,dispatchable,shift_id,job_id|string|max:255'
            //            'shift_details' => 'boolean',
            //            'job' => 'integer|max:255'
        ]);

        $shifts = shift::with('shift_has_contractor.belongs_to_company.customer')->with('shift_has_needs.has_person.employee_has_address')->with('shift_has_address')->with('shift_has_needs.has_skills.skill')->with('shift_has_notes.note')->with('shift_has_equipment_needs.subtype')->with('shift_has_equipment_needs.equipment')->when(request()->input('Active_Status', true), function ($query) {
            $query->where('Active_Status', 1);
        })->when(request()->input('start_date'), function ($query) {
            $query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "' . date('Y-m-d', strtotime(request()->input('start_date'))) . ' ' . config('app.jobs_start_time') . '" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "' . date('Y-m-d H:i', strtotime("+1 days", strtotime(date('Y-m-d', strtotime(request()->input('end_date'))) . ' ' . config('app.jobs_start_time')))) . '"'));
        })->when(request()->input('date'), function ($query) {
            $query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "' . date('Y-m-d', strtotime(request()->input('date'))) . ' ' . config('app.jobs_start_time') . '" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "' . date('Y-m-d H:i', strtotime("+1 days", strtotime(date('Y-m-d', strtotime(request()->input('date'))) . ' ' . config('app.jobs_start_time')))) . '"'));
        })->when(request()->input('future'), function ($query) {
            $query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) > "' . date('Y-m-d') . ' ' . config('app.jobs_start_time') . '"'));
        })->when(request()->input('past'), function ($query) {
            $query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "' . date('Y-m-d') . ' ' . config('app.jobs_start_time') . '"'));
        })->when(request()->input('dates'), function ($query) {
            $query->where(function ($query) {
                foreach (request()->input('dates') as $date) {
                    $date = date('Y-m-d', strtotime($date));
                    $query->orWhereRaw(DB::raw('(CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "' . date('Y-m-d', strtotime($date)) . ' ' . config('app.jobs_start_time') . '" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "' . date('Y-m-d H:i', strtotime("+1 days", strtotime($date . ' ' . config('app.jobs_start_time')))) . '")'));
                }
            });
        })->when(request()->input('person_id'), function ($query) {
            $query->whereHas('shift_has_needs', function ($query) {
                $query->where('person_id', request()->input('person_id'));
            });
        })->when(request()->input('need_id'), function ($query) {
            $query->whereHas('shift_has_needs', function ($query) {
                $query->where('id', request()->input('need_id'));
            });
        })->when(request()->input('needs'), function ($query) {
            $query->whereHas('shift_has_needs', function ($query) {
                $query->whereIn('id', request()->input('needs'));
            });
        })->when(request()->input('dispatchable'), function ($query) {
            $query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "2020-12-15 03:00:00" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "2020-12-16 03:00:00"'));
        })->when(request()->input('shift_id'), function ($query) {
            $query->where('shift_id', request()->input('shift_id'));
        })->when(request()->input('job_id'), function ($query) {
            $query->where('job_id', request()->input('job_id'));
        })->when(request()->input('contact'), function ($query) {
            $query->where('contractor_id', request()->input('contact'));
        })->when(request()->input('customer'), function ($query) {
            $query->whereHas('shift_has_contractor', function ($query) {
                $query->whereHas('belongs_to_company', function ($query) {
                    $query->where('customer_id', request()->input('customer'));
                });
            });
        })->when(request()->input('mssing_tcp'), function ($query) {
            $query->where('TCP', '<>', 'tcp_not_required');
        })->when(request()->input('location_tbd'), function ($query) {
            $query->whereHas('shift_has_address', function ($query) {
                $query->where('name', 'TBD');
            });
        })->when(request()->input('location'), function ($query) {
            $query->whereHas('shift_has_address', function ($query) {
                $query->where('street_address', 'LIKE', '%' . request()->input('location') . '%');
            });
        })->when(request()->input('job_number'), function ($query) {
            $query->where('job_number', request()->input('job_number'));
        })->orderBy('date')->get();
        $return_array = [];
        foreach ($shifts as $shift) {
            $object = json_encode($shift);
            $object = json_decode($object);
            $object->distance = [];
            foreach (config('app.dh_offices') as $office) {
                $object->distance[$office->address] = distance($office->lat, $office->lon, $object->shift_has_address->lat, $object->shift_has_address->long);
            }
            foreach ($shift->shift_has_needs as $index => $need) {
                if (is_object($need->has_person) && is_object($need->has_person->employee_has_address)) {
                    $object->shift_has_needs[$index]->distance = distance($need->has_person->employee_has_address->lat, $need->has_person->employee_has_address->long, $object->shift_has_address->lat, $object->shift_has_address->long);
                    $object->shift_has_needs[$index]->has_person->office = Get_Closest_Office($need->has_person);
                } else {
                    $object->shift_has_needs[$index]->distance = null;
                }
            }
            $return_array[] = $object;
        }
        return response()->json(['message' => "shifts", "shifts" => $shifts]);
    });
    Route::get('calls', function () {
        $calls = call::all();
        return response()->json(['message' => 'All unsaved calls', 'calls' => $calls]);
    });
    Route::post('calls', function () {
        request()->validate([
            'call' => 'required|json'
        ]);
        $user = new AzureAuth;
        $call = new call;
        $call->dispatcher = $user->Get_User_Oid(request());
        $call->call = request()->input('call');
        $call->save();
        return response()->json(['message' => '', 'call' => $call], 201);
    });
    Route::put('calls/{call}', function (call $call) {
        request()->validate([
            'call' => 'required|json'
        ]);
        $call->call = request()->input('call');
        $call->save();
        return response()->json(['message' => '', 'call' => $call], 201);
    });
    Route::delete('calls/{call}', function (call $call) {
        $call->forceDelete();
        return response()->json(['message' => ''], 201);
    });
});

function distance($lat1, $lon1, $lat2, $lon2, $unit = "M")
{
    if ($lat1 === "" || $lat2 === "" || $lon1 === "" || $lon2 === "") {
        $lat1 = 0;
        $lat2 = 0;
        $lon1 = 0;
        $lon2 = 0;
    }
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

function Get_Closest_Office($employee)
{
    $min_distance = 100000;
    $office = false;
    foreach (config('app.dh_offices') as $offices) {
        $distance = distance($employee->employee_has_address->lat, $employee->employee_has_address->long, $offices->lat, $offices->lon);
        if ($min_distance >= $distance) {
            $min_distance = $distance;
            $office = $offices->address;
        }
    }
    return $office;
}
