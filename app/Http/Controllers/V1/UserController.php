<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\OtpRequest;
use App\Services\Upload;
use App\User;
use App\UserTransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Services\SMS\GateWay\Twilio;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    protected $guard = 'user';
    /**
     * @var User
     */
    private $user;
    /**
     * @var OtpRequest
     */
    private $otpRequest;
    private $accountSid;
    private $authToken;
    private $serviceSid;

    public function __construct(User $user, OtpRequest $otpRequest)
    {
        $this->middleware('jwt.auth:' . $this->guard);
        $this->user = $user;
        $this->otpRequest = $otpRequest;
        $this->accountSid = config('twilio.accountSid');
        $this->authToken = config('twilio.authToken');
        $this->serviceSid = config('twilio.serviceSid');
    }

    public function profile()
    {
        $user = auth($this->guard)->user();

        return $this->responseSuccess($user, 200);
    }



    public function getTransactionHistory(Request $request)
    {
        $user = auth($this->guard)->user();

        $data = UserTransactionHistory::where('userId', $user->id)->with(['quest', 'event'])->orderBy('id', 'desc')->get();

        $history = [];
        foreach ($data as $d) {
            $record = new \stdClass();
            if ($d->action == 0) {
                $action = "Completed: ";
                $record->title = $action . $d['quest']['title'];
                $record->boothName = $d['quest']['booth']['name'];
                $record->eventName = $d['event']['title'];
                $date = new \DateTime($d->created_at);
                $record->transactionDate  = $date->format('Y-m-d\TH:i:s.v\Z');
                $record->points = $d->point;
                array_push($history, $record);
            } else {
                $action = "Redeemed: ";
                $record->title = $action . $d['itemName'];
                $record->eventName = $d['event']['title'];
                $date = new \DateTime($d->redeemedDate);
                $record->transactionDate = $date->format('Y-m-d\TH:i:s.v\Z');
                $record->points = -abs($d->point);
                array_push($history, $record);
            }
        }
        return $this->responseSuccess($history, 200);
    }

    /**
     * Upload user's photo
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\JsonResponse;
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $path = 'user/' . $userId;


            $file = Input::file('file');
            $size = Input::file('file')->getSize();
            if($file) {
                if($size > 0){
                    $photo = Upload::path($path)->file($file)->s3();

                    $this->user->where('id', $userId)->update([
                        'photo' => $photo['fullPath'],
                    ]);
                    $user = $this->user->find($userId);
                    return $this->responseSuccess($user, 200);
                } else {
                    return $this->responseFail(["message" => "Your file seems corrupted. Please check your file."],500);
                }

            } else {
                return $this->responseFail(["message" => "No file uploaded."],500);
            }


        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(422);
        }
    }

    /**
     * Request otp to change phone number
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\JsonResponse;
     */

}
