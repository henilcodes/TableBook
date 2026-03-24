<?php
namespace App\Services;

class MailService
{
    private $config;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/config.php';
    }

    private function sendHtmlEmail($to, $subject, $messageHtml)
    {
        $mailConfig = $this->config['mail'] ?? [];
        $driver = $mailConfig['driver'] ?? 'mail';
        
        $fromEmail = $mailConfig['from_address'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = $mailConfig['from_name'] ?? ($this->config['app']['name'] ?? 'TableTap');

        if ($driver === 'smtp') {
            return $this->sendSmtpEmail($to, $subject, $messageHtml, $fromEmail, $fromName, $mailConfig);
        }

        // Fallback to mail()
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";

        return mail($to, $subject, $messageHtml, $headers);
    }

    private function sendSmtpEmail($to, $subject, $messageHtml, $fromEmail, $fromName, $mailConfig) 
    {
        $host = $mailConfig['host'] ?? '127.0.0.1';
        $port = $mailConfig['port'] ?? 1025;
        $username = $mailConfig['username'] ?? '';
        $password = $mailConfig['password'] ?? '';
        $encryption = $mailConfig['encryption'] ?? '';
        
        $transport = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
        $timeout = 10;
        
        $socket = @stream_socket_client("$transport:$port", $errno, $errstr, $timeout);
        if (!$socket) {
            error_log("SMTP Connect Failed: $errstr ($errno)");
            return false;
        }
        
        $res = fread($socket, 515);
        if (substr($res, 0, 3) != '220') {
            error_log("SMTP Connect Failed - unexpected response: $res");
            return false;
        }
        
        fwrite($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n");
        $res = fread($socket, 4096);
        
        if ($encryption === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            fread($socket, 515);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fwrite($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n");
            fread($socket, 4096);
        }
        
        if (!empty($username)) {
            fwrite($socket, "AUTH LOGIN\r\n");
            fread($socket, 515);
            fwrite($socket, base64_encode($username) . "\r\n");
            fread($socket, 515);
            fwrite($socket, base64_encode($password) . "\r\n");
            $res = fread($socket, 515);
            if (substr($res, 0, 3) != '235') {
                error_log("SMTP Auth Failed: $res");
                return false;
            }
        }
        
        fwrite($socket, "MAIL FROM:<$fromEmail>\r\n");
        fread($socket, 515);
        
        fwrite($socket, "RCPT TO:<$to>\r\n");
        fread($socket, 515);
        
        fwrite($socket, "DATA\r\n");
        fread($socket, 515);
        
        $headers = "From: =?UTF-8?Q?" . preg_replace('/[^\x00-\x7F]/', '', $fromName) . "?= <$fromEmail>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: =?UTF-8?Q?" . preg_replace('/[^\x00-\x7F]/', '', $subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $body = "$headers\r\n\r\n$messageHtml\r\n.\r\n";
        fwrite($socket, $body);
        $res = fread($socket, 515);
        
        if (substr($res, 0, 3) != '250') {
            error_log("SMTP Data Failed: $res");
            return false;
        }
        
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    }

    public function sendReservationConfirmation($reservation, $toEmail)
    {
        if (empty($toEmail)) return false;

        $subject = "Your Reservation is Confirmed - " . ($reservation['reservation_code'] ?? '');
        $restaurantName = htmlspecialchars($reservation['restaurant_name'] ?? 'Our Restaurant');
        $date = date('F j, Y', strtotime($reservation['reservation_date']));
        $time = date('g:i A', strtotime($reservation['reservation_time']));
        $party = $reservation['party_size'];
        $table = htmlspecialchars($reservation['table_number'] ?? 'Assigned on arrival');

        $html = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #28a745;'>Reservation Confirmed!</h2>
            <p>Dear Guest,</p>
            <p>We are delighted to confirm your reservation at <strong>{$restaurantName}</strong>.</p>
            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <ul style='list-style-type: none; padding: 0;'>
                    <li><strong>Reservation Code:</strong> {$reservation['reservation_code']}</li>
                    <li><strong>Date:</strong> {$date}</li>
                    <li><strong>Time:</strong> {$time}</li>
                    <li><strong>Party Size:</strong> {$party} Guests</li>
                    <li><strong>Table:</strong> {$table}</li>
                </ul>
            </div>
            <p>If you have any special requests or need to make changes, please manage your booking via your account or contact the restaurant.</p>
            <p>We look forward to hosting you!</p>
            <p>Best regards,<br>{$this->config['app']['name']}</p>
        </div>
        ";

        return $this->sendHtmlEmail($toEmail, $subject, $html);
    }

    public function sendReservationStatusUpdate($reservation, $toEmail)
    {
        if (empty($toEmail)) return false;

        $statusOptions = [
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            'no_show' => 'Marked as No-Show'
        ];

        $statusText = $statusOptions[$reservation['status']] ?? ucfirst($reservation['status']);
        $subject = "Reservation Status Update: {$statusText} - " . ($reservation['reservation_code'] ?? '');
        
        $restaurantName = htmlspecialchars($reservation['restaurant_name'] ?? 'Our Restaurant');

        $html = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2>Reservation Update</h2>
            <p>Dear Guest,</p>
            <p>Your reservation at <strong>{$restaurantName}</strong> (Code: {$reservation['reservation_code']}) has been updated.</p>
            <p>New Status: <strong>{$statusText}</strong></p>
            <p>If you have any questions, please contact the restaurant directly.</p>
            <p>Best regards,<br>{$this->config['app']['name']}</p>
        </div>
        ";

        return $this->sendHtmlEmail($toEmail, $subject, $html);
    }

    public function sendNewReservationNotification($reservation, $restaurantEmail)
    {
        if (empty($restaurantEmail)) return false;

        $subject = "New Reservation Received - " . ($reservation['reservation_code'] ?? '');
        $date = date('F j, Y', strtotime($reservation['reservation_date']));
        $time = date('g:i A', strtotime($reservation['reservation_time']));
        $party = $reservation['party_size'];
        $customerName = htmlspecialchars($reservation['customer_name'] ?? 'Guest');

        $html = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #007bff;'>New Reservation</h2>
            <p>You have received a new reservation.</p>
            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <ul style='list-style-type: none; padding: 0;'>
                    <li><strong>Code:</strong> {$reservation['reservation_code']}</li>
                    <li><strong>Customer:</strong> {$customerName}</li>
                    <li><strong>Date:</strong> {$date}</li>
                    <li><strong>Time:</strong> {$time}</li>
                    <li><strong>Party Size:</strong> {$party} Guests</li>
                </ul>
            </div>
            <p>Please log in to your admin dashboard to view full details.</p>
        </div>
        ";

        return $this->sendHtmlEmail($restaurantEmail, $subject, $html);
    }

    public function sendContactMessage($name, $email, $subject, $message)
    {
        $adminEmail = $this->config['mail']['from_address'] ?? 'admin@tabletap.com';
        $emailSubject = "📬 Contact Form: " . htmlspecialchars($subject);

        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <div style='background: linear-gradient(135deg, #152238 0%, #1f7a8c 100%); padding: 28px 32px; border-radius: 12px 12px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 20px;'>📬 New Contact Message</h2>
                <p style='color: rgba(255,255,255,0.75); margin: 6px 0 0; font-size: 14px;'>Received via TableTap Contact Form</p>
            </div>
            <div style='background: #f8fafc; padding: 24px 32px;'>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; color: #64748b; font-size: 13px; width: 100px; vertical-align: top;'><strong>Name</strong></td>
                        <td style='padding: 8px 0; font-weight: 600;'>" . htmlspecialchars($name) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #64748b; font-size: 13px; vertical-align: top;'><strong>Email</strong></td>
                        <td style='padding: 8px 0;'><a href='mailto:" . htmlspecialchars($email) . "' style='color: #1f7a8c;'>" . htmlspecialchars($email) . "</a></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #64748b; font-size: 13px; vertical-align: top;'><strong>Subject</strong></td>
                        <td style='padding: 8px 0;'>" . htmlspecialchars($subject) . "</td>
                    </tr>
                </table>
            </div>
            <div style='background: #ffffff; padding: 24px 32px; border: 1px solid #e2e8f0; border-top: none;'>
                <p style='color: #64748b; font-size: 13px; text-transform: uppercase; font-weight: 700; margin-bottom: 12px;'>Message</p>
                <div style='background: #f8fafc; padding: 16px; border-radius: 8px; border-left: 4px solid #1f7a8c; font-size: 15px; line-height: 1.6;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
            <div style='background: #f8fafc; padding: 16px 32px; border-radius: 0 0 12px 12px; text-align: center; border: 1px solid #e2e8f0; border-top: none;'>
                <p style='color: #94a3b8; font-size: 12px; margin: 0;'>TableTap — Restaurant Reservation Platform</p>
            </div>
        </div>
        ";

        return $this->sendHtmlEmail($adminEmail, $emailSubject, $html);
    }
}
