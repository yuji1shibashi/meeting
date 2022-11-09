<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Services\CommonService;

class Account extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'accounts';

    protected $fillable = [
        'id', 'name', 'email', 'password', 'role', 'slackID', 'created_at', 'update_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function registAccount($request)
    {
        $password = null;

        // 管理者の場合のみパスワードをセットする
        if (CommonService::convertBooleanValuePhp($request->input('role')) === CommonService::BOOL_VALUE['TRUE']) {
            $password = Hash::make($request->input('password'));
        }
        $datas = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $password,
            'role' => CommonService::convertBooleanValuePhp($request->input('role')),
            'slackID' => $request->input('slackid')
        ];

        return Account::create($datas);
    }

    public function updateAccount($request, $accountId)
    {
    	$datas = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => CommonService::convertBooleanValuePhp($request->input('role')),
            'slackID' => $request->input('slackid')
        ];

        $password = null;

        // パスワードの再設定がある合のみ更新する
        if (!empty($request->input('password'))) {
            // 管理者の場合
            if (CommonService::convertBooleanValuePhp($request->input('role')) === CommonService::BOOL_VALUE['TRUE']) {
                $password = Hash::make($request->input('password'));
            }
            $datas = array_merge($datas, ['password' => $password]);

        } else {
            // 一般の場合
            if (CommonService::convertBooleanValuePhp($request->input('role')) === CommonService::BOOL_VALUE['FALSE']) {
                $datas = array_merge($datas, ['password' => $password]);
            }
        }
    	return Account::find($accountId, 'id')->update($datas);
    }

    /**
     * メールアドレス重複チェック
     *
     * @param string $accountId
     * @param string $email
     * @return boolean
     */
    public function existEmailDuplicate(string $accountId, string $email) : bool
    {
        $existEmailDuplicate = Account::from('accounts')
            ->select(DB::raw("count(*) AS 'duplicate_num'"))
            ->where('email', '=', $email)
            ->where('id', '<>', $accountId)
            ->first();

        return ($existEmailDuplicate->duplicate_num > 0) ? true : false;
    }
}
