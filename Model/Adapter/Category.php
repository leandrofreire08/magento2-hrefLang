<?php


namespace BrunoCanada\HrefLang\Model\Adapter;

use BrunoCanada\HrefLang\Model\AdapterInterface;
use \Magento\Framework\Registry;
use BrunoCanada\HrefLang\Model\BlockParser;
use BrunoCanada\HrefLang\Model\Property;
use BrunoCanada\HrefLang\Model\PropertyInterface;

class Category implements AdapterInterface
{
    /**
     * @var array
     */
    private $messageAttributes = [
        'meta_description',
        'description'
    ];
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var PropertyInterface
     */
    private $property;
    /**
     * @var BlockParser
     */
    private $blockParser;

    public function __construct(
        PropertyInterface $property,
        BlockParser $blockParser,
        Registry $registry
    ) {
        $this->registry = $registry;
        $this->property = $property;
        $this->blockParser = $blockParser;
    }

    public function getProperty() : PropertyInterface
    {
        /**
         * @var $category \Magento\Catalog\Model\Category
         */
        $category = $this->registry->registry('current_category');
        if ($category) {
            $this->property->setTitle((string) $category->getName());
            $this->property->setUrl((string) $category->getUrl());

            foreach ($this->messageAttributes as $messageAttribute) {
                if ($category->getData($messageAttribute)) {
                    $this->property->setDescription($category->getData($messageAttribute));
                }
            }

            if ($category->hasLandingPage() && !$this->property->getProperty('description')) {
                $this->property->setDescription(
                    $this->blockParser->getBlockContentById(
                        (int) $category->getLandingPage()
                    )
                );
            }

            if ($category->getImageUrl()) {
                $this->property->setImage((string) $category->getImageUrl());
            }
            $this->property->addProperty('item', $category->getData(), Property::META_DATA_GROUP);
        }
        return $this->property;
    }
}
