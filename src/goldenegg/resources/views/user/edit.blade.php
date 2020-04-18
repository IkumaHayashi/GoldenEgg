@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">登録情報編集</div>
          <div class="card-body">
            <form method="POST" action="{{ route('user.update', ['user' => $user, 'security' => $user->security]) }}">
              @csrf
              <div class="form-group">
                <label for="name">お名前</label>
                <input type="text" class="form-control" id="name" name="user[name]" value="{{ $user->name }}">
              </div>

              <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" class="form-control" id="email" name="user[email]" value="{{ $user->email }}">
              </div>

              <div class="form-group">
                <label for="security_loginid">ネオモバログインID</label>
                <input type="text" class="form-control" id="security_loginid" name="security[loginid]" value="{{ $user->security->loginid }}">
              </div>

              <div class="form-group">
                <label for="security_password">ネオモバログインパスワード</label>
                <input type="password" class="form-control" id="security_password" name="security[password]" value="{{ $user->security->password }}">
              </div>

              <button type="submit" class="btn btn-primary mb-2">更新する</button>
              <a href="{{route('user.show')}}">キャンセル</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
