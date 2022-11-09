/**
 * 初期時間
 *
 * @var {string}
 */
define('INIT_TIME', '00');

/**
 * リマインド日（時）
 *
 * @var {string}
 */
define('REMIND_HOUR_DAY', '10');

/**
 * リマインド日（分）
 *
 * @var {string}
 */
define('REMIND_MINUTE_DAY', '00');

/**
 * チェックリマインド日
 *
 * @var {object}
 */
define('CHECK_REMIND_DAY', {
    THREE_DAYS_AGO: 3,
    TWO_DAYS_AGO: 2,
    PREV_DAYS_AGO: 1,
    CURRENT_DAY: 0
});

/**
 * チェックリマインド時間
 *
 * @var {object}
 */
define('CHECK_REMIND_TIME', {
    CURRENT_HOUR: 0,
    CURRENT_MINUTE: 0,
    ONE_HOURS_AGO: 1,
    HALF_AN_HOUR_AGO: 30,
    TEN_MINUTES_AGO: 10,
    FIVE_MINUTE_AGO: 5
});

/**
 * チェックタイプ
 *
 * @var {object}
 */
define('CHECK_TYPE', {
    REMIND: '.checkRemind',
    REPEAT: '.checkRepeat'
});

/**
 * オプション（日）
 *
 * @var {array}
 */
define('OPTION_DAYS', [
    {text: '1日', value: '01'},
    {text: '2日', value: '02'},
    {text: '3日', value: '03'},
    {text: '4日', value: '04'},
    {text: '5日', value: '05'},
    {text: '6日', value: '06'},
    {text: '7日', value: '07'},
    {text: '8日', value: '08'},
    {text: '9日', value: '09'},
    {text: '10日', value: '10'},
    {text: '11日', value: '11'},
    {text: '12日', value: '12'},
    {text: '13日', value: '13'},
    {text: '14日', value: '14'},
    {text: '15日', value: '15'},
    {text: '16日', value: '16'},
    {text: '17日', value: '17'},
    {text: '18日', value: '18'},
    {text: '19日', value: '19'},
    {text: '20日', value: '20'},
    {text: '21日', value: '21'},
    {text: '22日', value: '22'},
    {text: '23日', value: '23'},
    {text: '24日', value: '24'},
    {text: '25日', value: '25'},
    {text: '26日', value: '26'},
    {text: '27日', value: '27'},
    {text: '28日', value: '28'},
    {text: '29日', value: '29'},
    {text: '30日', value: '30'},
    {text: '31日', value: '31'}
]);

/**
 * オプション（時）
 *
 * @var {array}
 */
define('OPTION_HOURS', [
    {text: '0時', value: '00'},
    {text: '1時', value: '01'},
    {text: '2時', value: '02'},
    {text: '3時', value: '03'},
    {text: '4時', value: '04'},
    {text: '5時', value: '05'},
    {text: '6時', value: '06'},
    {text: '7時', value: '07'},
    {text: '8時', value: '08'},
    {text: '9時', value: '09'},
    {text: '10時', value: '10'},
    {text: '11時', value: '11'},
    {text: '12時', value: '12'},
    {text: '13時', value: '13'},
    {text: '14時', value: '14'},
    {text: '15時', value: '15'},
    {text: '16時', value: '16'},
    {text: '17時', value: '17'},
    {text: '18時', value: '18'},
    {text: '19時', value: '19'},
    {text: '20時', value: '20'},
    {text: '21時', value: '21'},
    {text: '22時', value: '22'},
    {text: '23時', value: '23'},
]);

/**
 * オプション（分）
 *
 * @var {array}
 */
define('OPTION_MINUTES', [
    {text: '00分', value: '00'},
    {text: '05分', value: '05'},
    {text: '10分', value: '10'},
    {text: '15分', value: '15'},
    {text: '20分', value: '20'},
    {text: '25分', value: '25'},
    {text: '30分', value: '30'},
    {text: '35分', value: '35'},
    {text: '40分', value: '40'},
    {text: '45分', value: '45'},
    {text: '50分', value: '50'},
    {text: '55分', value: '55'},
]);

$(document).ready(function() {

    // モーダル初期化処理
    modalInit()

    /**
     * モーダルを開いたタイミングで処理を実行
     */
    $('#reservationModal').on('show.bs.modal', function() {
        // ロード開始
        showLoading();
        // モーダルをリセット
        modalReset();

        // 編集の場合
        if (parseInt($('#meetingId').val()) !== ADD_RESERVATION) {
            // 削除ボタン表示
            $('#deleteBtn').show();

            // 会議予約詳細を取得
            getMeetingReservationDetail(parseInt($('#meetingId').val())).then(function(detailData) {
                // 会議予約詳細をセット
                setMeetingReservationDetail(detailData);
                // 選択した会議カラーをセット
                selectedColor();
                // 選択した会議メンバーをセット
                setMeetingMembers(detailData.members);
                // リマインド有無の切り替え
                changeRemind();
                // リマインド設定が可能かチェック
                CanRemindsSetting();
                // 任意リマインド設定の切り替え
                checkedOptionalRemind();
                // リピート有無の切り替え
                changeRepeat();
                // リピート日設定の切り替え
                checkedOptionalRepeatDay();
                // ロード終了
                removeLoading();
            })
        } else {
            // ロード終了
            removeLoading();
        }
    });
});

/**
 * モーダル初期化処理
 *
 * @return {any}
 */
