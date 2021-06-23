<?php

namespace App\Http\Controllers\V1;

use App\Event;
use App\Http\Controllers\Controller;
use App\UserBookmark;
use Aws\Api\Parser\Exception\ParserException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    protected $guard = 'user';
    /**
     * @var Event
     */
    private $event;
    /**
     * @var UserBookmark
     */
    private $userBookmark;

    public function __construct(Event $event, UserBookmark $userBookmark)
    {
        $this->middleware('jwt.auth:' . $this->guard,['except' => ['getEvents', 'getEventInfo', 'search']]);
        $this->event = $event;
        $this->userBookmark = $userBookmark;
    }

    public function getEvents(Request $request)
    {
        $data = $request->all();

        try {
            if (isset($data['startFrom']) && $data['startFrom'] !== '') {
                $event = $this->event->where('startDate', '>=', $data['startFrom']);
            } else {
                $event = $this->event->take(50);
            }

            $event = $event->with(['eventImages' => function ($query) {
                $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
            }, 'organizer'])->orderBy('startDate', 'desc')->get();

            // get present and past date
            $present = [];
            $past = [];
            foreach($event as $key => $value){
                if(date('Y-m-d H:i:s', strtotime($value->startDate)) >= date('Y-m-d H:i:s')){
                    array_push($present, $value);
                }
                if(date('Y-m-d H:i:s', strtotime($value->startDate)) < date('Y-m-d H:i:s')){
                    array_push($past, $value);
                }
            }
            
            usort($present, function($a, $b)
                {
                    return $a->startDate > $b->startDate;
                });
        
            $merge_event =  array_merge($present, $past);
        
            foreach($merge_event as $mkey => $mvalue){
                    $event[$mkey] = $mvalue;
            }

            return $this->responseSuccess($this->formatData($event), 200);
        } catch (\Exception $ex) {
            return $this->responseFailWithCode(500);
        }
    }

    public function getEventInfo(Request $request)
    {
        $data = $request->all();
        $userId = auth($this->guard)->user() ? auth($this->guard)->user()->id : false;

        try {
            $event = $this->event->with(['eventImages' => function ($query) {
                $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
            }, 'segments' => function ($query) {
                $query->with(['segmentExhibitors', 'locationImages' => function ($q) {
                    $q->select('eventSegmentId', DB::raw("CONCAT(path, fileName) as fullPath"));
                }]);
            }, 'schedules' => function ($query) {
                $query->with(['schedules' => function($q){
                    $q->orderBy('time', 'asc');
                }]);
            }, 'organizer', 'quests' => function ($query) use ($userId) {
                $query->with('booth');
                $query->whereHas('booth');
                $query->isCompleted($userId)->distinct('id');
            }])->find($data['id']);

            // return $event;
            return $this->responseSuccess($this->formatData($event), 200);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function search(Request $request)
    {
        $data = $request->all();

        try {
            if (isset($data['startFrom']) && $data['startFrom'] !== '') {
                $event = $this->event->where('startDate', '>=', $data['startFrom']);
            } else {
                $event = $this->event->take(50);
            }

            $event = $event->with('eventImages')->where(function ($query) use ($data) {
                $query->where('title', 'like', $data['keyword'] . '%')
                    ->orWhere('title', 'like', '% ' . $data['keyword'] . '%')
                    ->orWhere('title', 'like', '% ' . $data['keyword']);
            })->get();

            return $this->responseSuccess($event, 200);
        } catch (\Exception $ex) {
            return $this->responseFailWithCode(500);
        }
    }

    public function bookEvent(Request $request)
    {
        $data = $request->all();

        $user = auth($this->guard)->user();
        $bookmark = [];

        $rules = [
            'eventId' => 'required|exists:events,id'
        ];

        try {
            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }

            $existingBookmark = $this->userBookmark->where('eventId', $data['eventId'])
                ->where('userId', $user->id)->first();

            if ($existingBookmark) {
                $existingBookmark->delete();
                $bookmark['bookmarked'] = false;
            } else {
                $data['userId'] = $user->id;
                if ($this->userBookmark->create($data)) {
                    $bookmark['bookmarked'] = true;
                }
            }
            $bookmark['eventId'] = $data['eventId'];

            return $this->responseSuccess($bookmark, 200);
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

    /**
     * Format event list data(append isBookMarked field)
     * @param mixed $data
     * @return mixed
     */
    private function formatData($data)
    {

        try {
            $data->map(function ($item) {

                if(auth($this->guard)->user()){
                    $item->isBookMarked = $this->userBookmark->where('userId', auth($this->guard)->user()->id)->where('eventId', $item->id)->exists();
                }
                else{
                    $item->isBookMarked = false;
                }
            });

            return $data;
        } catch (\Exception $e) {
            if(auth($this->guard)->user()) {
                $data->isBookMarked = $this->userBookmark->where('userId', auth($this->guard)->user()->id)->where('eventId', $data->id)->exists();
            } else {
                $data->isBookMarked = false;

            }
            return $data;
        }
    }

    /**
     * Get user bookmarks
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookMarks(Request $request)
    {
        $userId = $request->user()->id;

        try {

            $bookMarks = $this->userBookmark
            ->has('events')
                ->with(['events' => function ($query) {
                    $query->with(['eventImages' => function ($query) {
                        $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
                    }]);
                }])
                ->where('userId', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return $item->events;
            });

            return $this->responseSuccess($bookMarks, 200);

        } catch (\Exception $ex) {

            return $this->responseFailWithCode(500);

        }
    }
}
