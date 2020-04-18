@extends('layouts.app')

@section('content')
<div class="container-fluid ">
  <div class="card">
    <div class="card-header">マイページ</div>

    <div class="card-body">
      <p>{{ $user->name }}</p>
      <p>{{ $user->email }}</p>
      <a href="{{route('user.edit')}}">修正する</a>

      <table class="table table-striped">
        <thead>
          <tr class="table-info">
            <th scope="col">約定日</th>
            <th scope="col">コード</th>
            <th scope="col">購入株式数</th>
            <th scope="col">購入単価</th>
            <!--
            <th scope="col">市場</th>
            <th scope="col">名称</th>
            <th scope="col">業種</th>
            <th scope="col">取得価額</th>
            <th scope="col">時価</th>
            <th scope="col">時価構成比</th>
            <th scope="col">損益</th>
            <th scope="col">損益率</th>
            <th scope="col">1株配当</th>
            <th scope="col">受取配当金</th>
            <th scope="col">配当金構成比</th>
            <th scope="col">簿価利回り</th>
            -->
          </tr>
        </thead>
        <tbody>
          @foreach ($user->executionHistories()->get() as $history)
            <tr>
              <td>{{ $history->execution_date }}</td>
              <td>{{ $history->code }}</td>
              <td>{{ $history->quantity }}</td>
              <td>{{ $history->unitprice * $history->quantity }}</td>
              <!--
              <td>{{ $history->market }}</td>
              <td>TODO: コードから名称を拾う</td>
              <td>TODO: コードから業種を拾う</td>
              <td>TODO: コードから時価を拾う</td>
              <td>TODO: コードから時価構成比を計算する</td>
              <td>TODO: 個別の損益を計算する</td>
              <td>TODO: 個別の損益率を計算する</td>
              <td>TODO: コードから１株配当を拾う</td>
              <td>TODO: 1株配当と保有株式数をかける</td>
              <td>TODO: 全体から配当金構成比を計算する</td>
              <td>TODO: 個別の簿価利回りを計算する</td>
              -->
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
