<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\PushNotificationRequest;
use App\PushNotification;
use App\Repositories\Rest\RestRepository;
use App\UserDeviceId;
use App\Http\Controllers\Controller;

class PushNotificationController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;

    public function __construct(PushNotification $rest)
    {

        $this->rest = new RestRepository($rest);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        //
        $data = $request->all();

        $callback = function () use ($data) {
            $response = $this->rest->getModel();
	        $response = $response->orderBy('id','DESC');
            $response = json_decode($data['paginate']) ? $response->paginate($data['per_page']) : $response->get();

            return listResponse($response);
        };

        return $this->exceptionHandler($callback);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PushNotificationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(PushNotificationRequest $request)
    {
        $data = $request->all();
        $data['admin_id'] = auth('admin')->user()->id;

        $callback = function () use ($data) {

            $response = $this->rest->create($data);
            $this->send($data);

            return createdResponse($response);
        };

        return $this->exceptionHandler($callback);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $callback = function () use ($id) {

            if ($response = $this->rest->getModel()->find($id)) {
                return showResponse($response);
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);

    }

    function send($data)
    {
        // Get users device id
        $id = UserDeviceId::whereNotNull('deviceToken')->get()->pluck('deviceToken');
        //firebase REST url
        $url = 'https://fcm.googleapis.com/fcm/send';

        // firebase app serverKey
     $server_key = env('FIREBASE_PUSH_NOTIFICATION_SERVER_KEY');

	    $fields = array(
            'registration_ids' => $id,
            'notification' => array(
                "title" => $data['title'],
                "body" => $data['message'],
                'push_trigger' => 'news_and_update',
            )
        );
        $fields = json_encode($fields);
        $headers = array
        (
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);

        curl_close($ch);
	    return $result;
    }

}
