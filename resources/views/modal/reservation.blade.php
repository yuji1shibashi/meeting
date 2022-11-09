<link rel="stylesheet" href="{{ asset('css/reservationModal.css') }}">
<script src="{{ asset('/js/reservationModal.js') }}"></script>

<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reservationModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="meetingId" value=0>
        <table class="rm_reservation_table">
          <tr>
            <th>会議タイトル<span class="required">※</span>：</th>
            <td>
              <input type="text" id="formMeetingTitle" class="form-control" autocomplete="off">
            </td>
          </tr>
          <tr>
            <th>会議室<span class="required">※</span>：</th>
            <td>
              <select id="formMeetingRoom" class="form-control">
                <option value="">選択してください▼</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>会議主催者<span class="required">※</span>：</th>
            <td>
              <select id="formMeetingRepresentative" class="form-control w_50">
                <option value="">選択してください▼</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>会議対象者<span class="required">※</span>：</th>
            <td>
              <select id="formMeetingMemberList" class="form-control w_50 mb_10" onchange="selectedMeetingMember(event)">
                <option value="">会議対象者を追加▼</option>
              </select>
              <div id="member_area" class="form-control rm_member_area"><div>
            </td>
          </tr>
          <tr>
            <th>会議日<span class="required">※</span>：</th>
            <td>
              <input type="date" id="formMeetingDate" class="form-control w_50" onchange="changeMeetingDate()">
            </td>
          </tr>
          <tr>
            <th>会議時間<span class="required">※</span>：</th>
            <td>
              <select id="formMeetingStartHour" class="form-control w_20 d_ib" onchange="changeMeetingDate()"></select>
              <select id="formMeetingStartMinute" class="form-control w_20 d_ib" onchange="changeMeetingDate()"></select>
              <span>～</span>
              <select id="formMeetingEndHour" class="form-control w_20 d_ib"></select>
              <select id="formMeetingEndMinute" class="form-control w_20 d_ib"></select>
            </td>
          </tr>
          <tr>
            <th>会議説明：</th>
            <td>
              <textarea id="formMeetingComment" class="form-control rm_comment" autocomplete="off"></textarea>
            </td>
          </tr>
          <tr>
            <th>カラー<span class="required">※</span>：</th>
            <td>
              <div id="color_area" class="rm_color_area d_ib"></div>
              <div class="d_ib">
                <div id="selectedColor" class="rm_selected_color" style="background-color: transparent;">選択カラー</div>
              </div>
            </td>
          </tr>
          <tr>
            <th>リマインド有無<span class="required">※</span>：</th>
            <td>
              <input type="radio" id="unRemind" name="remind" value="false" onchange="changeRemind()" checked>
              <label for="unRemind">なし</label>
              <input type="radio" id="isRemind" class="ml_20" name="remind" value="true" onchange="changeRemind()">
              <label for="isRemind">あり</label>
            </td>
          </tr>
          <tr id="displayRemind">
            <th>リマインド設定<span class="required">※</span>：</th>
            <td>
              <div class="rm_remind_area">
                <p class="rm_item">・簡易設定（日付）<span class="rm_description">※全て10:00にslackへリマインド通知されます。</span></p>
                <input type="checkbox" id="threeDaysAgo" class="ml_20 checkRemind">
                <label for="threeDaysAgo">3日前</label>
                <input type="checkbox" id="twoDaysAgo" class="ml_20 checkRemind">
                <label for="twoDaysAgo">2日前</label>
                <input type="checkbox" id="prevDaysAgo" class="ml_20 checkRemind">
                <label for="prevDaysAgo">会議前日</label>
                <input type="checkbox" id="currentDay" class="ml_20 checkRemind">
                <label for="currentDay">会議当日</label>
              </div>
              <div class="rm_remind_area">
                <p class="rm_item">・簡易設定（時間）<span class="rm_description">※全て会議開始前にslackへリマインド通知されます。</span></p>
                <input type="checkbox" id="oneHourAgo" class="ml_20 checkRemind">
                <label for="oneHourAgo">1時間前</label>
                <input type="checkbox" id="halfAnHourAgo" class="ml_20 checkRemind">
                <label for="halfAnHourAgo">30分前</label>
                <input type="checkbox" id="tenMinuteAgo" class="ml_20 checkRemind">
                <label for="tenMinuteAgo">10分前</label>
              </div>
              <div>
                <input type="checkbox" id="optionalRemind" class="ml_20 checkRemind" onchange="checkedOptionalRemind()">
                <label for="optionalRemind">任意リマインド設定<span class="rm_description">　※リピート設定には適用されません。</span></label>
                <div>
                  <input type="date" id="formRemindDate" class="form-control w_50 d_ib ml_20">
                  <select id="formRemindHour" class="form-control w_20 d_ib"></select>
                  <select id="formRemindMinute" class="form-control w_20 d_ib"></select>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th>リピート有無<span class="required">※</span>：</th>
            <td>
              <input type="radio" id="unRepeat" name="repeat" value="false" onchange="changeRepeat()" checked>
              <label for="unRepeat">なし</label>
              <input type="radio" id="isRepeat" class="ml_20" name="repeat" value="true" onchange="changeRepeat()">
              <label for="isRepeat">あり</label>
            </td>
          </tr>
          <tr class="displayRepeat">
            <th>リピート期間<span class="required">※</span>：</th>
            <td>
              <input type="date" id="formRepeatDateStart" class="form-control w_45 d_ib">
              <span> ～ </span>
              <input type="date" id="formRepeatDateEnd" class="form-control w_45 d_ib">
            </td>
          </tr>
          <tr class="displayRepeat">
            <th>リピート設定<span class="required">※</span>：</th>
            <td>
              <div class="rm_repeat_area">
                <p class="rm_item">・簡易設定</p>
                <input type="checkbox" id="repeatMonthFirst" class="ml_20 checkRepeat">
                <label for="repeatMonthFirst">月初</label>
                <input type="checkbox" id="repeatMonthLast" class="ml_20 checkRepeat">
                <label for="repeatMonthLast">月末</label>
              </div>
              <div class="rm_repeat_area">
                <p class="rm_item">・曜日設定</p>
                <div>
                  <input type="checkbox" id="repeatMonday" class="ml_20 checkRepeat">
                  <label for="repeatMonday">月曜</label>
                  <input type="checkbox" id="repeatTuesday" class="ml_20 checkRepeat">
                  <label for="repeatTuesday">火曜</label>
                  <input type="checkbox" id="repeatWednesday" class="ml_20 checkRepeat">
                  <label for="repeatWednesday">水曜</label>
                  <input type="checkbox" id="repeatThursday" class="ml_20 checkRepeat">
                  <label for="repeatThursday">木曜</label>
                  <input type="checkbox" id="repeatFriday" class="ml_20 checkRepeat">
                  <label for="repeatFriday">金曜</label>
                </div>
                  <!-- <div>
                  <input type="checkbox" id="repeatSaturday" class="ml_20 checkRepeat">
                  <label for="repeatSaturday">土曜</label>
                  <input type="checkbox" id="repeatSunday" class="ml_20 checkRepeat">
                  <label for="repeatSunday">日曜</label>
                </div> -->
              </div>
              <div>
                <input type="checkbox" id="optionalRepeatDay" class="ml_20 checkRepeat" onchange="checkedOptionalRepeatDay()">
                <label for="optionalRepeatDay">リピート日設定<span class="rm_description">　※対象の日付が月に存在しない場合は省略されます。</span></label>
                <select id="formMeetingRepeatList" class="form-control w_50 mb_10" onchange="selectedMeetingRepeat(event)">
                  <option value="">リピート日を追加▼</option>
                </select>
                <div id="repeat_area" class="form-control rm_repeat_form_area"><div>
              </div>
            </td>
          </tr>
          <tr>
            <th>slack通知<span class="required">※</span>：</th>
            <td>
              <p class="rm_slack_description">※会議予約保存後、slackIDが登録されている会議メンバーにslack通知されます。</p>
              <input type="radio" id="unSlack" name="slack" value="false" checked>
              <label for="unSlack">なし</label>
              <input type="radio" id="isSlack" class="ml_20" name="slack" value="true">
              <label for="isSlack">あり</label>
            </td>
          </tr>
        </table>
      </div>
      <div id="error_area" class="alert alert-danger" role="alert"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
        <button type="button" id="deleteBtn" class="btn btn-danger" onclick="onDeleteBtn()">削除</button>
        <button type="button" id="saveBtn" class="btn btn-primary" onclick="onSaveBtn()"></button>
      </div>
    </div>
  </div>
</div>
