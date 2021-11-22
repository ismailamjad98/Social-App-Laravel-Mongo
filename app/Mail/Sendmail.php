<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class Sendmail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $url;
    public $email;

    public function __construct($url, $email)
    {
       $this->url = $url;    
       $this->email = $email;    
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('ismail@gmail.com', 'Social Camp')
            ->subject('New User Register')
            ->view('welcome_email');
    }
}
