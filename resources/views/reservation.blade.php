<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>会議予約一覧</title>
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link rel="stylesheet" href="{{ asset('css/reservation.css') }}">
        <script src="{{ asset('/js/jquery-3.5.1.min.js') }}"></script>
        <script src="{{ asset('/js/common.js') }}"></script>
        <script src="{{ asset('/js/reservation.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    </head>
    <body>

        @include('common.header')
        @include('modal.reservation')

        <div class="r_search_area">
            <div class="d_ib w_20 r_search_form">
                <p class="r_search_head">検索対象日：</p>
                <input type="hidden" id="searchDate" value="">
                <input type="date" id="inputMeetingDate" class="form-control w_100" autocomplete="off">
            </div>
            <div class="d_ib w_20 r_search_form">
                <p class="r_search_head">主催者：</p>
                <select id="inputMeetingRepresentative" class="form-control w_100">
                    <option value="">未設定</option>
                    @foreach ($meetingMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d_ib w_20 r_search_form">
                <p class="r_search_head">会議メンバー：</p>
                <select id="inputMeetingMember" class="form-control w_100">
                    <option value="">未設定</option>
                    @foreach ($meetingMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d_ib w_10">
                <button type="button" class="r_search_btn btn btn btn-dark" data-toggle="modal" onclick="onSearch()">検索</button>
            </div>
            <button type="button" class="r_add_btn btn btn btn-light" data-toggle="modal" onclick="openReservationModal(0)">新規会議予約作成</button>
        </div>
        <div class="r_date_area">
            <button id="prevDay" class="d_ib btn btn-light r_change_day_btn" type="button" onclick="onPrevDay()"><</button>
            <div id="targetDay" class="d_ib r_target_day"></div>
            <button id="nextDay" class="d_ib btn btn-light r_change_day_btn" type="button" onclick="onNextDay()">></button>
        </div>
        <div class="r_reservation_area">
            <div id="time_area" class="r_time_area">
                <span class="r_head r_meeting_room">会議室名</span>
            </div>

            <div id="row_area" class="r_row_area"></div>
        </div>
    </body>
</html>
