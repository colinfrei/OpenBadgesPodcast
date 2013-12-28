<?php

namespace ColinFrei\OpenBadgesPodcastBundle;

use ColinFrei\OpenBadgesPodcastBundle\Entity\Podcast;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Symfony\Component\Routing\RouterInterface;

class AddPodcastLinkEventSubscriber implements EventSubscriberInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
        );
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $podcast = $event->getObject();
        if (!($podcast instanceof Podcast)) {
            return;
        }

        $podcast->setLink($this->router->generate(
            'colin_frei_openbadges_podcast_feed',
            array('id' => $podcast->getId()),
            true
        ));
    }
}
