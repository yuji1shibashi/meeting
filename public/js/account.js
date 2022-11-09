/**
 * 新規アカウントID
 *
 * @var number
 */
 define('ADD_ACCOUNT', 0);

$(function(){

/**
* モーダル
*/

// 必須項目が未入力状態になった場合メッセージを表示する
$('.valid').change(function() {
	if($(this).val() === '') {
		$(this).addClass('is-invalid');
	} else {
		$(this).removeClass('is-invalid');
	}
})

// 登録ボタン押下時
$('.modal-regist-btn').on('click', function() {

	// ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }
    // ロード開始
    showLoading();

    // バリデーションチェックを行う
    let errorMessage = validationByAdd();

	// メール重複チェック
	existEmailDuplicate(ADD_ACCOUNT, $('.modal-store-email').val()).then(function(data) {
		//
		if (data.existEmailDuplicate) {
			errorMessage.push('重複するメールアドレスが存在します。');
		}

		// エラーが存在する場合
		if (errorMessage.length > 0) {
			checkValidate();
			// エラー内容を出力する
			displayErrorMessageByAdd(errorMessage);
			// ロード終了
			removeLoading();
			return false;
		}

		// action先を設定する
		$('.modal-store-form').attr('action', '/account_list');
		$('.modal-store-form').submit();
	});
});

// 更新ボタン押下時
$('.modal-update-btn').on('click', function() {
	// ロード中の場合は処理を実行しない
    if (checkCurrentLoading()) {
        return;
    }
    // ロード開始
    showLoading();

    // バリデーションチェックを行う
    let errorMessage = validationByEdit();
	var id = $('.modal-edit-id').val();

	// メール重複チェック
	existEmailDuplicate(id, $('.modal-edit-email').val()).then(function(data) {

		// 重複メールアドレスが存在する場合
		if (data.existEmailDuplicate) {
			errorMessage.push('重複するメールアドレスが存在します。');
		}

		// エラーが存在する場合
		if (errorMessage.length > 0) {
			checkValidate()
			// エラー内容を出力する
			displayErrorMessageByEdit(errorMessage);
			// ロード終了
			removeLoading();
			return false;
		}

		// action先を設定する
		$('.modal-edit-form').attr('action', '/account_list/' + id);
		$('.modal-edit-form').submit();
	});
});


/**
* 会議室一覧
*/

// 編集ボタン押下時
$('.edit-btn').on('click', function() {
	// バリデーションをリセットする
	$('.valid').removeClass('is-invalid');
	$('#error_area_edit').empty();
    $('#error_area_edit').hide();

	// モーダルに各項目を格納
	var accountId = $(this).attr('id');

	$.ajax({
		url: '/account_list/' + accountId,
		type: 'POST',
		headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	}
	})
	.done(function(data){
		$('.modal-edit-id').val(data.id);
		$('.modal-edit-name').val(data.name);
		$('.modal-edit-email').val(data.email);
		$('.modal-edit-slackid').val(data.slackID);

		// 管理者の場合はパスワード項目活性
		if (data.role === 1) {
			$('.modal-edit-role').prop('checked', true);
			$('#isAdminByEdit').prop('checked', true);
			$('.modal-edit-password').prop('disabled', false);
		} else {
			$('.modal-edit-role').prop('checked', false);
			$('#isGeneralByEdit').prop('checked', true);
			$('.modal-edit-password').prop('disabled', true);
		}
	})
	.fail(function(){
		console.log('error');
	});
});

// 削除ボタン押下時確認ダイアログ表示
$('.delete-btn').on('click', function() {
	var id = $(this).attr('id');
	if(!confirm('削除しますよろしいですか？')) {
		return false;
	} else {
		$('.delete-form' + id).submit();
	}
});

// アカウント登録ボタン押下時
$('.account-btn').on('click', function() {
	// 値をリセットする
	$('.valid').removeClass('is-invalid');
	$('.modal-store-name').val('');
	$('.modal-store-email').val('');
	$('.modal-store-password').val('');
	$('#isGeneralByAdd').prop('checked', true);
	$('.modal-store-slackid').val('');
	$('.modal-store-password').prop('disabled', true);
	$('#error_area_add').empty();
    $('#error_area_add').hide();
});

});

/**
 * バリデーションチェック
 *
 * @return {array}
 */