function modalInit() {
    // ロード開始
    showLoading()
    // カラーをセットする
    setColors();
    // オプションの日にちをセット
    setOptionDays()
    // オプションの時間をセット
    setOptionHours();
    // オプションの分をセット
    setOptionMinutes();

    // 会議室予約設定に必要な情報を取得する
    getMeetingRoomsAndMembers().then(function(data) {
        // 取得したメンバーリストを会議担当者と会議対象者にセット
        setMemberList(data.meetingMember);
        // 取得した会議室リスト取得した
        setMeetingRoomList(data.meetingRooms);
        // ロード終了
        removeLoading();

    }).catch(function (error) {
        // ロード終了
        removeLoading();
    });
}

/**
 * モーダル初期化処理
 *
 * @return {any}
 */
function modalReset() {
    $('#deleteBtn').hide();
    $('#error_area').empty();
    $('#error_area').hide();
    $('#member_area').empty();
    $('#formMeetingTitle').val('');
    $('#formMeetingDate').val('');
    $('#formMeetingRoom').val('');
    $('#formMeetingRepresentative').val('');
    $('#formMeetingMemberList').val('');
    $('#formMeetingComment').val('');
    $('#formMeetingColor').val('');
    $('#formMeetingStartHour').val(INIT_TIME);
    $('#formMeetingEndHour').val(INIT_TIME);
    $('#formMeetingStartMinute').val(INIT_TIME);
    $('#formMeetingEndMinute').val(INIT_TIME);
    $('input:radio[name="formMeetingColor"]').val(['']);
    $('#selectedColor').css('background-color', '');
    $('#displayRemind').css('display', 'none');
    $('#formRemindDate').prop("disabled", true);
    $('#formRemindHour').prop("disabled", true);
    $('#formRemindMinute').prop("disabled", true);
    $('input[name=remind]').val(['false']);
    $('input[name=repeat]').val(['false']);
    $('input[name=slack]').val(['false']);
    $('#threeDaysAgo').prop("checked", false);
    $('#threeDaysAgo').prop("checked", false);
    $('#twoDaysAgo').prop("checked", false);
    $('#prevDaysAgo').prop("checked", false);
    $('#currentDay').prop("checked", false);
    $('#oneHourAgo').prop("checked", false);
    $('#halfAnHourAgo').prop("checked", false);
    $('#tenMinuteAgo').prop("checked", false);
    $('#optionalRemind').prop("checked", false);
    $('#formRemindDate').val('');
    $('#formRemindHour').val(INIT_TIME);
    $('#formRemindMinute').val(INIT_TIME);
    $('.displayRepeat').css('display', 'none');
    $('#repeatMonthFirst').prop("checked", false);
    $('#repeatMonthLast').prop("checked", false);
    $('#repeatMonday').prop("checked", false);
    $('#repeatTuesday').prop("checked", false);
    $('#repeatWednesday').prop("checked", false);
    $('#repeatThursday').prop("checked", false);
    $('#repeatFriday').prop("checked", false);
    $('#repeatSaturday').prop("checked", false);
    $('#repeatSunday').prop("checked", false);
    $('#optionalRepeatDay').prop("checked", false);
    $('#formRepeatDateStart').val('');
    $('#formRepeatDateEnd').val('');
    $('#formMeetingRepeatList').prop("disabled", true);
    $('#repeat_area').css({backgroundColor: '#e9ecef', cursor: 'not-allowed'});
    $('#repeat_area').empty();
}

/**
 * 会議室カラー情報をセット
 *
 * @return {void}
 */
function setColors() {
    // 色の数だけループし追加する
    for (var color of COLORS) {
        $('#color_area').append(''
            + '<input type="radio" name="formMeetingColor" id="' + color.id + '" class="form-control" value="' + color.colorCode + '" onchange="selectedColor()">'
            + '<label for="' + color.id + '" class="rm_color_label" style="background-color: ' + color.colorCode + ';"></label>'
        );
    }
}

/**
 * 会議室と会議メンバーの情報を取得
 *
 * @return {any}
 */
function getMeetingRoomsAndMembers() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url:'reservation/modal',
            type:'get',
            cache: false,
            dataType:'json',
            success: function(data){
                resolve(data);
            },
            error: function(error){　
                reject(error);
            }
        });
    });
}

/**
 * 会議室リストをセット
 *
 * @param {object} meetingRoomList
 * @return {void}
 */
function setMeetingRoomList(meetingRoomList) {
    // 登録されている会議室の数だけループ
    for (var meetingRoom of meetingRoomList) {
        // 会議室情報をセット
        $('#formMeetingRoom').append('<option value="'+ meetingRoom.id +'">'+ meetingRoom.name +'</option>');
    }
}

/**
 * メンバーリストを会議担当者と会議対象者にセット
 *
 * @param {object} memberList
 * @return {void}
 */
function setMemberList(memberList) {
    // 登録されているメンバーの数だけループ
    for (var member of memberList) {
        // 会議担当者と会議対象者にメンバー情報をセット
        $('#formMeetingRepresentative').append('<option value="'+ member.id +'">'+ member.name +'</option>');
        $('#formMeetingMemberList').append('<option value="'+ member.id +'">'+ member.name +'</option>');
    }
}

/**
 * オプションの日にちをセット
 *
 * @return {void}
 */
