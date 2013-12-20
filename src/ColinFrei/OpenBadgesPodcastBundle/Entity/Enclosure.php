<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

/**
 * This is not a persisted entity, it's just a helper for the serializer, since I couldn't
 * figure out how to add an XML element with attributes in any other way.
 *
 * @Serializer\XmlRoot("item")
 * @Serializer\ExclusionPolicy("all")
 */
class Enclosure
{
    private $url;
    private $length;
    private $type;

    public function __construct($url, $length, $type)
    {
        $this->url = $url;
        $this->length = $length;
        $this->type = $type;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\XmlAttributeMap
     */
    public function getSerializedEnclosure()
    {
        return array(
            'url' => $this->url,
            'length' => $this->length,
            'type' => $this->type
        );
    }
}