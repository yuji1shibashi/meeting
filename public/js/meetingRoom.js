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

	// バリデーションチェックを行う
	let errorMessage = validationByAdd();

	// エラーが存在する場合
	if (errorMessage.length > 0) {
		checkValidate()
		// エラー内容を出力する
		displayErrorMessageByAdd(errorMessage);
		return false;
	}

	// action先を設定する
	$('.modal-store-form').attr('action', '/meeting_room');
	$('.modal-store-form').submit();
});

// 更新ボタン押下時
$('.modal-update-btn').on('click', function() {

		// バリデーションチェックを行う
	let errorMessage = validationByEdit();

	// エラーが存在する場合
	if (errorMessage.length > 0) {
		checkValidate()
		// エラー内容を出力する
		displayErrorMessageByEdit(errorMessage);
		return false;
	}

	// action先を設定する
	var id = $('.modal-edit-id').val();
	$('.modal-edit-form').attr('action', '/meeting_room/' + id);
	$('.modal-edit-form').submit();
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
	var id = $(this).attr('id');
	var name = $('.meeting-room-name' + id).text();
	var description = $('.meeting-room-description' + id).text();
	var priority = $('.meeting-room-priority' + id).attr('id');

	$('.modal-edit-id').val(id);
	$('.modal-edit-name').val(name);
	$('.modal-edit-description').val(description);
	$('.modal-edit-priority').val(priority);
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

// 会議室登録ボタン押下時
$('.store-btn').on('click', function() {
	// 値をリセットする
	$('.valid').removeClass('is-invalid');
	$('.modal-store-name').val('');
	$('.modal-store-description').val('');
	$('#error_area_add').empty();
    $('#error_area_add').hide();
});

/**
 * バリデーションチェック
 *
 * @return {array}
 */
 function validationByAdd() {

	// バリデーションをリセットする
	$('#error_area_add').empty();
    $('#error_area_add').hide();

	// バリデーションチェックを行う
    let errorMessage = [];

	// 会議室名
	if ($('.modal-store-name').val() === '') {
		errorMessage.push('会議室名が未設定です。')
	}

	// 説明
	if ($('.modal-store-description').val() === '') {
		errorMessage.push('会議室説明が未設定です。')
	}

	return errorMessage;
}

/**
 * バリデーションチェック
 *
 * @return {array}
 */
 function validationByEdit() {

	// バリデーションをリセットする
	$('#error_area_edit').empty();
    $('#error_area_edit').hide();

	// バリデーションチェックを行う
    let errorMessage = [];

	// 会議室名
	if ($('.modal-edit-name').val() === '') {
		errorMessage.push('会議室名が未設定です。')
	}

	// 説明
	if ($('.modal-edit-description').val() === '') {
		errorMessage.push('会議室説明が未設定です。')
	}

	return errorMessage;
}

});
