<!DOCTYPE html>
<html lang="ja">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<style>
th {
    width: 0.1%;
    white-space: nowrap;
    }
</style>
<body>


<div class="container">
    <div class="py-5 text-center">
        <h2>FROM JETSTRAM パスワードリセットが要求されました。</h2>
        <p class="lead text-left mg-t-10">JETSTRAMは、あなたのアカウントパスワードリセット要求を受信しました。この<span class="text-primary">パスワードリセットリンクの有効期限は60分です。</span>有効期限内にパスワードリセットを実行してください。有効期限内にリセットができなかった場合は、改めてリセット要求を実行してください。
        <p class="lead text-left mg-t-10 text-danger">このパスワードリセット要求に身の覚えがない場合は、’情報システム室'までお知らせください。</p>
    </div>

<div class="row">
    <div class="col-md-12 ">
        パスワードのリセットは<a href="{{$reset_url}}" class="btn btn-info btn-block" style="color: #0a8dd3; font-size: 20px;">こちら</a>より実施してください。
    </div>
</div>

<div class="py-5 text-center">
    <h6 class="text-danger">ご注意</h6>
    <p class="text-left">このメールは、配信専用のアドレスで配信されています。 このメールに返信されても、返信内容の確認およびご返答ができません。あらかじめご了承ください。</p>
</div>

<footer class="my-5 pt-5 text-muted text-center text-small">
    <p class="mb-1">© @php print date('Y年m月d日') @endphp 株式会社　龍森 TATSUMORI LTD.</p>
</footer>
</div>
</body>
</html>
