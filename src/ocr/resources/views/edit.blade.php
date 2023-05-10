@extends('layouts.ocrResult')

@section('content')
<div class="col-md-4" style="padding: 0;">
  <div class="card h-100">
    <div class="card-header d-flex">OCR済画像 <a class='ml-auto' href='/home'><i class="fas fa-plus-circle"></i></a>
    </div>
    <div class="card-body p-2">
      <img src="{{ '/storage/' . $ocr['image']}}" class='w-100 mb-3' />
    </div>
  </div>
</div>
<div class="col-md-6" style=" padding: 0;">
  <div class="card h-100">
    <form method='POST' action="/delete/{{$ocr['id']}}" id='delete-form'>
      @csrf
      <div class="card-header">OCRされた文章
        <button><i id='delete-button' class="fas fa-trash"></i></button>
      </div>
    </form>
    <div class="card-body">
      <form method='POST' action="{{route('update',['id' => $ocr['id']])}}">
        @csrf
        <input type='hidden' name='user_id' value="{{ $user['id'] }}">
        <div class="form-group">
          <p>OCR後に修正された文章</p>
          <textarea name='fixed_content' class="form-control" rows="10">{{$ocr['fixed_content']}}</textarea>
        </div>
        <div class="form-group">
          <p class="mt-4">要約された文章</p>
          <textarea name='point_of_content' class="form-control" rows="10">{{$ocr['point_of_content']}}</textarea>
        </div>
        <button type='submit' class="btn btn-primary btn-lg">更新</button>
      </form>
    </div>
  </div>
</div>
@endsection