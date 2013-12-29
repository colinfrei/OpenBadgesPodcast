<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Command;

use Buzz\Browser;
use Buzz\Exception\ClientException;
use ColinFrei\OpenBadgesPodcastBundle\Entity\Podcast;
use ColinFrei\OpenBadgesPodcastBundle\Entity\PodcastItem;
use ColinFrei\OpenBadgesPodcastBundle\Entity\PodcastRepository;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class SpiderCommand extends Command
{
    private $buzz;
    private $serializer;
    private $entityManager;
    private $logger;

    private $archiveOrgBase = 'https://archive.org';

    public function __construct(Browser $buzz, SerializerInterface $serializer, EntityManager $entityManager, LoggerInterface $logger)
    {
        parent::__construct();

        $this->buzz = $buzz;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('colinfrei:openbadgespodcast:spider')
            ->setDescription('Check for new call recordings to add to database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PodcastItem[] $podcastItems */
        $podcastItems = $this->entityManager->getRepository('ColinFreiOpenBadgesPodcastBundle:PodcastItem')->findAll();

        $searchPage = $this->buzz->get($this->archiveOrgBase . '/search.php?query=subject%3A%22openbadges%22');
        $crawler = new Crawler($searchPage->getContent());

        $links = $crawler->filter('a.titleLink');
        /** @var \DOMElement $link */
        foreach ($links as $link) {
            $hrefAttribute = $link->getAttribute('href');
            $this->logger->info('Processing link', array('link' => $hrefAttribute));

            $podcastIdentifier = $this->getIdentifierFromListItem($link);

            $mediaItemUrl = $this->archiveOrgBase . $hrefAttribute;
            if ($podcastIdentifier && $this->isUrlAlreadyInDatabase($podcastItems, $mediaItemUrl)) {
                $this->logger->info('Skipping podcast item because already in DB', array('href' => $mediaItemUrl));

                continue;
            }

            try {
                $jsonMediaItemPage = $this->buzz->get($mediaItemUrl . '?output=json');
            } catch (ClientException $exception) {
                $this->logger->warning(
                    'Got an Exception when fetching Media Item page',
                    array('href' => $mediaItemUrl . '?output=json', 'exception' => $exception)
                );

                continue;
            }

            $mediaItem = json_decode($jsonMediaItemPage->getContent());

            if (!$podcastIdentifier) {
                $podcastIdentifier = $this->getIdentifierFromMediaItem($mediaItem);

                if (!$podcastIdentifier) {
                    $this->logger->info('Could not identify what podcast media item belongs to', (array) $mediaItem);

                    continue;
                }
            }

            $this->addPodcastItem($mediaItemUrl, $podcastIdentifier);
        }
    }

    /**
     * @param PodcastItem[] $podcastItems
     * @param string $mediaItemUrl
     *
     * @return bool
     */
    private function isUrlAlreadyInDatabase(array $podcastItems, $mediaItemUrl)
    {
        foreach ($podcastItems as $podcastItem) {
            if ($podcastItem->getLink() == $mediaItemUrl) {
                // Assumption is that if it's in the DB then both file formats are in the db, so don't differentiate

                return true;
            }
        }

        return false;
    }

    private function getIdentifierFromListItem(\DOMElement $link)
    {
        $prefix = strtolower(substr($link->textContent, 0, 3));
        switch ($prefix) {
            case 'obi':
                return Podcast::IDENTIFIER_COMMUNITY_CALL;
            break;

            case 'rsd':
            case 'rbsd':
                return Podcast::IDENTIFIER_RESEARCH_CALL;
            break;

            default:
                return '';
        }
    }

    private function getIdentifierFromMediaItem(\stdClass $mediaItem)
    {
        $description = $mediaItem->metadata->description[0];

        if (false !== strpos($description, 'Research & Badge System Design Call')) {
            return Podcast::IDENTIFIER_RESEARCH_CALL;
        }

        if (false !== strpos($description, 'Community Call')) {
            return Podcast::IDENTIFIER_COMMUNITY_CALL;
        }

        return '';
    }

    private function addPodcastItem($mediaItemUrl, $podcastIdentifier)
    {
        $this->logger->info('Adding Podcast Item', array('href' => $mediaItemUrl));

        //TODO: should parse the url to check how I should be adding the parameter
        $jsonMediaItemPage = $this->buzz->get($mediaItemUrl . '?output=json');
        $mediaItem = json_decode($jsonMediaItemPage->getContent());

        /** @var PodcastRepository $podcastRepository */
        $podcastRepository = $this->entityManager->getRepository('ColinFreiOpenBadgesPodcastBundle:Podcast');
        $mediaItemDate = new \DateTime($mediaItem->metadata->publicdate[0]);
        foreach ($mediaItem->files as $fileName => $fileData) {
            $fileUrl = 'http://' . $mediaItem->server . $mediaItem->dir . $fileName;
            switch ($fileData->format) {
                case 'VBR MP3':
                    $podcast = $podcastRepository->findByIdentifierAndType($podcastIdentifier, 'mp3');
                    $item = new PodcastItem(
                        $mediaItem->metadata->title[0],
                        $mediaItemUrl,
                        $mediaItemDate,
                        $fileUrl,
                        $podcast
                    );
                    $item->setDuration($fileData->length);
                    $item->setType(PodcastItem::TYPE_MP3);
                    $item->setDescription($mediaItem->metadata->description[0]);

                break;

                case 'Ogg Vorbis':
                    $podcast = $podcastRepository->findByIdentifierAndType($podcastIdentifier, 'ogg');
                    $item = new PodcastItem(
                        $mediaItem->metadata->title[0],
                        $mediaItemUrl,
                        $mediaItemDate,
                        $fileUrl,
                        $podcast
                    );
                    $item->setDuration($fileData->length);
                    $item->setType(PodcastItem::TYPE_OGGVORBIS);
                    $item->setDescription($mediaItem->metadata->description[0]);

                break;

                default:
                    // ignore
                    continue 2;
            }

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
