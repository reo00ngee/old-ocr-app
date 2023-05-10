@extends('layouts.ocrResult')

@section('content')
<div class="col-md-4" style="padding: 0;">
    <div class="card h-100">
        <div class="card-header d-flex">OCR済画像 <a class='ml-auto' href='/home'><i class="fas fa-plus-circle"></i></a>
        </div>
        <div class="card-body p-2">

        </div>
    </div>
</div>
<div class="col-md-6" style=" padding: 0;">
    <div class="card h-100">
        <div class="card-header">OCRされた文章</div>
        <div class="card-body">
            <form method='POST' action="/store">
                @csrf
                <input type='hidden' name='user_id' value="{{ $user['id'] }}">
                <div class="form-group">
                    <p>OCR後に修正された文章</p>
                    <textarea name='fixed_content' class="form-control" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <p class="mt-4">要約された文章</p>
                    <textarea name='point_of_content' class="form-control" rows="10"></textarea>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection