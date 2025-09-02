@include('mail.partials.header')
<div class="container">
    <div class="py-5 text-center">
        <h2>工場出荷予定回答（単納）が回答されました。</h2>
        <h3 class="mt-5 text-left">受注担当者へ</h3>
        <h3 class="mt-5 text-left">工場出荷予定（単納）の回答が登録されましたのでお知らせいたします。回答の確認はJETSTREAMの「受注情報一覧」より確認してください。</h3>
    </div>
    <div class="row">
        <div class="col-md-12 p-5">
            <h4 class="mb-3 text-center">登録された受注情報</h4>
            <table style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">顧客注番</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">
                        {{ $mail_data['order_number'] ? '---' : '' }}
                    </td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">受注登録日</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['order_date'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">得意先名</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['customer_name'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">受注・内示</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['indication'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">有償・無償</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['paid'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">希望納期</th>
                        <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['requested_delivery_date'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">工場出荷予定（単納）</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">{{ $mail_data['shipping_possible_date'] }}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;"> URL</th>
                    <td style="border: 1px solid #222222; padding: 8px; background: #EFEFEF; color: #000000;">
                        <a href="{{ $mail_data['app_url'] }}" style="margin-top: 10px; color: #1abc9c; font-size: 16px; text-decoration: none; font-size: 10px;">{{ $mail_data['app_url'] }}</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© @php print date('Y年m月d日') @endphp 株式会社　龍森 JETSTREAM </p>
    </footer>
</div>
@include('mail.partials.footer')