function validationByAdd() {

	let errorMessage = [];
	let emailRegexp = /^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/;
	let passwordRegexp = /^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}$/i;
	let slackRegexp = /^[A-Za-z0-9]*$/;

	// エラーメッセージリセット、非表示
	$('#error_area_add').empty();
	$('#error_area_add').hide();

	// 名前
	if ($('.modal-store-name').val() === '') {
		errorMessage.push('名前が未設定です。');
	}

	// メールアドレス
	if ($('.modal-store-email').val() === '') {
		errorMessage.push('メールアドレスが未設定です。');
	}

	// メールアドレス（メールアドレス形式チェック）
	if ($('.modal-store-email').val() !== '' && !emailRegexp.test($('.modal-store-email').val())) {
		errorMessage.push('メールアドレスの形式が不正です。');
	}

	if ($('input:radio[name="role"]:checked').val() === undefined) {
		errorMessage.push('管理者権限が未設定です。');
	}

	// パスワード ※管理者にチェックが入っている場合のみチェック
	if ($('#isAdminByAdd').prop('checked') && $('.modal-store-password').val() === '') {
		errorMessage.push('パスワードが未設定です。');
	}

	// パスワード（パスワード形式チェック）※管理者にチェックが入っている場合のみチェック
	if ($('#isAdminByAdd').prop('checked')
		&& $('.modal-store-password').val() !== '' && !passwordRegexp.test($('.modal-store-password').val())
	) {
		errorMessage.push('パスワードは8文字以上の半角英数字で入力してください。');
	}

	// slack（slack形式チェック）※空文字でないい場合のみチェック
	if ($('.modal-store-slackid').val() !== '' && !slackRegexp.test($('.modal-store-slackid').val())) {
		errorMessage.push('slackは半角英数字で入力してください。');
	}
	return errorMessage;
}

/**
 * バリデーションチェック
 *
 * @return {array}
 */
 function validationByEdit() {
	let errorMessage = [];
	let emailRegexp = /^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/;
	let passwordRegexp = /^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}$/i;
	let slackRegexp = /^[A-Za-z0-9]*$/;

	// エラーメッセージリセット、非表示
	$('#error_area_edit').empty();
	$('#error_area_edit').hide();

	// 名前
	if ($('.modal-edit-name').val() === '') {
		errorMessage.push('名前が未設定です。');
	}

	// メールアドレス
	if ($('.modal-edit-email').val() === '') {
		errorMessage.push('メールアドレスが未設定です。');
	}

	// メールアドレス（メールアドレス形式チェック）
	if ($('.modal-edit-email').val() !== '' && !emailRegexp.test($('.modal-edit-email').val())) {
		errorMessage.push('メールアドレスの形式が不正です。');
	}

	if ($('input:radio[name="role"]:checked').val() === undefined) {
		errorMessage.push('管理者権限が未設定です。');
	}

	// パスワード（パスワード形式チェック）※管理者にチェックが入っている場合のみチェック
	if ($('#isAdminByAdd').prop('checked')
		&& $('.modal-edit-password').val() !== '' && !passwordRegexp.test($('.modal-edit-password').val())
	) {
		errorMessage.push('パスワードは8文字以上の半角英数字で入力してください。');
	}

	// slack（slack形式チェック）※空文字でないい場合のみチェック
	if ($('.modal-edit-slackid').val() !== '' && !slackRegexp.test($('.modal-edit-slackid').val())) {
		errorMessage.push('slackは半角英数字で入力してください。');
	}
	return errorMessage;
}

/**
 * 管理者権限設定切り替え処理
 *
 * @param {any} params
 */
 function changeRoleByAdd(params) {
    // 管理者の場合はパスワード項目を表示
    if ($('#isAdminByAdd').prop('checked')) {
        $('.modal-store-password').prop('disabled', false);
		$('.modal-store-password').addClass('valid');
    } else {
        $('.modal-store-password').prop('disabled', true);
		$('.modal-store-password').removeClass('valid');
    }
}

/**
 * 管理者権限設定切り替え処理
 *
 * @param {any} params
 */
 function changeRoleByEdit(params) {
    // 管理者の場合はパスワード項目を表示
    if ($('#isAdminByEdit').prop('checked')) {
        $('.modal-edit-password').prop('disabled', false);
		// $('.modal-edit-password').addClass('valid');
    } else {
        $('.modal-edit-password').prop('disabled', true);
		// $('.modal-edit-password').removeClass('valid');
    }
}

/**
 * 会議予約新規登録処理
 *
 * @param {string} accountId
 * @param {string} email
 * @return {any}
 */
 function existEmailDuplicate(accountId, email) {
	 return new Promise(function(resolve, reject) {

		// メールアドレスが存在しない場合
		if (email === '') {
			resolve({existEmailDuplicate: false});return;
		}

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'existEmailDuplicate',
            type:'post',
            cache: false,
            dataType:'json',
            data: {accountId: accountId, email: email},
            success: function(data){
                // ロード終了
                removeLoading();
                resolve(data);
            },
            error: function(error){　
                // ロード終了
                removeLoading();
            }
        });
    });
}