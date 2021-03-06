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

namespace SolidInvoice\QuoteBundle\Form\EventListener;

use SolidInvoice\QuoteBundle\Entity\Quote;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuoteUsersSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        if ($data instanceof Quote) {
            $clientId = !is_null($data->getClient()) ? $data->getClient()->getId() : null;
        } else {
            $clientId = isset($data['client']) ? $data['client'] : null;
        }

        if (!empty($clientId)) {
            $form = $event->getForm();

            $form->add(
                'users',
                EntityType::class,
                [
                    'constraints' => new NotBlank(),
                    'multiple' => true,
                    'expanded' => true,
                    'class' => 'SolidInvoiceClientBundle:Contact',
                    'query_builder' => function (EntityRepository $repo) use ($clientId) {
                        return $repo->createQueryBuilder('c')
                            ->where('c.client = :client')
                            ->setParameter('client', $clientId);
                    },
                ]
            );
        }
    }
}
