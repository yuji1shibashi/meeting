<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
/* ログアウト */
Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

/* 存在しないURLは会議予約画面に遷移 */
Route::fallback(function() {
    return redirect(route('reservation'));
});

/* 会議室予約画面 */
Route::prefix('reservation')->group(function () {
    /* 会議室予約一覧表示 */
    Route::get('/', [App\Http\Controllers\ReservationController::class, 'index'])->name('reservation');
    /* 会議室予約一覧検索 */
    Route::get('/search', [App\Http\Controllers\ReservationController::class, 'search']);
    /* 会議室予約詳細 */
    Route::get('/detail', [App\Http\Controllers\ReservationController::class, 'detail']);
    /* 会議室予約モーダル必要データ取得 */
    Route::get('/modal', [App\Http\Controllers\ReservationController::class, 'getMeetingRoomsAndMembers']);
    /* 新規会議室予約登録 */
    Route::post('/', [App\Http\Controllers\ReservationController::class, 'store']);
    /* 新規会議室予約編集 */
    Route::put('/', [App\Http\Controllers\ReservationController::class, 'update']);
    /* 新規会議室予約削除 */
    Route::delete('/', [App\Http\Controllers\ReservationController::class, 'destroy']);
});

/* 管理者権限 */
Route::middleware('auth')->group( function() {
    /* 会議室管理 */
    Route::prefix('meeting_room')->group(function () {
        /* 会議室一覧表示 */
        Route::get('/', [App\Http\Controllers\MeetingRoomController::class, 'index']);
        /* 会議室登録 */
        Route::post('/', [App\Http\Controllers\MeetingRoomController::class, 'store']);
        /* 会議室編集 */
        Route::put('/{id}', [App\Http\Controllers\MeetingRoomController::class, 'update']);
        /* 会議室削除 */
        Route::delete('/{id}', [App\Http\Controllers\MeetingRoomController::class, 'destroy']);
    });

    /* アカウント管理 */
    Route::prefix('account_list')->group(function () {
        /* アカウント一覧表示 */
        Route::get('/', [App\Http\Controllers\AccountListController::class, 'index']);
        /* アカウント登録 */
        Route::post('/', [App\Http\Controllers\AccountListController::class, 'regist']);
        /* アカウント更新 */
        Route::put('/{id}', [App\Http\Controllers\AccountListController::class, 'update']);
        /* アカウント削除 */
        Route::delete('/{id}', [App\Http\Controllers\AccountListController::class, 'destroy']);
        /* アカウント情報取得API */
        Route::post('/{id}', [App\Http\Controllers\AccountListController::class, 'getAccountData']);
    });
    /* メール重複チェック */
    Route::post('/existEmailDuplicate', [App\Http\Controllers\AccountListController::class, 'existEmailDuplicate']);
});