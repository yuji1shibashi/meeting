<!-- アカウント編集モーダル -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel">アカウント編集</h5>
            </div>
            <form action="" class="modal-edit-form" method="POST">
                @csrf
                @method("PUT")
                <div class="modal-body">
                    <input type="hidden" class="modal-edit-id" name="id">
                    <div class="form-group">
                        <label>名前<span class="required">※</span></label>
                        <input type="text" class="form-control col-12 modal-edit-name valid" name="name">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>メールアドレス<span class="required">※</span></label>
                        <input type="email" class="form-control col-12 modal-edit-email valid" name="email">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>管理者権限<span class="required">※</span></label>
                        <input type="radio" id="isGeneralByEdit" class="modal-edit-role valid ml_20" name="role" value="false" onchange="changeRoleByEdit(event)" checked>
                        <label for="isGeneralByEdit">一般</label>
                        <input type="radio" id="isAdminByEdit" class="modal-edit-role valid ml_40" name="role" onchange="changeRoleByEdit(event)" value="true">
                        <label for="isAdminByEdit">管理者</label>
                    </div>
                    <div class="form-group">
                        <label>パスワード<span class="fs_11">　※管理者権限のみ設定できます。</span></label>
                        <input type="password" class="form-control col-12 modal-edit-password" name="password">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>SlackID</label>
                        <input type="text" class="form-control col-12 modal-edit-slackid" name="slackid">
                    </div>
                </div>
                <div id="error_area_edit" class="alert alert-danger" role="alert"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
                    <button type="button" class="modal-update-btn btn btn-primary">更新</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- アカウント登録モーダル -->
<div class="modal fade" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel">アカウント登録</h5>
            </div>
            <form action="" class="modal-store-form" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>名前<span class="required">※</span></label>
                        <input type="text" class="form-control col-12 modal-store-name valid" name="name" autocomplete="off">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>メールアドレス<span class="required">※</span></label>
                        <input type="email" class="form-control col-12 modal-store-email valid" name="email" autocomplete="off">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>管理者権限<span class="required">※</span></label>
                        <div>
                            <input type="radio" id="isGeneralByAdd" class="modal-store-role valid ml_20" name="role" value="false" onchange="changeRoleByAdd(event)" checked>
                            <label for="isGeneralByAdd">一般</label>
                            <input type="radio" id="isAdminByAdd" class="modal-store-role valid ml_40" name="role" onchange="changeRoleByAdd(event)" value="true">
                            <label for="isAdminByAdd">管理者</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>パスワード<span class="fs_11">　※管理者権限のみ設定できます。</span></label>
                        <input type="password" class="form-control col-12 modal-store-password" name="password" autocomplete="off">
                        <div class="invalid-feedback">入力してください</div>
                    </div>
                    <div class="form-group">
                        <label>SlackID</label>
                        <input type="text" class="form-control col-12 modal-store-slackid" name="slackid" autocomplete="off">
                    </div>
                </div>
                <div id="error_area_add" class="alert alert-danger" role="alert"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
                    <button type="button" class="modal-regist-btn btn btn-primary">登録</button>
                </div>
            </form>
        </div>
    </div>
</div>