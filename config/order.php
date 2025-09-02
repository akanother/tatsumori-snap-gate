<?php

$appUrl = env('APP_URL');

if (strpos($appUrl, 'homestead.tatsumori_jet') !== false) {
    // APP_URLが https://homestead.tatsumori_jet/ の場合

    $mailRecipients = [
        'hshiraka@tatsumori.co.jp'
    ];

} elseif (strpos($appUrl, 'jet.t1.tatsumori.co.jp') !== false) {
    // APP_URLが https://jet.t1.tatsumori.co.jp の場合
    $mailRecipients = [
        'hshiraka@tatsumori.co.jp',
        'miyazaki@tatsumori.co.jp',
        'tanfang@tatsumori.co.jp',
        'hoshina7@tatsumori.co.jp',
    ];

    $domesticOrderUser = [
        'hshiraka@tatsumori.co.jp',
        'miyazaki@tatsumori.co.jp',
        'tanfang@tatsumori.co.jp',
        'hoshina7@tatsumori.co.jp',
    ];

    $internationalOrderUser = [
        'hshiraka@tatsumori.co.jp',
        'miyazaki@tatsumori.co.jp',
        'tanfang@tatsumori.co.jp',
        'hoshina7@tatsumori.co.jp',
    ];

} else {
    // それ以外の場合は、必要に応じてデフォルト値を設定
    $mailRecipients = [
        'hshiraka@tatsumori.co.jp'
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Order File Send Mail Group
    |--------------------------------------------------------------------------
    |
    | 受注ファイルの保存時にメール送信する宛先を指定します。
    | 環境変数 ORDER_FILE_STORAGE_PATH が設定されていない場合は、
    | デフォルトで 'Order' ディレクトリに保存されます。
    |
    */
    'mail' => $mailRecipients,

    /*
    |--------------------------------------------------------------------------
    | 工場出荷回答メール送信先
    |--------------------------------------------------------------------------
    |
    | 工場出荷予定回答（編集・削除）の際に送信するメール宛先
    | 環境変数 ORDER_FILE_STORAGE_PATH が設定されていない場合は、
    | デフォルトで 'Order' ディレクトリに保存されます。
    |
    */
    'factory_shipping_mail_tp' => $mailRecipients,

    /*
    |--------------------------------------------------------------------------
    | Order File Storage Path
    |--------------------------------------------------------------------------
    |
    | 受注ファイルの保存先ディレクトリを指定します。
    | 環境変数 ORDER_FILE_STORAGE_PATH が設定されていない場合は、
    | デフォルトで 'Order' ディレクトリに保存されます。
    |
    */
    'file_storage_path' => env('ORDER_FILE_STORAGE_PATH', 'Order'),
];
