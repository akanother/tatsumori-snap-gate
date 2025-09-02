@include('mail.partials.header')

<div class="container">
    <div class="py-5 text-center">
        <h3 class="mt-5 text-left">各位</h3>
        <h3 class="mt-5 text-left">{{$mail_data['date_of_issue']}}に,{{ $mail_data['from_user_name'] }}より安否確認メールが一斉送信されました。</h3>
        <p class="mt-5 text-left">{{$mail_data['safety_message']}}</p>
        <h4>繰り返します。<strong style="color: #35bf44">先ずは、ご自身やご家族の身の安全を確保してください。</strong></h4>
        <p class="mt-5 text-left text-success">その後、下記URLをクリックして、安否状況の登録をお願いします。</p>
        <a href="{{ route('safety_report.answer',[$mail_data['safety_uuid'],$mail_data['user_id']]) }}">回答ページ</a>
        <hr>
        <p>{{$mail_data['safety_uuid']}}</p>
        <p>{{$mail_data['user_id']}}</p>

    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">©　株式会社　龍森 情報シス ：　@php print date('Y年m月d日') @endphp</p>
    </footer>
</div>
@include('mail.partials.footer')
