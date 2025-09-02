@include('mail.partials.header')

<div class="container">
    <div class="py-5 text-center">
        <h3 class="mt-5 text-left">{{$name}}さん</h3>
        <h3 class="mt-5 text-left">申請された共有フォルダの準備ができましたのでお知らせいたします。</h3>
        <p class="mt-5 text-left">共有フォルダの作成が完了しました。これより共有をご利用いただく事ができます。プロジェクト共有を指定されている場合は、プロジェクトメンバーへ通知してください。</p>

        <table class="table table-bordered table-striped text-left" cellpadding="0" cellspacing="0">
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">ディレクトリ</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$dir}}</td>
            </tr>

            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">フォルダ名</th>
                <td style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">{{$folder}}</td>
            </tr>
        </table>

        <h4 class="mt-3">容量の管理</h4>
        <ul>
            <li>・不必要なファイルや古いデータは定期的に削除する</li>
            <li>・大きなファイルは圧縮して保管できるか検討してください</li>
            <li>・容量を消費する可能性のあるメディアファイル（動画、高解像度の画像など）のアップロードは控えることを検討してください</li>
            <li>・ファイル名は明確かつわかりやすく命名する。</li>
        </ul>

        <h4 class="mt-3">フォルダの使い方の明確化</h4>
        <ul>
            <li>・フォルダ利用用途に、その利用目的や内容を記載し確認できるようにしてください。</li>
            <li>・新しいユーザーが加わった場合、フォルダの使い方を説明するオリエンテーションを行ってください</li>
            <li>・容量を消費する可能性のあるメディアファイル（動画、高解像度の画像など）のアップロードは控えることを検討してください</li>
        </ul>
        <small>JetStreamでフォルダ利用用途を確認することができます</small>
        <small>https://jet.t1.tatsumori.co.jp</small>

        <h4 class="mt-3">セキュリティとアクセス管理</h4>
        <ul>
            <li>・アクセス権限を必要なユーザーに限定し、不正アクセスを防止する</li>
            <li>・重要なデータは適切な暗号化やパスワード保護を行う</li>
            <li>・容量を消費する可能性のあるメディアファイル（動画、高解像度の画像など）のアップロードは控えることを検討してください</li>
        </ul>
    </div>
    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">©株式会社龍森 情報システム室 ：　@php print date('Y年m月d日') @endphp</p>
    </footer>
</div>
@include('mail.partials.footer')
