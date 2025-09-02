@include('mail.partials.header')

<div class="container">
    <div class="py-5 text-center">
        <h3 class="mt-5 text-left">システム管理者</h3>
        <h3 class="mt-5 text-left">{{$userName}}さんより共有フォルダの作成申請が登録されました。</h3>
        <p class="mt-5 text-left">セキュリティ設定を実施してください。完了後は{{$userName}}さんへ利用開始の通知を送信してください</p>
        <hr>
        <table class="table table-bordered table-striped text-left" cellpadding="0" cellspacing="0">
            <tr style="padding: 10px;">
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">申請者</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$userName}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">作成先フォルダ</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$parentType}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">作成フォルダ名</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$folderName}}</td>
            </tr>
        </table>

        @if($member)
            <p>このフォルダはプロジェクトフォルダとして申請されています。セキュリティグループに属するメンバーは以下の通りです。</p>
            <table class="table table-bordered table-striped text-left" cellpadding="0" cellspacing="0">
                <tr style="padding: 10px;">
                    <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">プロジェクトメンバー</th>
                </tr>
                @foreach($member as $value)
                    @if($value->user)
                        <tr style="padding: 10px;">
                            <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{ $value->user->name }}</td>
                        </tr>
                  @endif
              @endforeach
            </table>
        @endif
    </div>
    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">©株式会社龍森 情報システム室 ：　@php print date('Y年m月d日') @endphp</p>
    </footer>
</div>
@include('mail.partials.footer')