function setOptionDays() {
    // 日にちの数だけループ
    for (var day of OPTION_DAYS) {
        // 日にちをセット
        $('#formMeetingRepeatList').append('<option value="'+ day.value +'">'+ day.text +'</option>');
    }
}

/**
 * オプションの時間をセット
 *
 * @return {void}
 */
function setOptionHours() {
    // 時間の数だけループ
    for (var hour of OPTION_HOURS) {
        // 時間をセット
        $('#formMeetingStartHour').append('<option value="'+ hour.value +'">'+ hour.text +'</option>');
        $('#formMeetingEndHour').append('<option value="'+ hour.value +'">'+ hour.text +'</option>');
        $('#formRemindHour').append('<option value="'+ hour.value +'">'+ hour.text +'</option>');
    }
}

/**
 * オプションの分をセット
 *
 * @return {void}
 */
function setOptionMinutes() {
    // 時間の数だけループ
    for (var minute of OPTION_MINUTES) {
        // 時間をセット
        $('#formMeetingStartMinute').append('<option value="'+ minute.value +'">'+ minute.text +'</option>');
        $('#formMeetingEndMinute').append('<option value="'+ minute.value +'">'+ minute.text +'</option>');
        $('#formRemindMinute').append('<option value="'+ minute.value +'">'+ minute.text +'</option>');
    }
}

/**
 * 会議メンバー削除処理
 *
 * @param {any} params
 * @return {void}
 */
function onMemberDelete(params) {
    params.srcElement.parentNode.remove();
}

/**
 * リピート日削除処理
 *
 * @param {any} params
 * @return {void}
 */
function onMeetingRepeatDelete(params) {
    params.srcElement.parentNode.remove();
}

/**
 * 会議メンバー選択時の処理
 *
 * @param {any} params
 * @return {void}
 */
function selectedMeetingMember(params) {
    // 選択したメンバーID、メンバー名を格納
    let index = params.target.selectedIndex;
    let memberId = parseInt(params.target.value);
    let memberName = params.target.options[index].innerText;

    // 会議メンバーを追加
    insertMeetingMemberSelection(memberId, memberName);
}

/**
 * 会議メンバーを追加
 *
 * @param {number} memberId
 * @param {string} memberName
 */
function insertMeetingMemberSelection(memberId, memberName) {
    // 選択したメンバーを追加
    $('#member_area').append(''
        + '<div class="rm_member" data-memberid="' + memberId + '">' + memberName
        + '<div class="rm_member_delete" onclick="onMemberDelete(event)">×</div></div>'
    );
    // 会議対象者選択プルダウン初期化
    $('#formMeetingMemberList').val('');
}

/**
 * リピート日選択時の処理
 *
 * @param {any} params
 * @return {void}
 */
function selectedMeetingRepeat(params) {
    // 選択したリピート日を格納
    let index = params.target.selectedIndex;
    let repeatDay = params.target.value;
    let repeatDayName = params.target.options[index].innerText;

    // リピート日を追加
    insertMeetingRepeatSelection(repeatDay, repeatDayName);
}

/**
 * リピート日を追加
 *
 * @param {number} repeatDay
 * @param {string} repeatDayName
 */
function insertMeetingRepeatSelection(repeatDay, repeatDayName) {
    // 選択したリピート日を追加
    $('#repeat_area').append(''
        + '<div class="rm_repeat_day" data-repeat_day="' + repeatDay + '">' + repeatDayName
        + '<div class="rm_repeat_day_delete" onclick="onMeetingRepeatDelete(event)">×</div></div>'
    );
    // リピート日選択プルダウン初期化
    $('#formMeetingRepeatList').val('');
}

/**
 * 会議カラー選択時の処理
 *
 * @return {void}
 */
function selectedColor() {
    // 選択したカラー情報を取得
    let colorCode = $('input:radio[name="formMeetingColor"]:checked').val();
    // 選択カラーの色を変える
    $('#selectedColor').css('background-color', colorCode);
}

/**
 * 会議室予約登録/更新ボタン押下時の処理
 *
 * @return {void}
 */
function onSaveBtn() {

    // ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }

    // ロード開始
    showLoading();

    // バリデーションチェックを行う
    let errorMessage = validation();

    // エラーが存在する場合
    if (errorMessage.length > 0) {
        // エラー内容を出力する
        displayErrorMessage(errorMessage);
        // ロード終了
        removeLoading();
        return;
    }

    // 会議室予約新規登録の場合
    if (parseInt($('#meetingId').val()) === ADD_RESERVATION) {
        // 会議室予約新規登録処理
        addMeetingReservation().then(function() {
            // 会議室予約モーダルを閉じる
            $('#reservationModal').modal('hide');
        });

    // 会議室予約更新の場合
    } else {
        // 会議室予約更新処理
        updateMeetingReservation().then(function() {
            // 会議室予約モーダルを閉じる
            $('#reservationModal').modal('hide');
        });
    }
}

/**
 * 削除ボタン押下処理
 *
 * @return {void}
 */
function onDeleteBtn() {
    // ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }

    // ユーザーに会議予約を削除してよいかチェック
    if (window.confirm("対象の会議予約を削除してよろしいでしょうか？")) {
        // ロード開始
        showLoading();
        // 会議予約削除
        deleteMeetingReservation().then(function() {
            // 会議室予約モーダルを閉じる
            $('#reservationModal').modal('hide');
        });
    }
}

