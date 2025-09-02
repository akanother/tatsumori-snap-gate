<!DOCTYPE html>
<html>
<head>
    <title>新しい議事録が共有されました</title>
</head>
<body>
<p>{{ $recipientName }} 様,</p>
<p>新しい議事録が共有されました。JETSTRAMSのSTOCKアプリより議事録を確認することができます。</p>
<p>
    <strong>タイトル:</strong> {{ $meetingReport->title }}<br>

@php
    // Overviewの内容を処理する
    $overviewText = $meetingReport->overview;

    // &nbsp; をスペースに変換してから削除
    $overviewText = str_replace('&nbsp;', ' ', $overviewText);

    // HTMLタグを削除し、<br> を改行に変換
    $overviewText = strip_tags(str_replace('<br>', "\n", $overviewText));

    // 文字数を100文字に制限
    $overviewText = \Illuminate\Support\Str::limit($overviewText, 200);
@endphp

<p style="font-family: Arial, sans-serif; color: #333;">
    <strong>概要:</strong> {!! nl2br(e($overviewText)) !!}
    <!--タグを削除しているので実際の見栄えと違うことを通知する文書-->


</p>

<div>
    <span>(この表示は、議事録の内容を簡略化しており、実際のフォーマットや見栄えと異なる場合があります。詳細や正確なフォーマットについては、議事録の完全版をご確認ください。)</span>

</div>

<p>
    <a href="{{ route('stock.meeting.report.index') }}">https://jet.tatsumori.co.jp</a>
</p>
</body>
</html>
