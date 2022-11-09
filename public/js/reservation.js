/**
 * 時間
 *
 * @var number
 */
define('HOURS', 24);

/**
 * 分単位
 *
 * @var number
 */
define('FIVE_MINUTE_UNIT', 5);

/**
 * 新規会議室予約ID
 *
 * @var number
 */
define('ADD_RESERVATION', 0);

/**
 * 曜日
 *
 * @var array
 */
define('WEEKDAY', ['日', '月', '火', '水', '木', '金', '土']);

/**
 * モーダルタイトル
 *
 * @var object
 */
define('RESERVATION_MODAL_TITLE', {
    ADD: '新規会議室予約登録',
    EDIT: '会議室予約編集'
});

/**
 * モーダル登録/更新ボタン
 *
 * @var object
 */
define('RESERVATION_MODAL_SAVE_BTN', {
    ADD: '登録',
    EDIT: '更新'
});

/**
 * 日にちの変更
 *
 * @var object
 */
define('CHANGE_DAY', {
    PREV: -1,
    NEXT: 1
});

/**
 * 会議カラー
 *
 * @var array
 */
define('COLORS', [
    {id: 'red', colorCode: '#8b0000'},
    {id: 'orange', colorCode: '#e47f04'},
    {id: 'yellow', colorCode: '#e4c102'},
    {id: 'olive', colorCode: '#808000'},
    {id: 'lime', colorCode: '#8ac101'},
    {id: 'green', colorCode: '#006400'},
    {id: 'teal', colorCode: '#008080'},
    {id: 'darkturquoise', colorCode: '#00cbcd'},
    {id: 'deepskyblue', colorCode: '#00abe4'},
    {id: 'dodgerblue', colorCode: '#0081ff'},
    {id: 'blue', colorCode: '#0000ff'},
    {id: 'slateblue', colorCode: '#6a5acd'},
    {id: 'purple', colorCode: '#4b0082'},
    {id: 'redpurple', colorCode: '#8b008b'},
    {id: 'deeppink', colorCode: '#ff1493'},
    {id: 'pink', colorCode: '#ff5fff'},
    {id: 'brown', colorCode: '#8B4513'},
    {id: 'silver', colorCode: '#909090'},
    {id: 'gray', colorCode: '#5c5c5c'},
    {id: 'black', colorCode: '#000000'},
]);

$(document).ready(function(){

    // 会議室予約画面初期化
    init();

    /**
     * モーダルを開いたタイミングで処理を実行
     */
    $('#reservationModal').on('hide.bs.modal', function() {
        // 会議予約情報をリロード
        reload();
    });
});

/**
 * 会議室予約画面初期化処理
 *
 * @return {void}
 */
function init() {
    // ロード開始
    showLoading()
    // 本日の年月日をセットする
    setTargetDay(getYmd(new Date()));
    // １時間間隔のラインを作成
    createHoursLine();
    // 会議スケジュールバー作成
    createMeetingRoomsRow();
}

/**
 * 会議予約一覧をリロード
 *
 * @return {void}
 */
function reload() {
    // ロード開始
    showLoading()
    // 会議予約情報を一度非表示にする。
    $('#row_area').empty();
    // 会議予約情報を再取得
    createMeetingRoomsRow();
}

/**
 * １時間間隔のラインを作成
 * ※1時間60pxで調整
 *
 * @return {void}
 */
function createHoursLine() {
    for (var hour = 0; hour < HOURS; hour++) {
        $('#time_area').append('<span class="r_head" style="left: calc(((5px * 12) * ' + hour + ') + 320px)">' + hour + ' 時</span>');
    }
}

/**
 * 会議スケジュールバー作成
 *
 * @return {void}
 */
function createMeetingRoomsRow() {
    // 会議予約を絞り込む
    filterMeetingReservationList().then(function(meetingData) {
        // 会議情報の数だけループ
        for(var i in meetingData) {
            // 会議室行を追加
            $('#row_area').append(''
                + '<div class="r_row">'
                    + '<div class="r_meeting_room_name">' + meetingData[i].meetingRoomName + ' </div>'
                    + '<div id="timebar' + i + '"class="r_time_bar">'
                        + createHtmlReservations(meetingData[i].reservationInfo)
                    + '</div>'
                + '</div>'
            );
        }
        // ツールチップセット
        $('[data-toggle="tooltip"]').tooltip();
        // ロード終了
        removeLoading();
    });
}

