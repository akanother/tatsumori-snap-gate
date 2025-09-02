@include('mail.partials.header')

<?php
    //
    $from_org = '郡山';
    $from_post = '人事・総務';
    $from_user = '山田太郎';

    $name = '鈴木花子';
    $name_kana = 'すずきはなこ';
    $add_org ='キクロス';
    $add_post ='球状第一';
    $email ='szuki@tatsumori.co.jp';
    $account ='suzuki';
    $password ='12345678';

?>
<div class="container">
    <div class="py-5 text-center">
        {{--<img class="d-block mx-auto mb-4" src="/docs/4.3/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">--}}
        <h2>社員登録が発行されました。</h2>
        <h3 class="mt-5 text-left">チーム各位へ</h3>
        <h3 class="mt-5 text-left">お疲れ様です。只今、{{$from_org}}　{{$from_post}}部の{{$from_user}}さんより、社員登録が発行されましたのでお知らせいたします。</h3>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <h4 class="mb-3 text-center">発行された申請情報</h4>
            <table class="table">
                <tr>
                    <th>氏名</th>
                    <td>{{$name}}</td>
                </tr>
                <tr>
                    <th>かな</th>
                    <td>{{$name_kana}}</td>
                </tr>
                <tr>
                    <th>所属組織</th>
                    <td>{{$add_org}}</td>
                </tr>
                <tr>
                    <th>所属部署</th>
                    <td>{{$add_post}}</td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                    <td>{{$email}}</td>
                </tr>
                <tr>
                    <th>アカウント</th>
                    <td>{{$account}}</td>
                </tr>
                <tr>
                    <th>パスワード</th>
                    <td>{{$password}}</td>
                </tr>
            </table>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© @php print date('Y年m月d日') @endphp 株式会社　龍森 < 情報システム室・GROUP ></p>
    </footer>
</div>
@include('mail.partials.footer')
