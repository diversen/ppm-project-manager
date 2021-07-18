<?php declare(strict_types=1);

namespace Pebble;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Parsedown;
use Pebble\Config;
use Pebble\LogInstance;
use Pebble\ExceptionTrace;


class SMTP
{

    /**
     * Default SMTP from (email)
     */
    private $from = '';

    /**
     * Default fromName (name)
     */
    private $fromName = '';

    /**
     * Markdown safemode enabled 
     */
    private $safeMode = true;


    /**
     * Set safemode if sending markdown emails
     */
    public function setSafeMode(bool $bool)
    {
        $this->safeMode = $bool;
    }

    /**
     * Constructor
     */
    public function __construct (string $from = '', string $fromName = '' ) {

        // Check constructor
        if (!empty($from)) {
            $this->from = $from;
            if (empty($fromName)) {
                $this->fromName = $from;
            }
            return;
        }

        // If variables NOT SET in constructor then load from Configuration
        if (!Config::get('SMTP.DefaultFrom') || !Config::get('SMTP.DefaultFromName')) { 
            throw new Exception('Set DefaultFrom and DefaultFromName in config/SMTP.php');
        }

        $this->from = Config::get('SMTP.DefaultFrom');
        $this->fromName = Config::get('SMTP.DefaultFromName');

    }

    /**
     * Get PHPMailer object
     * Initialized from SMTP in Config folder
     * @return PHPMailer\PHPMailer\PHPMailer
     */
    private function getPHPMailer()
    {

        $smtp_config = Config::getSection('SMTP');
        $mail = new PHPMailer(true);

        // You don't need to catch configuration settings
        $mail->SMTPDebug = $smtp_config['SMTPDebug'];
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $smtp_config['Host'];
        $mail->SMTPAuth = $smtp_config['SMTPAuth'];
        $mail->Username = $smtp_config['Username'];
        $mail->Password = $smtp_config['Password'];
        $mail->SMTPSecure = $smtp_config['SMTPSecure'];
        $mail->Port = $smtp_config['Port'];

        return $mail;
    }


    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $text, string $html, array $attachments = [])
    {

        try {

            $mail = $this->getPHPMailer();
            $mail->setFrom($this->from, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->from);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $text;

            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $mail->addAttachment($file);
                }
            }

            $mail->send();
        
        } catch (Exception $e) {
            LogInstance::get()->message(ExceptionTrace::get($e), 'error');
            
            return false;
        }

        return true;
    }

    public function getMarkdown (string $text) {

        $parsedown = new Parsedown();
        $parsedown->setSafeMode($this->safeMode);
        $html = $parsedown->text($text);
        return $html;
    }

    /**
     * Send mail as markdown
     */
    public function sendMarkdown(string $to, string $subject, string $text, array $attachments = [])
    {

        $html = $this->getMarkdown($text);

        return $this->send($to, $subject, $text, $html, $attachments);

    }
}

