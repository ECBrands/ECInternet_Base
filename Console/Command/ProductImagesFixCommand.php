<?php
/**
 * Copyright (C) EC Brands Corporation - All Rights Reserved
 * Contact Licensing@ECInternet.com for use guidelines
 */
declare(strict_types=1);

namespace ECInternet\Base\Console\Command;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Custom Command for fixing Product Images
 */
class ProductImagesFixCommand extends Command
{
    const NAME = 'ecinternet:product-images:fix';

    const DESC = 'Fix small_image and thumbnail on products that have an image defined but no small_image or thumbnail';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_productCollectionFactory;

    /**
     * ProductImagesFixCommand constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct();

        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName(self::NAME)->setDescription(self::DESC);
    }

    /**
     * Executes the current command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
        $products = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addMediaGalleryData();

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            $imagePath = $product->getMediaGalleryImages()->getFirstItem()->getData('file');

            if (!empty($imagePath)) {
                $output->writeln("SKU: {$product->getSku()} - Setting thumbnail and small_image to: $imagePath.");
                $product->addAttributeUpdate('thumbnail', $imagePath, 0);
                $product->addAttributeUpdate('small_image', $imagePath, 0);
            }
        }
    }
}
