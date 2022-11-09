<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    protected $table = 'meeting_rooms';

    protected $fillable = [
        'id', 'name', 'description', 'priority', 'created_at', 'update_at'
    ];

    public function storeMeetingRoom($request)
    {
        $datas = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'priority' => $request->input('priority')
        ];
        return MeetingRoom::create($datas);
    }

    public function updateMeetingRoom($request, $meetingRoomId)
    {
    	$datas = [
    		'name' => $request->input('name'),
    		'description' => $request->input('description'),
    		'priority' => $request->input('priority')
    	];
    	return MeetingRoom::find($meetingRoomId, 'id')->update($datas);
    }
    
}
