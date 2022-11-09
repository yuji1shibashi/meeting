<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

/**
 * 会議室予約システムコントローラー
 */
class AccountListController extends Controller
{
    protected $account;

    /**
     * コンストラクタ
     *
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    /**
     * アカウント一覧
     *
     * @return view
     */
    public function index()
    {
        $lists = account::all();

        return view('account_list', ['lists' => $lists]);
    }

    /**
     * アカウント登録
     *
     * @param Request $request
     * @return array
     */
    public function regist(Request $request)
    {
        // トランザクション開始
        DB::beginTransaction();
        try
        {
            // アカウントの登録処理を行う
            $result = $this->account->registAccount($request);
            DB::commit();
            return redirect('/account_list')->with('result_message', 'アカウント登録が完了しました');
        }
        catch (\Exception $e)
        {
            DB::rollback();
            return redirect('/account_list')->with('result_message', 'アカウント登録に失敗しました');
        }
    }

    /**
     * アカウント更新
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $accountId = (int)$request->id;

        // トランザクション開始
        DB::beginTransaction();
        try
        {
            // アカウントの更新処理を行う
            $result = $this->account->updateAccount($request, $accountId);
            DB::commit();
            return redirect('/account_list')->with('result_message', 'アカウント更新が完了しました');
        }
        catch (\Exception $e)
        {
            DB::rollback();
            return redirect('/account_list')->with('result_message', 'アカウント更新に失敗しました');
        }
    }

    /**
     * アカウント削除
     *
     * @param Request $accountId
     * @return array
     */
    public function destroy(Request $request)
    {
        $accountId = (int)$request->id;
        $accountData = Account::find($accountId);

        // 会議室情報の取得に失敗した場合
        if(is_null($accountData)) {
            return redirect('/account_list')->with('result_message', 'アカウント情報の取得に失敗しました');
        }

        // トランザクション開始
        DB::beginTransaction();
        try
        {
            // 会議室の削除処理を行う
            $accountData->delete();
            DB::commit();
            return redirect('/account_list')->with('result_message', 'アカウント削除が完了しました');
        }
        catch (\Exception $e)
        {
            DB::rollback();
            return redirect('/account_list')->with('result_message', 'アカウント削除に失敗しました');
        }
        return redirect('/account_list');
    }

    /**
     * アカウント詳細取得
     *
     * @param Request $request
     * @return array
     */
    public function getAccountData(Request $request)
    {
        $accountId = (int)$request->id;
        return Account::find($accountId);
    }

    /**
     * メール重複チェック
     *
     * @param Request $request
     * @return array
     */
    public function existEmailDuplicate(Request $request) : array
    {
        return ['existEmailDuplicate' => $this->account->existEmailDuplicate(
            $request->input('accountId'),
            $request->input('email')
        )];
    }
}
