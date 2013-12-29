<?php

namespace ColinFrei\OpenBadgesPodcastBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PodcastRepository extends EntityRepository
{
    /**
     * @param string $identifier The identifier of the podcast (one of the 'IDENTIFIER' constants in the 'Podcast' class
     * @param string $fileType The filetype of the podcast (currently mp3 or ogg)
     *
     * @return null|object
     */
    public function findByIdentifierAndType($identifier, $fileType)
    {
        $id = $identifier . '_' . $fileType;

        return $this->find($id);
    }
}
