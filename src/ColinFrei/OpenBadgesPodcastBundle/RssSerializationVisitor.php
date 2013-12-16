<?php

namespace ColinFrei\OpenBadgesPodcastBundle;

use JMS\Serializer\XmlSerializationVisitor;

class RssSerializationVisitor extends XmlSerializationVisitor
{
    public function getResult()
    {
        // <rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">

        $rootNode = $this->document->createElement("rss");
        $nsAttribute = $this->document->createAttribute('xmlns:itunes');
        $nsAttribute->value = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
        $rootNode->appendChild($nsAttribute);

        $versionAttribute = $this->document->createAttribute('version');
        $versionAttribute->value = '2.0';
        $rootNode->appendChild($versionAttribute);

        $rootNode->appendChild($this->document->firstChild);
        $this->document->appendChild($rootNode);

        return $this->document->saveXML();
    }
}