/**
 * 会議スケジュールHTML生成
 *
 * @param {object} reservationInfo
 * @return {string}
 */
function createHtmlReservations(reservationInfo) {
    let reservationHtml = '';

    // 会議予約数だけループ
    for(var i in reservationInfo) {
        // 会議予約バーのスタイル属性を取得
        let reservationBarStyle = getStyleMeetingReservation(reservationInfo[i].start, reservationInfo[i].end, reservationInfo[i].color);
        // ツールチップの情報を取得
        let toolTipBody = getToolTipBody(
            reservationInfo[i].title,
            reservationInfo[i].roomName,
            reservationInfo[i].organizerName,
            reservationInfo[i].start,
            reservationInfo[i].end,
            reservationInfo[i].comment,
            reservationInfo[i].members
        );
        // 会議予約バーを作成
        reservationHtml += '<div id="meetingRow' + reservationInfo[i].reservationId + '" class="r_reservation" data-toggle="tooltip" data-html="true" data-placement="right" title="' + toolTipBody;
        reservationHtml += '" style="' + reservationBarStyle +'" ondblclick="openReservationModal('+ reservationInfo[i].reservationId +')">' + reservationInfo[i].title + ' </div>';
    }
    return reservationHtml;
}

/**
 * 会議予約位置のスタイル属性を返す
 *
 * @param {string} start
 * @param {string} end
 * @param {string} color
 * @return {string}
 */
function getStyleMeetingReservation(start, end, color) {
    var startDate = new Date(start);
    var endDate = new Date(end);

    // 会議時間を計算
    var diff = endDate.getTime() - startDate.getTime();
    // 会議の開始位置を計算、終了位置を計算
    var leftStart = ((startDate.getHours() * 12) * 5) + startDate.getMinutes();
    let meetingTime = (((diff / (60 * 60 * 1000)) * 12) * 5);

    // style指定をして返す
    return 'left: ' + leftStart + 'px; width: ' + meetingTime + 'px; background: ' + color;
}

/**
 * ツールチップの中身を生成
 *
 * @param {string} title
 * @param {string} roomName
 * @param {string} organizerName
 * @param {string} start
 * @param {string} end
 * @param {string} comment
 * @param {array} members
 * @return {string}
 */
function getToolTipBody(title, roomName, organizerName, start, end, comment, members) {
    let toolTipBody = '【会議名】<br>' + title + '<br><br>';
    toolTipBody += '【会議場所】<br>' + roomName + '<br><br>';
    toolTipBody += '【会議時間】<br>' + getReservationTime(start) + '～' + getReservationTime(end) + '<br><br>';
    toolTipBody += '【会議メンバー】<br>' + getToolTipMembers(organizerName, members) + '<br>';
    toolTipBody += '【会議詳細】<br>' + formatCommentforLengthOver(comment);
    return toolTipBody;
}

/**
 * 文字数超過を整形（…を末尾に付ける）
 *
 * @param {string} comment
 * @return {string}
 */
function formatCommentforLengthOver(comment) {
    let maxLength = 300;

    // コメントが存在しない場合は空文字を返す
    if (comment === null) {
        return '<br>';
    }
    return (comment.length > maxLength) ? comment.substr(0, maxLength) + "..." : comment;
}

/**
 * ツールチップ表示用の会議メンバー一覧を取得
 *
 * @param {string} organizerName
 * @param {array} members
 * @return {string}
 */
function getToolTipMembers(organizerName, members) {
    let meetingMembersStr = '';

    // 会議メンバーの数だけループ
    for (memberName of members) {
        // 主催者の場合はマークを付けて追加
        if (organizerName === memberName) {
            meetingMembersStr += '・' + memberName + '（主催者）<br>';

        // 会議メンバー名を追加
        } else {
            meetingMembersStr += '・' + memberName + '<br>';
        }
    }
    return meetingMembersStr;
}

