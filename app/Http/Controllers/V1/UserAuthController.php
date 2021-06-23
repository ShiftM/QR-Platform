<?php

namespace App\Http\Controllers\V1;

use App\CartHeader;
use App\Http\Controllers\Controller;
use App\OtpRequest;
use App\RegistrationOtp;
use App\User;
use App\UserDeviceId;
use Aws\Api\Parser\Exception\ParserException;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAuthController extends Controller {
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

	private $accountSid;
	private $authToken;
	private $serviceSid;
	/**
	 * @var CartHeader
	 */
	private $cartHeader;

	public function __construct(User $user, OtpRequest $otpRequest, RegistrationOtp $registrationOtp, UserDeviceId $userDeviceId, CartHeader $cartHeader) {
		$this->middleware('jwt.auth:' . $this->guard, ['except' => ['login', 'register', 'requestOtp', 'requestRegistrationOtp']]);
		$this->user = $user;
		$this->otpRequest = $otpRequest;
		$this->registrationOtp = $registrationOtp;
		$this->userDeviceId = $userDeviceId;
		$this->accountSid = config('twilio.accountSid');
		$this->authToken = config('twilio.authToken');
		$this->serviceSid = config('twilio.serviceSid');

		$this->cartHeader = $cartHeader;
	}

	public function createCartIfNotExist($userId) {
		$this->cartHeader->updateOrCreate(
			['user_id' => $userId],
			['user_id' => $userId, "limit" => 50]
		);
	}

	public function requestRegistrationOtp(Request $request) {
		$data = $request->all();

		$rules = [
			'phoneNumber' => 'required|digits:10',
			'countryCode' => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			$existingUser = $this->user->where('countryCode', $data['countryCode'])->where('phoneNumber', $data['phoneNumber'])->first();

			if (!$existingUser) {

				$res = $this->sendOtp($data['countryCode'] . $data['phoneNumber']);

				if ($res) {
					return $this->responseSuccess(null, 200);
				}

				return $this->responseFailWithCode(500);
			} else {
				return $this->responseFailWithCode(711);
			}
		} catch (\Exception $ex) {
			$validationMessage = $ex->getMessage();

			if ($validationMessage === "ValidationException") {
				return $this->responseFailWithCode(713);
			} else if ($validationMessage === "NotFoundException") {
				return $this->responseFailWithCode(700);
			}

			return $this->responseFailWithCode(500);
		}
	}

	public function register(Request $request) {
		$data = $request->all();
		$code = $data['otpCode'];
		$phone_number = $data['countryCode'] . $data['phoneNumber'];

		$rules = [
			'phoneNumber' => 'bail|required|unique:users',
			'countryCode' => 'required',
//            'birthday' => 'required',
			'username'    => 'required',
			'otpCode'     => 'required',
			'deviceType'  => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			if (!isset($data['birthday'])) {
				$data['birthday'] = date('Y-m-d H:i:s');
			}

			$verify = $this->verifyCode($code, $phone_number);
			if ($verify === 'approved') {
				$user = $this->user->create($data);
				$this->userDeviceId->create([
					"userId"      => $user->id,
					"deviceToken" => $request->deviceToken ? $request->deviceToken : '',
					"deviceType"  => $data["deviceType"],
				]);

				$this->createCartIfNotExist($user->id);
				if (!$token = JWTAuth::fromUser($user, ['exp' => null])) {
					return $this->responseFailWithCode(500);
				}
				$user->bearerToken = $token;

				return $this->responseSuccess(['user' => $user], 200);
			} else if ($verify === 'pending') {
				return $this->responseFailWithCode(702);
			} else {
				return $this->responseFailWithCode(712);
			}
		} catch (\Exception $ex) {
			$validationMessage = $ex->getMessage();

			if ($validationMessage === "ValidationException") {
				$errors = $validation->errors();

				if ($errors->has('phoneNumber') && $errors->first('phoneNumber') === "The phone number has already been taken.") {
					return $this->responseFailWithCode(711);
				} else {
					return $this->responseFailWithCode(600);
				}
			}

			return $this->responseFailWithCode(500);
		}
	}


	public function requestOtp(Request $request) {
		$data = $request->all();
		$phone_number = $data['countryCode'] . $data['phoneNumber'];


		$rules = [
			'phoneNumber' => 'required|digits:10',
			'countryCode' => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			$isUserDeleted = $user = $this->user->where('phoneNumber', $data['phoneNumber'])
				->where('countryCode', $data['countryCode'])->withTrashed()->first();


			if ($isUserDeleted && $isUserDeleted->deleted_at != null) {
				return $this->responseFail(["message" => "Failed to login, contact support@questrewards.com"], 401);
			}

			$user = $this->user->where('phoneNumber', $data['phoneNumber'])
				->where('countryCode', $data['countryCode'])->first();

			if ($user) {
				$res = $this->sendOtp($phone_number);

				if ($res) {
					return $this->responseSuccess(null, 200);
				}

				return $this->responseFailWithCode(500);
			} else {
				throw new \Exception("NotFoundException");
			}
		} catch (\Exception $ex) {
			$validationMessage = $ex->getMessage();

			if ($validationMessage === "ValidationException") {
				return $this->responseFailWithCode(713);
			} else if ($validationMessage === "NotFoundException") {
				return $this->responseFailWithCode(700);
			}

			return $this->responseFailWithCode(500);
		}
	}

	public function sendOtp($phoneNumber) {
		try {
			$twilio = new Client($this->accountSid, $this->authToken);

			$verification = $twilio->verify->v2->services($this->serviceSid)
				->verifications
				->create($phoneNumber, "sms");

			return true;
		} catch (\Exception $ex) {
			return false;
		}
	}

	public function login(Request $request) {
		$data = $request->all();
		$code = $data['otpCode'];
		$phone_number = $data['countryCode'] . $data['phoneNumber'];

		$rules = [
			'phoneNumber' => 'required',
			'countryCode' => 'required',
			'otpCode'     => 'required',
			'deviceType'  => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}
			$user = $this->user->where('phoneNumber', $data['phoneNumber'])
				->where('countryCode', $data['countryCode'])->first();

			if ($user) {
				$verify = $this->verifyCode($code, $phone_number);
				if ($verify === 'approved') {
					if (!$token = JWTAuth::fromUser($user, ['exp' => null])) {
						return $this->responseFailWithCode(500);
					}

					$this->createCartIfNotExist($user->id);
					$this->userDeviceId->updateOrCreate([
						"userId" => $user->id,
					], ["deviceToken" => $request->deviceToken ? $request->deviceToken : '', "deviceType" => $data["deviceType"]]);
					$user->bearerToken = $token;

					return $this->responseSuccess(['user' => $user], 200);
				} else if ($verify === 'pending') {
					return $this->responseFailWithCode(702);
				} else {
					return $this->responseFailWithCode(712);
				}
			} else {
				throw new \Exception("NotFoundException");
			}
		} catch (\Exception $ex) {
			$validationMessage = $ex->getMessage();

			if ($validationMessage === "ValidationException") {
				return $this->responseFailWithCode(600);
			} else if ($validationMessage === "NotFoundException") {
				return $this->responseFailWithCode(710);
			}

			return $this->responseFailWithCode(500);
		}

	}


	public function changePhoneNumberOtp(Request $request) {
		$data = $request->all();
		$phone_number = $data['countryCode'] . $data['phoneNumber'];
		$rules = [
			'phoneNumber' => 'bail|required|unique:users|digits:10',
			'countryCode' => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}
//            $res = $this->sendOtp($phone_number);
//
//            if($res) {
//                return $this->responseSuccess(null, 200);
//            }
//
//            return $this->responseFailWithCode(500);
		} catch (\Exception $ex) {
			$validationMessage = $ex->getMessage();

			if ($validationMessage === "ValidationException") {
				$errors = $validation->errors();


				if ($errors->has('phoneNumber') && $errors->first('phoneNumber') === "The phone number has already been taken.") {
					return $this->responseFailWithCode(711);
				} else {
					return $this->responseFailWithCode(713);
				}
			} else if ($validationMessage === "NotFoundException") {
				return $this->responseFailWithCode(700);
			}

			return $this->responseFailWithCode(500);
		}
	}

	public function updatePhoneNumber(Request $request) {
		$data = $request->all();
		$code = $data['otpCode'];
		$phone_number = $data['countryCode'] . $data['phoneNumber'];

		$rules = [
			'phoneNumber' => 'required|digits:10',
			'countryCode' => 'required',
			'otpCode'     => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			$user = auth($this->guard)->user();

			if ($user) {
				$verify = $this->verifyCode($code, $phone_number);
				if ($verify === 'approved') {
					$user->phoneNumber = $data['phoneNumber'];
					$user->countryCode = $data['countryCode'];
					if ($user->save()) {
						return $this->responseSuccess(['user' => $user], 200);
					}

				} else if ($verify === 'pending') {
					return $this->responseFailWithCode(702);
				} else {
					return $this->responseFailWithCode(712);
				}

				return $this->responseFailWithCode(500);
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

	public function verifyCode($code, $phone_number) {
		try {
			$twilio = new Client($this->accountSid, $this->authToken);
			$verification_check = $twilio->verify->v2->services($this->serviceSid)
				->verificationChecks
				->create($code, // code
					["to" => $phone_number]
				);
			//check if twilio returns approved status
			if ($verification_check->status == 'approved') {
				return 'approved';
			} else {
				return 'pending';
			}
		} catch (\Exception $ex) {
			return 'expired';
		}
	}


	public function updateDeviceToken(Request $request) {
		$user = auth($this->guard)->user();

		try {
			if ($user) {
				$this->userDeviceId->where('userId', $user->id)->update([
					"deviceToken" => $request->deviceToken ? $request->deviceToken : '',
					"deviceType"  => $request->deviceType,
				]);
			} else {
				throw new \Exception("NotFoundException");

			}
		} catch (\Exception $ex) {
			if ($ex->getMessage() === "NotFoundException") {
				return $this->responseFailWithCode(710);
			}
		}


		return $this->responseSuccess(null, 200);
	}

	public function logout() {
		auth($this->guard)->logout();

		return $this->responseSuccess(null, 200);
	}
}
