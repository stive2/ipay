<?php

namespace App\Notifications\User\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerifyCode extends Notification
{
    use Queueable;

    public $email;
    public $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($email,$code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $code = $this->code;
        $username  = explode('@',$this->email);
        return (new MailMessage)
                    ->greeting(__("Hello")." ".@$username." !")
                    ->subject(__("Verification Code ( Register )"))
                    ->line(__('Votre compte a été crée.'))
                    ->line(__("Votre mot de passe ").": " . $code)
                    ->line(__('Cordialement!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