/**
 * 必須項目のバリデーションチェックを行う
 *
 * @return {array}
 */
function validation() {
    let errorMessage = [];
    let isStartDateFurture = true;

    // エラーメッセージリセット、非表示
    $('#error_area').empty();
    $('#error_area').hide();

    // 会議タイトル未入力
    if ($('#formMeetingTitle').val() === '') {
        errorMessage.push('会議タイトルが未設定です。');
    }

    // 会議室未設定
    if ($('#formMeetingRoom').val() === '') {
        errorMessage.push('会議室が未設定です。');
    }

    // 会議担当者未設定
    if ($('#formMeetingRepresentative').val() === '') {
        errorMessage.push('会議担当者が未設定です。');
    }

    // 会議対象者未設定
    if ($('#member_area').find('.rm_member').length === 0) {
        errorMessage.push('会議対象者が未設定です。');
    }

    // 会議日未設定
    if ($('#formMeetingDate').val() === '') {
        errorMessage.push('会議日が未設定です。');

    } else {
        // 会議開始日時が現時刻より未来の日付かどうか
        if (!checkFutureDateTime(formatDateTime($('#formMeetingDate').val(), $('#formMeetingStartHour').val(), $('#formMeetingStartMinute').val()))) {
            isStartDateFurture = false;
            errorMessage.push('会議開始日時は現日時以降で設定してください。');
        }

        // 会議開始時間が会議終了時刻より前かどうか
        if (isStartDateFurture && !checkStartDateTimeWithThanEndDateTime(
            formatDateTime($('#formMeetingDate').val(), $('#formMeetingStartHour').val(), $('#formMeetingStartMinute').val()),
            formatDateTime($('#formMeetingDate').val(), $('#formMeetingEndHour').val(), $('#formMeetingEndMinute').val())
        )) {
            errorMessage.push('会議終了日時は会議開始日時以降の日時で設定してください。');
        }
    }

    // 会議カラー未設定
    if ($('input:radio[name="formMeetingColor"]:checked').val() === undefined) {
        errorMessage.push('会議カラーが未設定です。');
    }

    // リマインド設定を行う場合はリマインド設定のバリデーションを行う
    if ($('#isRemind').prop("checked")) {
        errorMessage = validationRemind(errorMessage);
    }

    // リピート設定を行う場合はリピート設定のバリデーションを行う
    if ($('#isRepeat').prop("checked")) {
        errorMessage = validationRepeat(errorMessage);
    }
    return errorMessage;
}

/**
 * リマインド設定のバリデーションを行う
 *
 * @param {array} errorMessage
 * @return {array}
 */
function validationRemind(errorMessage) {
    let isSetOptionalRemind = true;
    let isSetOptionalRemindFuture = true;

    // リマインド設定に1つでもチェックがついているかどうかチェック
    if (!checkSelection(CHECK_TYPE.REMIND)) {
        errorMessage.push('リマインド設定は1つ以上設定してください。');
    }

    // 任意リマインド設定にチェックがついている場合
    if ($('#optionalRemind').prop("checked")) {
        // リマインド年月日が設定されていない場合
        if ($('#formRemindDate').val() === '') {
            errorMessage.push('リマインド年月日が未設定です。');
            isSetOptionalRemind = false;
        }

        // リマインド設定に不備がない場合はリマインド日時が現時刻より未来の日付かどうかチェック
        if (isSetOptionalRemind && !checkFutureDateTime(formatDateTime($('#formRemindDate').val(), $('#formRemindHour').val(), $('#formRemindMinute').val()))) {
            errorMessage.push('リマインド日時は現日時以降で設定してください。');
            isSetOptionalRemindFuture = false
        }

        // リマインド設定に不備がない場合はリマインド日時より前の日時かどうかチェック
        if (isSetOptionalRemind &&  isSetOptionalRemindFuture
            && !checkBeforeMeetingStartDateTime(
                formatDateTime($('#formMeetingDate').val(), $('#formMeetingStartHour').val(), $('#formMeetingStartMinute').val()),
                formatDateTime($('#formRemindDate').val(), $('#formRemindHour').val(), $('#formRemindMinute').val()))
        ) {
            errorMessage.push('リマインド日時は会議開始日時以前で設定してください。');
        }
    }
    return errorMessage;
}

/**
 * リピート設定のバリデーションを行う
 *
 * @param {array} errorMessage
 * @return {array}
 */
