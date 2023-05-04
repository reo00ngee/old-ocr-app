@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">OCR登録</div>

                <div class="card-body">
                    <form method='POST' action="/store" enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='user_id' value="{{ $user['id'] }}">
                        <!-- 画像をアップロードするフォーム -->
                        <div class="form-group">
                            <label for="image">OCRする画像をアップロードしてください</label>
                            <div>
                                <input type="file" class="form-control-file" name='image' id="image">
                            </div>
                        </div>
                        <button type='submit' class="btn btn-primary btn-lg">保存</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection