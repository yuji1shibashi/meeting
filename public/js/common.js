/**
 * グローバル変数
 *
 * @var boolean
 */
loading = false;

/**
 * グローバル定数を定義する
 *
 * @param {string}
 * @param {any}
 * @return {void}
 */
function define(name, value) {
    Object.defineProperty(window, name, {
        get: function() { return value; },
        set: function() { throw (name + ' is already defined !!'); }
    });
}

 /**
  * 数値に変換できるかチェックし、変換できない場合はnullを返す
  *
  * @param {string} value
  * @return {number|null}
  */
 function convertNumber(value) {
    return (String(parseInt(value)) !== 'NaN') ?  parseInt(value) : null;
 }

/**
 * ロードスピナーを表示
 *
 * @return {void}
 */
function showLoading() {
    // ローディング画像が非表示かどうかチェック
    if (!loading && $('.loading').length === 0) {

        // ロード状況をONにする
        loading = true;

        // 非表示の場合のみ出力。
        $('body').append(''
            + '<div class="loading">'
                + '<div class="d-flex justify-content-center align-items-center">'
                    + '<div class="spinner-border text-primary loading_spinner" role="status">'
                        + '<span class="sr-only">Connecting...</span>'
                    + '</div>'
                + '</div>'
            + '</div>'
        );
    }
}

/**
 * ロードスピナーを非表示
 *
 * @return {void}
 */
function removeLoading() {
    loading = false;
    $(".loading").remove();
}

/**
 * 現在のロード状況を返す
 *
 * @return {boolean}
 */
function checkCurrentLoading() {
    return loading;
}

/**
 * エラー箇所の枠を囲う
 *
 * @return {void}
 */
function checkValidate() {
    $('.valid').each(function(index, element){
        if($(element).val() === '') {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    })
}

/**
 * エラーメッセージ表示
 *
 * @param {array} errorMessage
 */
 function displayErrorMessageByAdd(errorMessage) {
    // エラーの数だけループ
    for (var error of errorMessage) {
        // エラー追加
        $('#error_area_add').append('<div>' + error + '</div>');
    }
    // エラー表示
    $('#error_area_add').show();
}

/**
 * エラーメッセージ表示
 *
 * @param {array} errorMessage
 */
 function displayErrorMessageByEdit(errorMessage) {
    // エラーの数だけループ
    for (var error of errorMessage) {
        // エラー追加
        $('#error_area_edit').append('<div>' + error + '</div>');
    }
    // エラー表示
    $('#error_area_edit').show();
}