function validationRepeat(errorMessage) {
    let settingRepeat = true;
    let isStartRepeatFurture = true;
    let isMeetingDateFurture = true;

    // リピート設定に1つでもチェックがついているかどうかチェック
    if (!checkSelection(CHECK_TYPE.REPEAT)) {
        errorMessage.push('リピート設定は1つ以上設定してください。');
    }

    // リピート開始日未設定
    if ($('#formRepeatDateStart').val() === '') {
        errorMessage.push('リピート開始日が未設定です。');
        settingRepeat = false;
    }

    // リピート終了日未設定
    if ($('#formRepeatDateEnd').val() === '') {
        errorMessage.push('リピート終了日が未設定です。');
        settingRepeat = false;
    }

    // リピートが設定されている場合
    if (settingRepeat) {
        // リピート開始日時が現時刻より未来の日付かどうか
        if (!checkFutureDateTime(formatDateTime($('#formRepeatDateStart').val(), INIT_TIME, INIT_TIME))) {
            isStartRepeatFurture = false;
            errorMessage.push('リピート開始日は未来の日付で設定してください。');
        }

        // 会議日がリピート開始日より前かどうか
        if (isStartRepeatFurture && !checkStartDateTimeWithThanEndDateTime(
            formatDateTime($('#formMeetingDate').val(), INIT_TIME, INIT_TIME),
            formatDateTime($('#formRepeatDateStart').val(), INIT_TIME, INIT_TIME)
        )) {
            isMeetingDateFurture = false;
            errorMessage.push('リピート開始日は会議日以降の日付で設定してください。');
        }

        // リピート開始時間がリピート終了時刻より前かどうか
        if (isStartRepeatFurture && isMeetingDateFurture && !checkStartDateTimeWithThanEndDateTime(
            formatDateTime($('#formRepeatDateStart').val(), INIT_TIME, INIT_TIME),
            formatDateTime($('#formRepeatDateEnd').val(), INIT_TIME, INIT_TIME)
        )) {
            errorMessage.push('リピート終了日はリピート開始日以降の日付で設定してください。');
        }
    }

    // リピート日設定にチェックがついている場合
    if ($('#optionalRepeatDay').prop("checked") && $('#repeat_area').find('.rm_repeat_day').length === 0) {
        errorMessage.push('リピート日を設定してください。');
    }
    return errorMessage;
}

/**
 * リマインド設定の選択状況を確認
 *
 * @param {string}
 * @return {boolean}
 */
function checkSelection(className) {
    let isSetSelection = false;

    // リマインド設定項目の数だけループ
    $(className).each(function(index, element) {
        // チェックがONの場合は処理を抜ける
        if ($(element).prop("checked")) {
            isSetSelection = true;
            return false;
        }
    });
    return isSetSelection;
}

/**
 * 現日時と設定日時を比較し、未来の日付で設定されているかをチェック
 *
 * @param {string} comparison
 * @return {boolean}
 */
function checkFutureDateTime(comparison) {
    let nowDate = new Date();
    let comparisonDate = new Date(comparison);

    return ((comparisonDate.getTime() - nowDate.getTime()) > 0) ? true : false;
}

/**
 * 会議開始日時と設定日時を比較し、会議開始以前で設定されているかをチェック
 *
 * @param {string} start
 * @param {string} comparison
 * @return {boolean}
 */
 function checkBeforeMeetingStartDateTime(start, comparison) {
    let startDate = new Date(start);
    let comparisonDate = new Date(comparison);

    return ((comparisonDate.getTime() < startDate.getTime())) ? true : false;
}

/**
 * 開始日時とより終了日時の方が遅く設定されているかどうかをチェック
 *
 * @param {string} start
 * @param {string} end
 * @return {boolean}
 */
function checkStartDateTimeWithThanEndDateTime(start, end) {
    let startDate = new Date(start);
    let endDate = new Date(end);

    return (startDate.getTime() < endDate.getTime()) ? true : false;
}

/**
 * エラーメッセージ表示
 *
 * @param {array} errorMessage
 */
function displayErrorMessage(errorMessage) {
    // エラーの数だけループ
    for (var error of errorMessage) {
        // エラー追加
        $('#error_area').append('<div>' + error + '</div>');
    }
    // エラー表示
    $('#error_area').show();
}

/**
 * サーバーエラーメッセージ表示
 *
 * @param {array} errors
 */
function displayBackErrorMessage(errors) {
    // エラーの数だけループ
    for (var errorMessage in errors) {
        for (var error of errors[errorMessage]) {
            // エラー追加
            $('#error_area').append('<div>' + error + '</div>');
        }
    }
    // エラー表示
    $('#error_area').show();
}

/**
 * 会議予約新規登録処理
 *
 * @return {any}
 */
function addMeetingReservation() {
    return new Promise(function(resolve, reject) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'reservation',
            type:'post',
            cache: false,
            dataType:'json',
            data: getSendData(),
            success: function(data){
                // ロード終了
                removeLoading();
                resolve(data);
            },
            error: function(error){　
                displayBackErrorMessage(error.responseJSON.errors);
                // ロード終了
                removeLoading();
            }
        });
    });
}

/**
 * 会議予約更新処理
 *
 * @return {any}
 */
function updateMeetingReservation() {
    return new Promise(function(resolve, reject) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'reservation/',
            type:'put',
            cache: false,
            dataType:'json',
            data: getSendData(),
            success: function(data){
                // ロード終了
                removeLoading();
                resolve(data);
            },
            error: function(error){　
                displayBackErrorMessage(error.responseJSON.errors);
                // ロード終了
                removeLoading();
            }
        });
    });
}

/**
 * 会議予約削除処理
 *
 * @return {any}
 */
function deleteMeetingReservation() {
    return new Promise(function(resolve, reject) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'reservation/',
            type:'delete',
            cache: false,
            dataType:'json',
            data: {reservationId: $('#meetingId').val()},
            success: function(data){
                // ロード終了
                removeLoading();
                resolve(data);
            },
            error: function(error){　
                displayBackErrorMessage(error.responseJSON.errors);
                // ロード終了
                removeLoading();
            }
        });
    });
}

/**
 * 会議予約詳細を取得
 *
 * @param {number} reservationId
 */
function getMeetingReservationDetail(reservationId) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url:'reservation/detail',
            type:'get',
            cache: false,
            dataType:'json',
            data: {reservationId: reservationId},
            success: function(data){
                resolve(data);
            },
            error: function(error){　
                displayBackErrorMessage(error.responseJSON.errors);
                // ロード終了
                removeLoading();
            }
        });
    });
}

