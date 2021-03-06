<?php

namespace ColinFrei\OpenBadgesPodcastBundle;

use JMS\Serializer\XmlSerializationVisitor;

/**
 * This overrides the JMS Serializer's XML Serialization Visitor in the config, and wraps the entire XML
 * with an '<rss>' element
 */
class RssSerializationVisitor extends XmlSerializationVisitor
{
    /**
     * Set here just to add a PHPDoc
     *
     * @var \DOMDocument
     */
    public $document;

    public function getResult()
    {
        // <rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">

        $this->addRssElement();

        return $this->document->saveXML();
    }

    private function addRssElement()
    {
        $rootNode = $this->document->createElement("rss");
        $nsAttribute = $this->document->createAttribute('xmlns:itunes');
        $nsAttribute->value = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
        $rootNode->appendChild($nsAttribute);

        $versionAttribute = $this->document->createAttribute('version');
        $versionAttribute->value = '2.0';
        $rootNode->appendChild($versionAttribute);

        $rootNode->appendChild($this->document->firstChild);
        $this->document->appendChild($rootNode);
    }
}
