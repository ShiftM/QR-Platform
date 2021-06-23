<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quest extends Model
{

    use SoftDeletes;

    protected $fillable = [ 'eventId', 'boothId', 'title', 'description', 'points'];

    protected $hidden = ['deleted_at', 'updated_at'];

    public function hasOneEventBooth(){
        return $this->hasOne('App\EventBooth', 'id', 'eventBoothId')->with(['event', 'booth']);
    }

    public function event(){
        return $this->hasOne('App\Event', 'id', 'eventId');
    }

    public function booth() {
        return $this->belongsTo('App\Booth', 'boothId', 'id');
    }

    /**
     * Qery that check if user has completed the quest
     * @param $query,
     * @param int $userId
     * @return void
     * Note: when functions in model has a prefix of scope it means that you can chain or reuse this method to controller
     * You can call this method like 'isCompleted'.(just remove the scope prefix and change the first letter from uppercase to lowercase)
     */
    public function scopeIsCompleted($query, $userId) : void{
        $query
        ->select(
            'quests.*',
            DB::raw("IF(user_transaction_histories.userId is not null, TRUE, FALSE)  AS IsCompleted")
        )
        ->leftJoin('user_transaction_histories', function($join) use ($userId) {
            $join->on('user_transaction_histories.questId', '=', 'quests.id')
            ->where('user_transaction_histories.userId',$userId);
        });
    }
}
