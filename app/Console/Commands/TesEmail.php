<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TesEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tes-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        require 'vendor/autoload.php'; // If you're using Composer (recommended)
        // Comment out the above line if not using Composer
        // require("<PATH TO>/sendgrid-php.php");
        // If not using Composer, uncomment the above line and
        // download sendgrid-php.zip from the latest release here,
        // replacing <PATH TO> with the path to the sendgrid-php.php file,
        // which is included in the download:
        // https://github.com/sendgrid/sendgrid-php/releases

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));
        $email->setSubject("Sending with SendGrid is Fun");
        $email->addTo("aliefwafiq2@gmail.com", "Example User");
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent(
            "text/html",
            "<strong>and easy to do anywhere, even with PHP</strong>"
        );
        $sendgrid = new \SendGrid(config('mail.sendgrid_api_key'));
        // $sendgrid->setDataResidency("eu");
        // uncomment the above line if you are sending mail using a regional EU subuser
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}
