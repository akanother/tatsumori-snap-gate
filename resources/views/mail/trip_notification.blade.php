<p style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
    以下の出張申請が<strong style="color: #007BFF;">{{ $data['action'] }}</strong>されました。
</p>

<h2 style="font-family: Arial, sans-serif; font-size: 20px; color: #007BFF; border-bottom: 2px solid #007BFF; padding-bottom: 5px; margin-bottom: 20px;">
    出張内容
</h2>

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; color: #333; margin-bottom: 20px;">
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">申請者</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['user_name'] }}</td>
    </tr>
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">組織 / 部門</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['organization_name'] }} / {{ $data['post_name'] }}</td>
    </tr>
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">出張期間</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['start_date'] }} ～ {{ $data['end_date'] }}</td>
    </tr>
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">出張日数</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['vacation_days'] }}</td>
    </tr>
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">出張先</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['place'] }}</td>
    </tr>
    <tr>
        <th style="text-align: left; background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd;">出張目的</th>
        <td style="padding: 10px; border: 1px solid #ddd;">{{ $data['purpose'] }}</td>
    </tr>
</table>

<div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin-top:20px; margin-bottom: 20px;">
    <p style="margin: 0; padding: 0;">{{ $data['reason'] }}</
</div>
