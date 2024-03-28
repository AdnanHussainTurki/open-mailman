<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPMailer\PHPMailer\PHPMailer;

class Message extends Model
{
    use HasFactory;

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    function messenger()
    {
        return $this->belongsTo(Messenger::class);
    }

    function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    function send()
    {
        if ($this->messenger->driver == 'email') {
            // Send the email
            $this->sendEmail();
            $this->sent_at = now();
            $this->status = 'sent';
            $this->save();
        } else if ($this->messenger->driver == 'sms') {
            // Send the SMS
            $this->sendSMS();
            $this->sent_at = now();
            $this->status = 'sent';
            $this->save();
        } else if ($this->messenger->driver == 'telegram') {
            // Send the SMS
            $this->sendTelegram();
            $this->sent_at = now();
            $this->status = 'sent';
            $this->save();
        } else {
            throw new \Exception('Invalid messenger type');
        }
    }

    function sendEmail()
    {
        $mail = $this->getMailer();
        $mail->addAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $this->body;
        return $mail->send();
    }

    function getMailer()
    {
        $mail = new PHPMailer(false);
        // $mail->SMTPDebug = 1;
        $mail->IsSMTP();
        $mail->Host = $this->messenger->host;
        $mail->SMTPAuth = true;
        $mail->CharSet = 'UTF-8';
        $mail->Username = $this->messenger->username;
        $mail->Password = $this->messenger->password;
        $meta = json_decode($this->messenger->meta);
        $mail->SMTPSecure = $meta->security || "ssl"; //"tls";//$this->mail_encryption;
        $mail->Port =
            $this->port;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom($this->from, $this->from_name);
        $mail->isHTML(true);
        return $mail;
    }
}
