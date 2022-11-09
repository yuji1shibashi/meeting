<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\DB;
use App\Http\Services\CommonService;
use App\Models\Account;

/**
 * slackサービス
 */
class AccountListService
{
    /**
     * アカウント削除関数
     *
     * @param int $accountId
     * @return array
     */
    public function destroy(int $accountId)
    {
        DB::beginTransaction();

        try {
            // アカウントIDが存在しない場合はエラー
            if (empty($accountId)) {
                throw new \Exception("アカウント削除に失敗しました。");
            }

            // 会議予約削除
            $this->_deleteAccount($accountId);

            DB::commit();

        } catch (\Exception $e) {
            echo $e;
            DB::rollback();
        }
        return ['success' => 'success'];
    }

    /**
     * アカウント削除処理
     *
     * @param int $accountId
     * @return void
     */
    private function _deleteAccount(int $accountId) : void
    {
        Account::where('id', $accountId)->delete();
    }
}