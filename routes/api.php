<?php

use App\Models\employee;
use App\Models\legacy_user;
use App\Models\log;
use App\Models\receivedsms;
use App\Models\shift;
use App\Models\sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
            'start_date' => 'required_with:end_date|date_format:Y-m-d',
            'end_date' => 'required_with:start_date|date_format:Y-m-d',
            'date' => 'date:Y-m-d',
            'future' => 'boolean',
            'past' => 'boolean',
            'dates' => 'array',
            'dates.*' => 'date:Y-m-d H:i',
            'person_id' => 'integer|min:0|max:18446744073709551615',
            'need_id' => 'integer|min:0|max:18446744073709551615',
            'needs' => 'array',
            'needs.*' => 'required_with:needs|integer|min:0|max:18446744073709551615',

        //            'customer' => 'integer|max:255',
        //            'contact' => 'integer|max:255',
        //            'shift_details' => 'boolean',
        //            'job' => 'integer|max:255'
        ]);

        $shifts = shift::with('shift_has_contractor.belongs_to_company.customer')->with('shift_has_needs.has_person.employee_has_address')->with('shift_has_address')->where('Active_Status','=',1)->when(request()->input('start_date'),function($query){$query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "'.request()->input('start_date').' '.config('app.jobs_start_time').'" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "'.date('Y-m-d H:i',strtotime("+1 days",strtotime(request()->input('end_date').' '.config('app.jobs_start_time')))).'"'));})->when(request()->input('date'),function($query){$query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "'.request()->input('date').' '.config('app.jobs_start_time').'" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "'.date('Y-m-d H:i',strtotime("+1 days",strtotime(request()->input('date').' '.config('app.jobs_start_time')))).'"'));})->when(request()->input('future'),function($query){$query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) > "'.date('Y-m-d').' '.config('app.jobs_start_time').'"'));})->when(request()->input('past'),function($query){$query->whereRaw(DB::raw('CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "'.date('Y-m-d').' '.config('app.jobs_start_time').'"'));})->when(request()->input('dates'),function($query){
            $query->where(function($query){
                ForEach(request()->input('dates') as $date)
                {
                    $query->orWhereRaw(DB::raw('(CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) >= "'.$date.' '.config('app.jobs_start_time').'" AND CONCAT(`Shift`.`date`," ",`Shift`.`go_time`) < "'.date('Y-m-d H:i',strtotime("+1 days",strtotime($date.' '.config('app.jobs_start_time')))).'")'));
                }    
            });
        })->when(request()->input('person_id'),function($query){
            $query->whereHas('shift_has_needs',function ($query){$query->where('person_id',request()->input('person_id'));});            
        })->when(request()->input('need_id'),function($query){
            $query->whereHas('shift_has_needs',function ($query){$query->where('id',request()->input('need_id'));});
        })->when(request()->input('needs'),function($query){
            $query->whereHas('shift_has_needs',function($query){$query->whereIn('id',request()->input('needs'));});
        })->get();
        $return_array = [];
        ForEach($shifts as $shift)
        {
            $object = json_encode($shift);
            $object = json_decode($object);
            $object->distance = [];
            ForEach(config('app.dh_offices') as $office)
            {
                $object->distance[$office->address] = distance($office->lat,$office->lon,$object->shift_has_address->lat,$object->shift_has_address->long);
            }
            ForEach($shift->shift_has_needs as $index => $need)
            {
                if(is_object($need->has_person) && is_object($need->has_person->employee_has_address))
                {
                    $object->shift_has_needs[$index]->distance = distance($need->has_person->employee_has_address->lat,$need->has_person->employee_has_address->long,$object->shift_has_address->lat,$object->shift_has_address->long);
                    $object->shift_has_needs[$index]->has_person->office = Get_Closest_Office($need->has_person);
                }else
                {
                    $object->shift_has_needs[$index]->distance = null;
                }
            }
            $return_array[] = $object;
        }
        return response()->json(['message' => "shifts","shifts" => $return_array]);
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
    ForEach(config('app.dh_offices') as $offices)
    {
        $distance = distance($employee->employee_has_address->lat,$employee->employee_has_address->long,$offices->lat,$offices->lon);
        if($min_distance >= $distance)
        {
            $min_distance = $distance;
            $office = $offices->address;
        }
    }
    return $office;
}



