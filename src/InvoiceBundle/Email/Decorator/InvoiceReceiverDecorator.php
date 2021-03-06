<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) 2013-2017 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InvoiceBundle\Email\Decorator;

use SolidInvoice\MailerBundle\Decorator\MessageDecorator;
use SolidInvoice\MailerBundle\Decorator\VerificationMessageDecorator;
use SolidInvoice\MailerBundle\Event\MessageEvent;
use SolidInvoice\InvoiceBundle\Email\InvoiceEmail;
use SolidInvoice\SettingsBundle\SystemConfig;

final class InvoiceReceiverDecorator implements MessageDecorator, VerificationMessageDecorator
{
    /**
     * @var SystemConfig
     */
    private $config;

    public function __construct(SystemConfig $config)
    {
        $this->config = $config;
    }

    public function decorate(MessageEvent $event): void
    {
        /** @var InvoiceEmail $message */
        $message = $event->getMessage();
        $invoice = $message->getInvoice();

        foreach ($invoice->getUsers() as $user) {
            $message->addTo($user->getEmail(), trim(sprintf('%s %s', $user->getFirstName(), $user->getLastName())));
        }

        if ($bcc = (string) $this->config->get('invoice/bcc_address')) {
            $message->addBcc($bcc);
        }
    }

    public function shouldDecorate(MessageEvent $event): bool
    {
        $message = $event->getMessage();

        return $message instanceof InvoiceEmail && null === $message->getTo();
    }
}
