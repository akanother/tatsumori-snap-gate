@include('mail.partials.header')

<?php
    //
    $from_org = '郡山';
    $from_post = '人事・総務';
    $from_user = '山田太郎';

    $name = '鈴木花子';
    $name_kana = 'すずきはなこ';
    $is_org ='キクロス';
    $is_post ='球状第一';
    $set_org ='クレアリカ';
    $set_post ='後工程';


?>
<div class="container">
    <div class="py-5 text-center">
        {{--<img class="d-block mx-auto mb-4" src="/docs/4.3/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">--}}
        <h2>組織・部署変更が発行されました。</h2>
        <h3 class="mt-5 text-left">チーム各位へ</h3>
        <h3 class="mt-5 text-left">お疲れ様です。只今、{{$from_org}}　{{$from_post}}部の{{$from_user}}さんより、組織・部署変更が発行されましたのでお知らせいたします。</h3>
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
                    <th>現在の所属組織</th>
                    <td>{{$is_org}}</td>
                </tr>
                <tr>
                    <th>現在の所属部署</th>
                    <td>{{$is_post}}</td>
                </tr>
                <tr>
                    <th>変更する所属組織</th>
                    <td class="text-success">{{$set_org}}</td>
                </tr>
                <tr>
                    <th>変更する所属部署</th>
                    <td class="text-success">{{$set_post}}</td>
                </tr>
            </table>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© @php print date('Y年m月d日') @endphp 株式会社　龍森 < 情報システム室・GROUP ></p>
    </footer>
</div>
@include('mail.partials.footer')
