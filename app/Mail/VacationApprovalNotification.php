<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $subject = $this->data['action'] === '登録'
            ? '【 ' . $this->data['subject'] . '】が申請されました'
            : '【 ' . $this->data['subject'] . '】が削除されました';

        return $this->subject($subject)
            ->view('mail.vacation_notification')
            ->with('data', $this->data);
    }
}
