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
    const IDENTIFIER_COMMUNITY_CALL = 'comm';
    const IDENTIFIER_RESEARCH_CALL = 'rbsd';

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
     * Not saved to DB, only used for output with Serializer
     *
     * @Serializer\XmlElement(cdata=false)
     */
    private $link;

    /**
     * @ORM\OneToMany(targetEntity="PodcastItem", mappedBy="podcast")
     * @ORM\OrderBy({"date"="desc"})
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

    public function setLink($link)
    {
        $this->link = $link;
    }
}