/**
 * サーバーに送るデータを生成する
 *
 * @return {object}
 */
function getSendData() {
    return {
        reservationId: $('#meetingId').val(),
        organizerId: convertNumber($('#formMeetingRepresentative').val()),
        meetingRoomId: convertNumber($('#formMeetingRoom').val()),
        title: $('#formMeetingTitle').val(),
        meetingDate: $('#formMeetingDate').val(),
        startTime: formatTime($('#formMeetingStartHour').val(), $('#formMeetingStartMinute').val()),
        endTime: formatTime($('#formMeetingEndHour').val(), $('#formMeetingEndMinute').val()),
        startDataTime: formatDateTime($('#formMeetingDate').val(), $('#formMeetingStartHour').val(), $('#formMeetingStartMinute').val()),
        endDateTime: formatDateTime($('#formMeetingDate').val(), $('#formMeetingEndHour').val(), $('#formMeetingEndMinute').val()),
        comment: $('#formMeetingComment').val(),
        isRemind: $('#isRemind').prop("checked"),
        isRepeat: $('#isRepeat').prop("checked"),
        isSlack: $('#isSlack').prop("checked"),
        color: $('input:radio[name="formMeetingColor"]:checked').val(),
        meetingMembers: getMeetingMemberSelecotions(),
        remind: getRemindSelecotions(),
        repeat: getRepeatSelecotions(),
    };
}

/**
 * 選択したミーティングメンバー情報を生成する
 *
 * @return {object}
 */
function getMeetingMemberSelecotions() {
    // 会議担当者を初期値として格納
    let meetingMembers = [{
        memberId: convertNumber($('#formMeetingRepresentative').val()),
    }];

    // 選択したミーティングメンバーの数だけループ
    $(".rm_member").each(function(i, element) {
        // 会議メンバーを重複していなければ追加する
        if (!checkDuplicationMeetingMember(meetingMembers, convertNumber($(element).data('memberid')))) {
            meetingMembers.push({
                memberId: convertNumber($(element).data('memberid')),
            });
        }
    });
    return meetingMembers;
}

 /**
  * 選択したミーティングメンバー情報を生成する
  *
  * @param {*} meetingMembers
  * @param {*} memberId
  * @return {boolean}
  */
function checkDuplicationMeetingMember(meetingMembers, memberId) {
    let duplication = false;

    // 既に追加したメンバーの数だけループ
    for (var member of meetingMembers) {
        // 既に追加しているメンバーの場合は処理を抜ける
        if (member.memberId === memberId) {
            duplication = true;
            break;
        }
    }
    return duplication;
}

/**
 * リマインド設定を返す
 *
 * @return {object}
 */
function getRemindSelecotions() {
    // リマインド設定ありの場合はリマインド設定データを返す
    if ($('#isRemind').prop("checked")) {
        return includeRemind();
    } else {
        return withoutRemind();
    }
}


/**
 * リピート設定を返す
 *
 * @return {object}
 */
function getRepeatSelecotions() {
    // リピート設定ありの場合はリピート設定データを返す
    if ($('#isRepeat').prop("checked")) {
        return includeRepeat();
    } else {
        return withoutRepeat();
    }
}

/**
 * リマインド設定を含む
 *
 * @return {object}
 */
function includeRemind() {
    return {
        isThreeDaysAgo: $('#threeDaysAgo').prop("checked"),
        isTwoDaysAgo: $('#twoDaysAgo').prop("checked"),
        isPrevDaysAgo: $('#prevDaysAgo').prop("checked"),
        isCurrentDay: $('#currentDay').prop("checked"),
        isOneHourAgo: $('#oneHourAgo').prop("checked"),
        isHalfAnHourAgo: $('#halfAnHourAgo').prop("checked"),
        isTenMinuteAgo: $('#tenMinuteAgo').prop("checked"),
        isOptionalRemind: $('#optionalRemind').prop("checked"),
        optionalRemindDate: getOptionalRemindDate()
    }
}

/**
 * リマインド設定を外す
 *
 * @return {object}
 */
function withoutRemind() {
    return {
        isThreeDaysAgo: false,
        isTwoDaysAgo: false,
        isPrevDaysAgo: false,
        isCurrentDay: false,
        isOneHourAgo: false,
        isHalfAnHourAgo: false,
        isTenMinuteAgo: false,
        isOptionalRemind: false,
        optionalRemindDate: null,
    }
}

/**
 * 任意リマインド設定日時を取得
 *
 * @return {string|mull}
 */
function getOptionalRemindDate() {
    let optionalRemindDate = null;

    // 任意リマインド設定にチェックがついている場合
    if ($('#optionalRemind').prop("checked")) {
        optionalRemindDate = formatDateTime($('#formRemindDate').val(), $('#formRemindHour').val(), $('#formRemindMinute').val());
    }
    return optionalRemindDate;
}

/**
 * リピート設定を含む
 *
 * @return {object}
 */
