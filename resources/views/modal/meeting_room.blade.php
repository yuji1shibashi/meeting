<!-- 会議室編集モーダル -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
	<div class="modal-dialog" role="document">
	    <div class="modal-content">
		    <div class="modal-header">
		        <h5 class="modal-title" id="reservationModalLabel">会議室編集</h5>
			</div>
			<form action="" class="modal-edit-form" method="POST">
				@csrf
				@method("PUT")
				<div class="modal-body">
					<input type="hidden" class="modal-edit-id" name="id">
					<div class="form-group">
						<label>会議室名<span class="required">※</span></label>
						<input type="text" class="form-control col-12 modal-edit-name valid" name="name" autocomplete="off">
						<div class="invalid-feedback">入力してください</div>
					</div>
					<div class="form-group">
						<label>説明<span class="required">※</span></label>
						<textarea class="form-control col-12 modal-edit-description valid" name="description" rows="8" autocomplete="off"></textarea>
						<div class="invalid-feedback">入力してください</div>
					</div>
					<div class="selectbox">
						<label>優先度<span class="required">※</span></label>
						<select class="form-control modal-edit-priority" name="priority">
							<option value="1">優先度1</option>
							<option value="2">優先度2</option>
							<option value="3">優先度3</option>
						</select>
					</div>
				</div>
				<div id="error_area_edit" class="alert alert-danger" role="alert"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
			        <button type="submit" class="modal-update-btn btn btn-primary">更新</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- 会議室登録モーダル -->
<div class="modal fade" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="label1" aria-hidden="true">
	<div class="modal-dialog" role="document">
	    <div class="modal-content">
		    <div class="modal-header">
		        <h5 class="modal-title" id="reservationModalLabel">会議室登録</h5>
			</div>
			<form action="" class="modal-store-form" method="POST">
				@csrf
				<div class="modal-body">
					<div class="form-group">
						<label>会議室名<span class="required">※</span></label>
						<input type="text" class="form-control col-12 modal-store-name valid is-invalid" name="name"autocomplete="off">
						<div class="invalid-feedback">入力してください</div>
					</div>
					<div class="form-group">
						<label>説明<span class="required">※</span></label>
						<textarea class="form-control col-12 modal-store-description valid is-invalid" name="description" rows="8" autocomplete="off"></textarea>
						<div class="invalid-feedback">入力してください</div>
					</div>
					<div class="selectbox">
						<label>優先度<span class="required">※</span></label>
						<select class="form-control" name="priority">
							<option value="1">優先度1</option>
							<option value="2">優先度2</option>
							<option value="3">優先度3</option>
						</select>
					</div>
				</div>
				<div id="error_area_add" class="alert alert-danger" role="alert"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">戻る</button>
			        <button type="submit" class="modal-regist-btn btn btn-primary">登録</button>
				</div>
			</form>
		</div>
	</div>
</div>