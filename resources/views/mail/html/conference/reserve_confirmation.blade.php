@include('mail.partials.header')
<div class="container">
    <div class="py-5 text-center">
        <h4 >{{$mail_data['user_name']}}さん</h4>
        @if($mail_data['status'] == 'creat')
            <h1 style="text-align: center;margin-bottom: 5px">会議室を予約しました。</h1>
            <p style="text-align: center;">Your meeting room reservation has been successfully registered.</p>
        @endif
{{--        @if($mail_data['status'] == 'edit')--}}
{{--            <h3 class="mt-5 text-left">会意義の予約が変更されましたのでお知らせいたします。</h3>--}}
{{--        @endif--}}
    </div>

    <div>
        <table class="table table-bordered table-striped text-left" cellpadding="0" cellspacing="0">
            <tr style="padding: 10px;">
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">予約した組織</th>
                <td style="padding: 10px; border: solid 1px #ccc;">{{$mail_data['org_name']}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">予約した会議室</th>
                <td style="padding: 10px; border: solid 1px #ccc;">{{$mail_data['room_name']}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">予約開始日時</th>
                <td style="padding: 10px; border: solid 1px #ccc;">{{$mail_data['start_date']}} - {{$mail_data['start_time']}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">予約終了日時</th>
                <td style="padding: 10px; border: solid 1px #ccc;">{{$mail_data['end_date']}} - {{$mail_data['end_time']}}</td>
            </tr>
            <tr>
                <th style="width: 20%; text-align: left; padding: 10px; border: solid 1px #ccc;">予約者</th>
                <td style="padding: 10px; border: solid 1px #ccc;">{{$mail_data['user_name']}}</td>
            </tr>
        </table>
    </div>

    <div class="py-5 mt-5 mb-5" style="margin-top: 20px; margin-bottom: 20px;">
        <h4 style="text-align: left; color: #00aced; font-weight: bold;">会議室予約情報をOUTLOOKカレンダーに連携できるようになりました。</h4>
        <p>会議室の予約情報がワンクリックでカレンダーツールと連携できるようになりました。連携する方法は予約メールに添付されている「ISC」ファイルをOUTLOOKなどで展開することでカレンダー情報がコピーされ連携を進めます。詳しい手順は下記のステップを参考にしてください。</p>
        <p>Meeting room reservation information can now be linked to the calendar tool with a single click. To link with the calendar tool, expand the "ISC" file attached to the reservation e-mail using OUTLOOK, etc., and the calendar information will be copied and linked to the calendar tool. Please refer to the following steps for detailed instructions.</p>
        <ul style="margin-top: 10px;">
            <li>1:添付ファイル「＊＊＊＊.icl」を右クリックして「開く」を選択します</li>
            <li>2:予約情報がカレンダ-項目に連携されます。必要に応じて他の項目を入力してください</li>
            <li>3:全ての予定情報の入力が完了しましたら「保存」して終了します。</li>
            <li>4:Teams会議を招集する場合は、「Teams会議」ボタンをクリックしてTeams会議リンクを生成します。参加者（招待者）のメールアドレスを記入し「送信」をクリックして会議招待メールを発行してくいださい。</li>
        </ul>
    </div>
    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">©情報システム室 ：　@php print date('Y年') @endphp</p>
    </footer>
</div>
@include('mail.partials.footer')
