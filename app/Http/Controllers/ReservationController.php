<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReservationRequest;
use App\Http\Services\ReservationService;
use App\Http\Services\SlackService;

/**
 * 会議室予約システムコントローラー
 */
class ReservationController extends Controller
{
    /**
     * 会議室予約サービスインスタンス
     *
     * @var ReservationService
     */
    protected $reservationService;

    /**
     * slackサービスインスタンス
     *
     * @var SlackService
     */
    protected $slackService;

    /**
     * コンストラクタ
     *
     * @param ReservationService $reservationService
     */
    public function __construct(ReservationService $reservationService, SlackService $slackService)
    {
        $this->slackService = $slackService;
        $this->reservationService = $reservationService;
    }

    /**
     * 会議室予約画面表示
     *
     * @return view
     */
    public function index()
    {
        return view('reservation', ['meetingMembers' => $this->reservationService->getMeetingMembers()]);
    }

    /**
     * 会議室予約画面表示
     *
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        return $this->reservationService->search($request->all());
    }

    /**
     * 会議室予約詳細
     *
     * @param Request $request
     * @return array
     */
    public function detail(Request $request)
    {
        return $this->reservationService->detail($request->input('reservationId'));
    }

    /**
     * 会議室予約登録
     *
     * @param ReservationRequest $request
     * @return array
     */
    public function store(ReservationRequest $request)
    {
        // リピート日一覧取得
        $repeatDateList = $this->reservationService->getRepeatDateList($request->all());
        // 会議重複チェック
        $duplicatedMessages = $this->reservationService->checkMeetingDateTimeDuplicated($request->all(), $repeatDateList);

        // 重複する会議が存在する場合はエラー
        if (!empty($duplicatedMessages)) {
            return response()->json(['errors' => [$duplicatedMessages]], 400);
        }
        // 会議予約登録処理
        $meetingId = $this->reservationService->store($request->all(), $repeatDateList);

        // slack送信をするかチェック
        $this->reservationService->isSlackSend(
            $request->input('isSlack'),
            $meetingId,
            $this->slackService::SLACK_NOTIFICATION_TYPE['ADD']
        );
        return [];
    }

    /**
     * 会議室予約更新
     *
     * @param ReservationRequest $request
     * @return array
     */
    public function update(ReservationRequest $request)
    {
        // リピート日一覧取得
        $repeatDateList = $this->reservationService->getRepeatDateList($request->all());
        // 会議重複チェック
        $duplicatedMessages = $this->reservationService->checkMeetingDateTimeDuplicated($request->all(), $repeatDateList);

        // 重複する会議が存在する場合はエラー
        if (!empty($duplicatedMessages)) {
            return response()->json(['errors' => [$duplicatedMessages]], 400);
        }
        // 会議予約更新処理
        $meetingId = $this->reservationService->update($request->all(), $repeatDateList);

        // slack送信をするかチェック
        $this->reservationService->isSlackSend(
            $request->input('isSlack'),
            $meetingId,
            $this->slackService::SLACK_NOTIFICATION_TYPE['EDIT']
        );
        return [];
    }

    /**
     * 会議室予約削除
     *
     * @param Request $request
     * @return array
     */
    public function destroy(Request $request)
    {
        return $this->reservationService->destroy(intval($request->input('reservationId')));
    }

    /**
     * 会議室情報と会議対象メンバー情報取得
     *
     * @return array
     */
    public function getMeetingRoomsAndMembers()
    {
        return [
            'meetingRooms' => $this->reservationService->getMeetingRooms(),
            'meetingMember' => $this->reservationService->getMeetingMembers()
        ];
    }
}