function includeRepeat() {
    return {
        start: $('#formRepeatDateStart').val(),
        end: $('#formRepeatDateEnd').val(),
        isMonthFirst: $('#repeatMonthFirst').prop("checked"),
        isMonthLast: $('#repeatMonthLast').prop("checked"),
        isMonday: $('#repeatMonday').prop("checked"),
        isTuesday: $('#repeatTuesday').prop("checked"),
        isWednesday: $('#repeatWednesday').prop("checked"),
        isThursday: $('#repeatThursday').prop("checked"),
        isFriday: $('#repeatFriday').prop("checked"),
        isOptionalRepeat: $('#optionalRepeatDay').prop("checked"),
        optionalRepeats: getMeetingRepeatSelecotions()
    }
}

/**
 * リピート設定を外す
 *
 * @return {object}
 */
function withoutRepeat() {
    return {
        start: null,
        end: null,
        isMonthFirst: false,
        isMonthLast: false,
        isMonday: false,
        isTuesday: false,
        isWednesday: false,
        isThursday: false,
        isFriday: false,
        isoptionalRepeat: false,
        optionalRepeats: []
    }
}

/**
 * 選択したリピート日を生成する
 *
 * @return {object}
 */
function getMeetingRepeatSelecotions() {
    let repeatDays = [];

    // 選択したリピート日の数だけループ
    $(".rm_repeat_day").each(function(i, element) {
        // リピート日を重複していなければ追加する
        if (!checkDuplicationMeetingRepeat(repeatDays, $(element).data('repeat_day'))) {
            repeatDays.push({
                repeatDay: $(element).data('repeat_day'),
            });
        }
    });
    return repeatDays;
}

 /**
  * 選択したリピート日情報を生成する
  *
  * @param {array} repeatDays
  * @param {string} targetDay
  * @return {boolean}
  */
function checkDuplicationMeetingRepeat(repeatDays, targetDay) {
    let duplication = false;

    // 既に追加したリピート日数だけループ
    for (var repeat of repeatDays) {
        // 既に追加しているリピート日の場合は処理を抜ける
        if (repeat.repeatDay === targetDay) {
            duplication = true;
            break;
        }
    }
    return duplication;
}

/**
 * 日付フォーマットに整形する
 *
 * @param {string} date
 * @param {string} hour
 * @param {string} minute
 * @return {string}
 */
function formatDateTime(date, hour, minute) {
    return date + ' ' + hour + ':' + minute + ':' + INIT_TIME;
}

/**
 * 時間フォーマットに整形する
 *
 * @param {string} hour
 * @param {string} minute
 * @return {string}
 */
function formatTime(hour, minute) {
    return hour + ':' + minute + ':' + INIT_TIME;
}

/**
 * 会議予約詳細をセット
 *
 * @param {object} detail
 */
function setMeetingReservationDetail(detail) {
    // 設定した値をセット
    $('#formMeetingRepresentative').val(detail.organizerId);
    $('#formMeetingRoom').val(detail.meetingRoomId);
    $('#formMeetingTitle').val(detail.title);
    $('#formMeetingDate').val(detail.date);
    $('#formMeetingStartHour').val(detail.startHour);
    $('#formMeetingStartMinute').val(detail.startMinute);
    $('#formMeetingEndHour').val(detail.endHour);
    $('#formMeetingEndMinute').val(detail.endMinute);
    $('#formMeetingComment').val(detail.comment);
    $('input[name=formMeetingColor]').val([detail.color]);
    $('#isRemind').prop("checked", detail.isRemind)
    $('#threeDaysAgo').prop("checked", detail.isThreeDaysAgo),
    $('#twoDaysAgo').prop("checked", detail.isTwoDaysAgo),
    $('#prevDaysAgo').prop("checked", detail.isPrevDaysAgo),
    $('#currentDay').prop("checked", detail.isCurrentDay),
    $('#oneHourAgo').prop("checked", detail.isOneHourAgo),
    $('#halfAnHourAgo').prop("checked", detail.isHalfAnHourAgo),
    $('#tenMinuteAgo').prop("checked", detail.isTenMinuteAgo),
    $('#optionalRemind').prop("checked", detail.isOptional),
    $('#formRemindDate').val(detail.optionalRemindDate),
    $('#formRemindHour').val(detail.optionalRemindHour),
    $('#formRemindMinute').val(detail.optionalRemindMinute)
}

/**
 * 会議メンバーをセット
 *
 * @param {object} meetingMembers
 */
function setMeetingMembers(meetingMembers) {
    // 会議メンバーの数だけループ
    for (member of meetingMembers) {
        // 会議メンバーを追加
        insertMeetingMemberSelection(member.memberId, member.memberName);
    }
}

/**
 * リマインド設定切り替え処理
 *
 * @param {any} params
 */
function changeRemind(params) {
    // リマインド設定ありの場合はリマインド設定項目を表示
    if ($('#isRemind').prop("checked")) {
        $('#displayRemind').css('display', 'table-row');
    } else {
        $('#displayRemind').css('display', 'none');
    }
}

/**
 * リマインド設定切り替え処理
 *
 * @param {any} params
 */
function changeRepeat(params) {
    // リマインド設定ありの場合はリマインド設定項目を表示
    if ($('#isRepeat').prop("checked")) {
        $('.displayRepeat').css('display', 'table-row');
    } else {
        $('.displayRepeat').css('display', 'none');
    }
}

/**
 * 任意リマインド設定チェック処理
 *
 * @return {void}
 */
