<?php

namespace App\Http\Controllers\V1;

use App\OtpRequest;
use App\RegistrationOtp;
use App\Services\SMS\GateWay\Twilio;
use App\User;
use App\UserDeviceId;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestOtpController extends Controller
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
    /**
     * @var RegistrationOtp
     */
    private $registrationOtp;
    /**
     * @var UserDeviceId
     */
    private $userDeviceId;

    public function __construct(User $user, OtpRequest $otpRequest, RegistrationOtp $registrationOtp, UserDeviceId $userDeviceId) {
        $this->user = $user;
        $this->otpRequest = $otpRequest;
        $this->registrationOtp = $registrationOtp;
        $this->userDeviceId = $userDeviceId;
    }

    public function requestRegistrationOtp(Request $request) {
        $data = $request->all();

        $rules = [
            'phoneNumber' => 'required',
            'countryCode' => 'required'
        ];

        try {
            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }

            $existingUser = $this->user->where('countryCode', $data['countryCode'])->where('phoneNumber', $data['phoneNumber'])->first();

            if (!$existingUser) {
                $token = $this->registrationOtp->where('countryCode', $data['countryCode'])->where('phoneNumber', $data['phoneNumber'])->first();
                $otpCode = mt_rand(100000, 999999);
                $currentDate = strtotime(date('Y-m-d h:i:s'));
                $futureDate = $currentDate + (60 * 5);
                $date = date("Y-m-d H:i:s", $futureDate);

                if ($token) {
                    $token->otpCode = $otpCode;
                    $token->otpCodeExpiration = $date;
                    $token->save();
                } else {
                    $this->registrationOtp->create([
                        'countryCode'       => $data['countryCode'],
                        'phoneNumber'       => $data['phoneNumber'],
                        'otpCode'           => $otpCode,
                        'otpCodeExpiration' => $date
                    ]);
                }

                return $this->responseSuccess(['token' => $otpCode], 200);
            } else {
                return $this->responseFailWithCode(711);
            }
        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(500);
        }
    }

    public function requestOtp(Request $request) {
        $data = $request->all();

        $rules = [
            'phoneNumber' => 'required',
            'countryCode' => 'required',
            'otpType' => 'required'
        ];

        try {
            $validation = $this->validator($data, $rules);

            if($validation->fails()){
                throw new \Exception("ValidationException");
            }

            $user = $this->user->where('phoneNumber', $data['phoneNumber'])
                ->where('countryCode', $data['countryCode'])->first();

            $token = $this->otpRequest->where('userId', $user->id)->where('otpType', $data['otpType'])->first();

            $otpCode =  mt_rand(100000, 999999);

            if ($user) {
                $currentDate = strtotime(date('Y-m-d h:i:s'));
                $futureDate = $currentDate+(60*5);
                $date = date("Y-m-d H:i:s", $futureDate);

                if ($token) {
                    $token->otpCode = $otpCode;
                    $token->otpCodeExpiration = $date;
                    $token->save();
                } else {
                    $this->otpRequest->create([
                        'userId' => $user->id,
                        'otpCode' => $otpCode,
                        'otpCodeExpiration' => $date,
                        'otpType' => $data['otpType'],
                    ]);
                }

                return $this->responseSuccess(['token' => $otpCode], 200);
            } else {
                throw new \Exception("NotFoundException");
            }
        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(500);
        }
    }

    public function changePhoneNumberOtp(Request $request)
    {
        $data = $request->all();

        $rules = [
            'phoneNumber' => 'required',
            'countryCode' => 'required'
        ];

        try {
            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }


            $otpCode =  mt_rand(100000, 999999);

            $currentDate = strtotime(date('Y-m-d h:i:s'));
            $futureDate = $currentDate+(60*5);
            $date = date("Y-m-d H:i:s", $futureDate);

            $otp = $this->otpRequest->create([
                'userId' => $request->user()->id,
                'otpCode' => $otpCode,
                'otpCodeExpiration' => $date,
                'otpType' => 2
            ]);

            return $this->responseSuccess(['token' => $otpCode], 200);

        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(500);
        }
    }
}
