<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Controller;

use ColinFrei\OpenBadgesPodcastBundle\Entity\PodcastRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $podcasts = $this->getPodcastRepo()->findAll();
        return $this->render('ColinFreiOpenBadgesPodcastBundle:Default:index.html.twig', array('podcasts' => $podcasts));
    }

    public function feedAction($id)
    {
        $podcast = $this->getPodcastRepo()->find($id);
        if (!$podcast) {
            throw new NotFoundHttpException;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        return new Response($serializer->serialize($podcast, 'xml'), 200, array('Content-Type' => 'application/xml'));
    }

    /**
     * @return PodcastRepository
     */
    private function getPodcastRepo()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('ColinFreiOpenBadgesPodcastBundle:Podcast');
    }
}
