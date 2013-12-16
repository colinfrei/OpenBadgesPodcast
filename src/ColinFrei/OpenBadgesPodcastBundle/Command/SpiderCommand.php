<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Command;

use Buzz\Browser;
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
            ->setDescription('Check for new calls to add to database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $podcastItems = $this->entityManager->getRepository('ColinFreiOpenBadgesPodcastBundle:PodcastItem')->findAll();

        $searchPage = $this->buzz->get('https://archive.org/search.php?query=subject%3A%22openbadges%22');
        $crawler = new Crawler($searchPage->getContent());

        $links = $crawler->filter('a.titleLink');
        foreach ($links as $link) {
            $hrefAttribute = $link->getAttribute('href');
            $this->logger->info('Processing link', array('link' => $hrefAttribute));

            $linkTitle = $link->textContent;
            $podcastIdentifier = strtolower(substr($linkTitle, 0, 3));
            if (!in_array($podcastIdentifier, array('obi', 'rsd'))) {
                $this->logger->debug(
                    'Skipping link, not in list of identifiers we\'re interested in',
                    array('link' => $hrefAttribute, 'identifier' => $podcastIdentifier, 'title' => $linkTitle)
                );
                continue;
            }

            $mediaItemUrl = $this->archiveOrgBase . $hrefAttribute;
            foreach ($podcastItems as $podcastItem) {
                if ($podcastItem->getLink() == $mediaItemUrl) {
                    // Assumption is that if it's in the DB then both file formats are in the db, so don't differentiate
                    $this->logger->debug(
                        'Skipping link, already in db',
                        array('link' => $hrefAttribute, 'dbItemId' => $podcastItem->getId())
                    );

                    continue 2;
                }
            }

            $this->addPodcastItem($mediaItemUrl, $podcastIdentifier);
        }
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
