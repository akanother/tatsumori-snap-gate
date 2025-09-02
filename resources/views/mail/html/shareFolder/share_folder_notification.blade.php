@include('mail.partials.header')

<div class="container">
    <div class="py-5 text-center">
        <h3 class="mt-5 text-left">{{$userName}}さん</h3>
        <h3 class="mt-5 text-left">共有フォルダの作成申請を受理しました。</h3>
        <p class="mt-5 text-left">情報システム部門がセキュリティ設定を完了次第、ご連絡いたします。登録手続きには、通常1日から2営業日程度の時間を要します。</p>
        <p class="mt-5 text-left">ご不明な点等ございましたら情報システム室までお知らせください。</p>
        <hr>
        <table class="table table-bordered" style="border: white; margin-top: 10px;">
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">作成先フォルダ</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$parentType}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">作成フォルダ名</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$folderName}}</td>
            </tr>
        </table>
    </div>
    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">©株式会社龍森 情報システム室 ：　@php print date('Y年m月d日') @endphp</p>
    </footer>
</div>
@include('mail.partials.footer')
