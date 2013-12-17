<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="podcast")
 * @ORM\Entity(repositoryClass="ColinFrei\OpenBadgesPodcastBundle\Entity\PodcastRepository")
 *
 * @Serializer\XmlRoot("channel")
 */
class Podcast
{
    /**
     * Manually set ID
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     *
     * @Serializer\Exclude
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="PodcastItem", mappedBy="podcast")
     * @Serializer\Inline
     * @Serializer\XmlList(entry="item")
     */
    private $items;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
