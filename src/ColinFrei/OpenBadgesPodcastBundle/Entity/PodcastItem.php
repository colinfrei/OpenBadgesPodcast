<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="podcast_item")
 * @ORM\Entity
 *
 * @Serializer\XmlRoot("item")
 */
class PodcastItem
{
    const TYPE_MP3 = 'audio/mpeg';
    const TYPE_OGGVORBIS = 'audio/ogg';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @ORM\Column(name="link", type="string")
     */
    private $link;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="date", type="datetime")
     *
     * @Serializer\SerializedName("pubDate")
     */
    private $date;

    /**
     * The duration in seconds
     *
     * @ORM\Column(name="duration", type="integer", nullable=true)
     *
     * @Serializer\Exclude
     */
    private $duration;

    /**
     * @ORM\Column(name="file_url", type="string")
     *
     * @Serializer\SerializedName("guid")
     */
    private $fileUrl;

    /**
     * @ORM\Column(name="type", type="string")
     *
     * @Serializer\Exclude
     */
    private $type = self::TYPE_MP3;

    /**
     * @ORM\ManyToOne(targetEntity="Podcast", inversedBy="items")
     * @ORM\JoinColumn(name="podcast_id", referencedColumnName="id")
     */
    private $podcast;


    public function __construct($title, $link, \DateTime $date, $fileUrl, Podcast $podcast)
    {
        $this->title = $title;
        $this->link = $link;
        $this->date = $date;
        $this->fileUrl = $fileUrl;
        $this->podcast = $podcast;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("enclosure")
     */
    public function getSerializedEnclosure()
    {
        return new Enclosure($this->fileUrl, $this->duration, $this->type);
    }

    public function setDuration($duration)
    {
        $this->duration = (int) $duration;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getId()
    {
        return $this->id;
    }
}
