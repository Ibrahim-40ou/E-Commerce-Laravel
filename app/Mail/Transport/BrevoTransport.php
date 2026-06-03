<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
// 💡 Import the exact modern classes from your Brevo documentation snippet
use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;

class BrevoTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {

        $email = $message->getOriginalMessage();

        // Ensure we are dealing with a standard Email instance that has headers/body elements
        if (!$email instanceof Email) {
            throw new \Exception('Brevo transport requires a standard Symfony Mime Email instance.');
        }

        // 1. Initialize the unified client
        $client = new Brevo(apiKey: $this->apiKey);

        // 2. Loop through Laravel's dynamic recipients and build SDK items
        $toItems = [];
        foreach ($email->getTo() as $to) {
            $toItems[] = new SendTransacEmailRequestToItem([
                'email' => $to->getAddress(),
                'name' => $to->getName() ?: null,
            ]);
        }

        // 3. Fire the request synchronously using the official layout
        $client->transactionalEmails->sendTransacEmail(
            new SendTransacEmailRequest([
                'subject' => $email->getSubject(),
                'htmlContent' => $email->getHtmlBody(),
                'sender' => new SendTransacEmailRequestSender([
                    'name' => config('mail.from.name'),
                    'email' => config('mail.from.address'),
                ]),
                'to' => $toItems,
            ])
        );
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
