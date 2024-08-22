<?php
namespace App\Helpers;

class ApiHelper
{

    /*
        * Function Name : getWithResource
        * Purpose       :  
        * Input Params  :  void
        * Return Value  :  Array 
   */

    public static function getWithResource()
    {
        return [];
        // return [
        //     'errors' => [
        //         'status' => 0,
        //         'message' => '',
        //     ]
        // ];
    }

    /*
        * Function name : showValidationError
        * Purpose : formatted validation error
        * Author  :
        * Created Date : 25-03-2019
        * Modified date :
        * Params : 
        * Return : array
    */    

    static function showValidationError($errorsRequired){
        $resp_required  = [];
        foreach ($errorsRequired->all() as $key => $value) {
            array_push($resp_required,$value);
        }
        return ['data' => [], 'status' => 422, 'message' => $resp_required];
    }    

}