function checkedOptionalRemind() {
    // 任意リマインド設定がONの場合は任意設定項目活性
    if ($('#optionalRemind').prop("checked")) {
        $('#formRemindDate').prop("disabled", false);
        $('#formRemindHour').prop("disabled", false);
        $('#formRemindMinute').prop("disabled", false);

    // 任意リマインド設定がOFFの場合は任意設定項目非活性
    } else {
        $('#formRemindDate').prop("disabled", true);
        $('#formRemindHour').prop("disabled", true);
        $('#formRemindMinute').prop("disabled", true);
    }
}

/**
 * リマインド設定が可能かチェック
 *
 * @return {void}
 */
function CanRemindsSetting() {
    let displayAlertArray = [];

    // 各項目のリマインド設定が可能かチェック
    displayAlertArray.push(CanRemindSetting(checkRemindDay(CHECK_REMIND_DAY.THREE_DAYS_AGO), '#threeDaysAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindDay(CHECK_REMIND_DAY.TWO_DAYS_AGO), '#twoDaysAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindDay(CHECK_REMIND_DAY.PREV_DAYS_AGO), '#prevDaysAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindDay(CHECK_REMIND_DAY.CURRENT_DAY), '#currentDay'));
    displayAlertArray.push(CanRemindSetting(checkRemindTime(CHECK_REMIND_TIME.ONE_HOURS_AGO, CHECK_REMIND_TIME.CURRENT_MINUTE), '#oneHourAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindTime(CHECK_REMIND_TIME.CURRENT_HOUR, CHECK_REMIND_TIME.HALF_AN_HOUR_AGO), '#halfAnHourAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindTime(CHECK_REMIND_TIME.CURRENT_HOUR, CHECK_REMIND_TIME.TEN_MINUTES_AGO), '#tenMinuteAgo'));
    displayAlertArray.push(CanRemindSetting(checkRemindTime(CHECK_REMIND_TIME.CURRENT_HOUR, CHECK_REMIND_TIME.FIVE_MINUTE_AGO), '#optionalRemind'));

    // リマインド不可に設定がされている場合はアラートを表示
    return displayAlertArray.find(function(element) {
        return element;
    });
}

/**
 * リマインド設定が可能かチェック
 *
 * @return {void}
 */
function CanRemindSetting(chackRemind, idName) {
    // リマインド設定がされているかつ、リマインド設定が不可な場合はtrue
    let checkedRemind = ($(idName).prop("checked") && !chackRemind) ? true : false;

    // リマインド設定が可能な場合は活性、不可能な場合は非活性にしチェックを外す
    if (chackRemind) {
        $(idName).prop("disabled", false);
    } else {
        $(idName).prop("disabled", true);
        $(idName).prop("checked", false);
    }
    return checkedRemind;
}

/**
 * 簡易リマインド設定（日）の設定が可能かチェック
 *
 * @param {number} day
 * @return {boolean}
 */
function checkRemindDay(day) {

    // 会議日が設定されていない場合
    if ($('#formMeetingDate').val() === '') {
        return false;
    }
    let nowDate = new Date();
    let nowDateTime = nowDate.getTime();
    let remindDate = new Date(formatDateTime(
        $('#formMeetingDate').val(),
        REMIND_HOUR_DAY,
        REMIND_MINUTE_DAY
    ));
    // 日にちを指定した時間にセットする
    remindDate.setDate(remindDate.getDate() - day);
    // 現在の時間がリマインド日より前の場合
    return (nowDateTime <= remindDate.getTime()) ? true : false;
}

/**
 * 簡易リマインド設定（時間）の設定が可能かチェック
 *
 * @param {number} hour
 * @param {number} minute
 * @return {boolean}
 */
function checkRemindTime(hour, minute) {

    // 会議日が設定されていない場合
    if ($('#formMeetingDate').val() === '') {
        return false;
    }
    let nowDate = new Date();
    let nowDateTime = nowDate.getTime();
    let remindDate = new Date(formatDateTime(
        $('#formMeetingDate').val(),
        $('#formMeetingStartHour').val(),
        $('#formMeetingStartMinute').val()
    ));
    // 時、分を指定した時間にセットする
    remindDate.setHours(remindDate.getHours() - hour);
    remindDate.setMinutes(remindDate.getMinutes() - minute);

    // 現在の時間がリマインド時間より前の場合
    return (nowDateTime <= remindDate.getTime()) ? true : false;
}

/**
 * 会議日時が変更された場合
 *
 * @return {void}
 */
function changeMeetingDate() {
    // リマインド設定が可能かチェック
    let isSetRemind = CanRemindsSetting();
    // リマインド設定をしている場合はアラート表示
    if ($('#isRemind').prop("checked") && isSetRemind) {
        alert('リマインド設定が会議日時変更により、リマインドできないため設定が解除されました。');
    }
    // 任意リマインド設定の切り替え
    checkedOptionalRemind();
}

/**
 * リピート日設定チェック処理
 *
 * @return {void}
 */
function checkedOptionalRepeatDay() {
    // リピート日設定がONの場合は任意設定項目活性
    if ($('#optionalRepeatDay').prop("checked")) {
        $('#formMeetingRepeatList').prop("disabled", false);
        $('#repeat_area').css({backgroundColor: '#ffffff', cursor: 'auto'});

    // リピート日設定がOFFの場合は任意設定項目非活性
    } else {
        $('#formMeetingRepeatList').prop("disabled", true);
        $('#repeat_area').css({backgroundColor: '#e9ecef', cursor: 'not-allowed'});
        $('#repeat_area').empty();
    }
}