<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeetingRoom;
use Illuminate\Support\Facades\DB;

class MeetingRoomController extends Controller
{
    protected $meetingRoom;

    public function __construct(MeetingRoom $meetingRoom)
    {
        $this->meetingRoom = $meetingRoom;
    }

    /**
     * 会議室一覧画面表示
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $lists = MeetingRoom::all();
        return view('meeting_room', ['lists' => $lists]);
    }

    /**
     * 会議室登録
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        // トランザクション開始
        DB::beginTransaction();
        try {
            // 会議室の登録処理を行う
            $result = $this->meetingRoom->storeMeetingRoom($request);
            DB::commit();
            return redirect('/meeting_room')->with('result_message', '会議室登録が完了しました');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('/meeting_room')->with('result_message', '会議室登録に失敗しました');
        }
    }

    /**
     * 会議室更新
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $meetingRoomId = (int)$request->id;

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 会議室の更新処理を行う
            $result = $this->meetingRoom->updateMeetingRoom($request, $meetingRoomId);
            DB::commit();
            return redirect('/meeting_room')->with('result_message', '会議室更新が完了しました');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('/meeting_room')->with('result_message', '会議室更新に失敗しました');
        }
    }

    /**
     * 会議室削除
     *
     * @param Request $request
     */
    public function destroy(Request $request)
    {
        $meetingRoomId = (int)$request->id;
        $meetingRoom = MeetingRoom::find($meetingRoomId);

        // 会議室情報の取得に失敗した場合
        if (is_null($meetingRoom)) {
            return redirect('/meeting_room')->with('result_message', '会議室情報の取得に失敗しました');
        }

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 会議室の削除処理を行う
            $meetingRoom->delete();
            DB::commit();
            return redirect('/meeting_room')->with('result_message', '会議室削除が完了しました');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('/meeting_room')->with('result_message', '会議室削除に失敗しました');
        }
        return redirect('/meeting_room');
    }
}
