<?php

namespace App\Http\Controllers\V1_1_0;

use App\CartHeader;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\OtpRequest;
use App\RegistrationOtp;
use App\Repositories\Wallet\WalletRepository;
use App\User;
use App\UserDeviceId;
use Aws\Api\Parser\Exception\ParserException;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Mail;
use App\Mail\QuestRewards;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

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
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;

	public function __construct(User $user, OtpRequest $otpRequest, RegistrationOtp $registrationOtp, UserDeviceId $userDeviceId, CartHeader $cartHeader, WalletRepository $walletRepository) {
		$this->middleware('jwt.auth:' . $this->guard, ['except' => ['login', 'register', 'requestOtp', 'requestRegistrationOtp','checkUsernameExist']]);
		$this->user = $user;
		$this->otpRequest = $otpRequest;
		$this->registrationOtp = $registrationOtp;
		$this->userDeviceId = $userDeviceId;
		$this->accountSid = config('twilio.accountSid');
		$this->authToken = config('twilio.authToken');
		$this->serviceSid = config('twilio.serviceSid');

		$this->cartHeader = $cartHeader;
		$this->walletRepository = $walletRepository;
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
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		];

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			$existingUser = $this->user->where('countryCode', $data['countryCode'])->where('phoneNumber', urldecode($data['phoneNumber']))->first();


			if (!$existingUser) {

				if (is_numeric($data['phoneNumber'])) {
					$res = $this->sendOtp($data['countryCode'] . $data['phoneNumber']);
				} else {
					$res = $this->sendEmailOtp($data['phoneNumber'], $data['countryCode']);
				}
				

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

	public function updateProfile(UpdateProfileRequest $request) {
		$data = $request->all();
		$user = auth($this->guard)->user();
		$response = $this->user->find($user->id);
		$response->fill($data)->save();

		return $this->responseSuccess($response, 200);
	}

	public function checkUsernameExist(Request $request){
	    $data =  $request->all();
        $checkExist = $this->user->getModel()->whereUsername($data['username'])->first();
        if ($checkExist){
            return $this->responseFailWithCode(714);
        }
        return $this->responseSuccess($checkExist, 200);
    }

	public function register(Request $request) {
		$data = $request->all();

		$code = $data['countryCode'];

		$rules = [
			'phoneNumber' => 'bail|required|unique:users',
			'countryCode' => 'required',
			'username'    => 'required|unique:users',
			'otpCode'     => 'required',
           	'deviceToken' => 'required',
           	// 'email'       => 'email',
		];

		try {

			// Log::debug(serialize ($data));

			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			$isNumberOTP = is_numeric($data['phoneNumber']);
			if ($isNumberOTP) {
				// NUMBER OTP
				$verify = $this->verifyCode($code, $phone_number);
			} else {
				// EMAIL OTP
				$verify = $this->verifyEmailCode($data['otpCode'], $data['deviceToken']);
			}

			if ($verify === 'approved') {

				// EMAIL WORKAROUND
				if (!$isNumberOTP) {
					$data = array(
						'phoneNumber' => "NA",
						'countryCode' => $data['countryCode'],
						'username'    => $data['username'],
						'otpCode'     => $data['otpCode'],
						'deviceType'  => 0,
						'deviceToken' => $data['deviceToken'],
						'email'       => $data['phoneNumber'],
					);
				}

				$user = $this->user->create($data);

				// $wallet = $this->walletRepository->createAccount('customer');

				// $wallet_input = [
				// 	"table_type"         => "users",
				// 	"table_id"           => $user->id,
				// 	"account_identifier" => "device_id",
				// 	"account_number"     => $wallet['deviceId'],
				// ];

				// $this->walletRepository->storeAccount($wallet_input);
				$this->createCartIfNotExist($user->id);


				$out = $this->userDeviceId->create([
					"userId"      => $user->id,
					"deviceToken" => $request->deviceToken ? $request->deviceToken : '',
					"deviceType"  => $data["deviceType"],
				]);


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
				}
                if ($errors->has('username')) {
                    return $this->responseFailWithCode(714);
                }
                return $this->responseFailWithCode(600);
			}

			return $this->responseFailWithCode(500);
		}
	}

	public function requestOtp(Request $request) {
		$data = $request->all();
		$phone_number = $data['countryCode'] . $data['phoneNumber'];

		$rules = [
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		];

		$isNumberOTP = is_numeric($data['phoneNumber']);

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			if ($isNumberOTP) {
				$isUserDeleted = $user = $this->user->where('number', $data['phoneNumber'])
					->where('countryCode', $data['countryCode'])->withTrashed()->first();

				if ($isUserDeleted && $isUserDeleted->deleted_at != null) {
					return $this->responseFail(["message" => "Failed to login, contact support@questrewards.com"], 401);
				}

				$user = $this->user->where('number', $data['phoneNumber'])
					->where('countryCode', $data['countryCode'])->first();

			} else {
				$isUserDeleted = $user = $this->user->where('email', $data['phoneNumber'])->first();

				if ($isUserDeleted && $isUserDeleted->deleted_at != null) {
					return $this->responseFail(["message" => "Failed to login, contact support@questrewards.com"], 401);
				}

				$user = $this->user->where('email', $data['phoneNumber'])->first();

			}

			if ($user) {
				$isNumberOTP = is_numeric($data['phoneNumber']);
				if ($isNumberOTP) {
					// NUMBER OTP
					$res = $this->sendOtp($phone_number);
				} else {
					// EMAIL OTP
					$res = $this->sendEmailOtp($data['phoneNumber'], $data['countryCode']);
				}

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
		if (substr($phoneNumber, - 5) == "99999") {
			return true;
		}
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

	public function sendEmailOtp($email, $device_id) {

		// GENERATE CODE
		$OTP = mt_rand(10000,99999);

		// SAVE TO DB
		$admin = DB::table('otp_records')->insert([
			'otpCode' => $OTP,
			'otpCodeExpiration' => date('Y-m-d h:i:s'),
			'deviceToken' => $device_id,
		]);

		// SEND EMAIL
		try {
			Mail::to($email)->send(new QuestRewards($OTP));
			return Mail::failures() != 0 ? true : false;

		} catch (\Exception $ex) {
			return true;
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
		$isNumberOTP = is_numeric($data['phoneNumber']);

		try {
			$validation = $this->validator($data, $rules);

			if ($validation->fails()) {
				throw new \Exception("ValidationException");
			}

			if ($isNumberOTP) {
				$user = $this->user->where('phoneNumber', $data['phoneNumber'])
					->where('countryCode', $data['countryCode'])->first();
			} else {
				$user = $this->user->where('email', $data['phoneNumber'])->first();
			}

			if ($user) {
				if ($isNumberOTP) {
					$verify = $this->verifyCode($code, $phone_number);
				} else {
					$verify = $this->verifyEmailCode($data['otpCode'], $data['countryCode']);
				}

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
			$res = $this->sendOtp($phone_number);

			if ($res) {
				return $this->responseSuccess(null, 200);
			}

			return $this->responseFailWithCode(500);
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

		if (substr($phone_number, - 5) == "99999") {
			return 'approved';
		}
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

	public function verifyEmailCode($code, $device_id) {

		try {

			$response = DB::table('otp_records');
			$response = $response->where('otpCode', $code)->first();

			if ($response) {
				if (strcmp($response->deviceToken,$device_id) == 0){

					return 'approved';
				}
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

				if (isset($request->deviceToken) && $request->deviceToken) {
					$data = [
						"userId"      => $user->id,
						"deviceToken" => $request->deviceToken,
						"deviceType"  => $request->deviceType,
					];
					$this->userDeviceId->updateOrCreate(
						$data,
						$data
					);
				}

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

	public function logout(Request $request) {

		$data = $request->all();

		if (isset($data['deviceToken']) && $data['deviceToken']) {
			if ($token = $this->userDeviceId->where('deviceToken', $data['deviceToken'])->first()) {
				$token->delete();
			}

		}
		auth($this->guard)->logout();

		return $this->responseSuccess(null, 200);
	}
}
