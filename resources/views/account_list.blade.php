<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>アカウント一覧</title>
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link rel="stylesheet" href="{{ asset('css/account.css') }}">
        <script src="{{ asset('/js/jquery-3.5.1.min.js') }}"></script>
        <script src="{{ asset('/js/common.js') }}"></script>
        <script src="{{ asset('/js/account.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    </head>
    <body>

        @include('common.header')
        @include('modal.account')

        @if (session('result_message'))
            <div class="result-message">
                {{ session('result_message') }}
            </div>
        @endif

        <div>
            <div>
                <img class="meet-room-icon" src="{{ asset('image/meet_icon.jpeg') }}" alt="">
                <h2 class="account-title">アカウント一覧</h2>
                <button type="button" class="store-btn btn btn-dark account-btn" data-toggle="modal" data-target="#storeModal">アカウント登録</button>
            <div>
            <table class='table account-table'>
                <tr class='account-item'>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>権限</th>
                    <th>編集</th>
                    <th>削除</th>
                </tr>

                @foreach ($lists as $key => $list)
                <tr>
                    <td class="account-list-name{{ $list->id }}">{{ $list->name }}</td>
                    <td class="account-list-email{{ $list->id }}">{{ $list->email }}</td>
                    <td class="account-list-role{{ $list->id }}">
                        @if ($list->role)
                            {{ '管理者' }}
                        @else
                            {{ '一般' }}
                        @endif
                    </td>
                    <td>
                        <button id="{{ $list->id }}" type="button" class="edit-btn btn btn-primary" data-toggle="modal" data-target="#editModal">編集</button>
                    </td>
                    <td>
                        <form class="delete-form{{ $list->id }}" action="/account_list/{{ $list->id }}" method="POST">
                            @csrf
                            @method("DELETE")
                            <button id="{{ $list->id }}" type="button" class="delete-btn btn btn-danger">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>