/**
 * HH:mmを返す
 *
 * @param {string} date
 * @return {string}
 */
function getReservationTime(date) {
    return date.substr(11, 5);
}

/**
 * 会議室予約モーダルを開く
 *
 * @param {number} meetingId
 */
function openReservationModal(meetingId) {

    // モーダルタイトル、ボタンを初期値にセット、会議予約IDをセット
    $('#reservationModalLabel').text(RESERVATION_MODAL_TITLE.EDIT);
    $('#saveBtn').text(RESERVATION_MODAL_SAVE_BTN.EDIT);
    $('#meetingId').val(meetingId);

    // 新規会議予約を行う場合はモーダルタイトル、ボタンを新規登録のものに置き換える
    if (meetingId === ADD_RESERVATION) {
        $('#reservationModalLabel').text(RESERVATION_MODAL_TITLE.ADD);
        $('#saveBtn').text(RESERVATION_MODAL_SAVE_BTN.ADD);
    }
    // 会議室予約モーダル表示
    $('#reservationModal').modal('show');
}

/**
 * 対象の会議予約一覧取得
 *
 * @return {any}
 */
function filterMeetingReservationList() {
    return new Promise(function(resolve, reject) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'reservation/search',
            type:'get',
            cache: false,
            dataType:'json',
            data: getSearchData(),
            success: function(data){
                resolve(data);
            },
            error: function(error){　
                console.log(error);
                // ロード終了
                removeLoading();
            }
        });
    });
}

/**
 * 検索データを取得
 *
 * @return {object}
 */
function getSearchData() {
    return {
        targetDate: $('#searchDate').val(),
        targetRepresentativeId: $('#inputMeetingRepresentative').val(),
        targetMemberId: $('#inputMeetingMember').val(),
    };
}

/**
 * 年月日を取得
 *
 * @param {object} date
 * @return {string}
 */
function getYmd(date) {
    let year = date.getFullYear();
    let month = ("00" + (date.getMonth() + 1)).slice(-2);
    let day = ("00" + date.getDate()).slice(-2);

    return year + '/' + month + '/' + day;
}

/**
 * 曜日を取得する
 *
 * @param {string} ymd
 * @return {string}
 */
function getWeekday(ymd) {
    var date = new Date (ymd) ;
    return WEEKDAY[date.getDay()];
}

/**
 * 会議予約表示対象日をセット
 *
 * @param {string} ymd
 * @return {void}
 */
function setTargetDay(ymd) {
    $('#targetDay').text((ymd.replace(/-/g, '/')) + '(' + getWeekday(ymd) + ')');
    $('#searchDate').val(ymd.replace(/\//g, '-'));
    $('#inputMeetingDate').val(ymd.replace(/\//g, '-'));
}

/**
 * 「<」ボタン押下処理
 *
 * @return {void}
 */
function onPrevDay() {
    // ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }

    // 現在表示している会議予約情報を1日前の情報に変更
    changeDay(CHANGE_DAY.PREV);
    // 会議予約情報をリロード
    reload();
}

/**
 * 「>」ボタン押下処理
 *
 * @return {void}
 */
function onNextDay() {
    // ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }

    // 現在表示している会議予約情報を1日後の情報に変更
    changeDay(CHANGE_DAY.NEXT);
    // 会議予約情報をリロード
    reload();
}

/**
 * 現在表示している会議予約対象日を変更
 *
 * @param {number} changeDay
 * @return {void}
 */
function changeDay(changeDay) {
    let date = new Date($('#targetDay').text().replace(/\//g, '-'));
    // 現在の年月日を変更
    date.setDate(date.getDate() + changeDay);

    // 変更した年月日をセットする
    setTargetDay(getYmd(date));
}

/**
 * 変更した検索年月日をセット
 *
 * @return {void}
 */
function onSearch() {
    // ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }

    // 検索対象日が入力されていない場合は本日の年月日をセット
    let changeDay = ($('#inputMeetingDate').val() !== '') ? $('#inputMeetingDate').val() : getYmd(new Date());
    setTargetDay(changeDay);

    // 会議予約情報をリロード
    reload();
}