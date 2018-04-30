<?php

namespace Shopsys\ShopBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture as DemoCategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture as DemoProductDataFixture;

class BestsellingProductDataFixture extends AbstractReferenceFixture
{
    /** @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade */
    private $manualBestsellingProductFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade $manualBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function __construct(
        ManualBestsellingProductFacade $manualBestsellingProductFacade
    ) {
        $this->manualBestsellingProductFacade = $manualBestsellingProductFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $domainId = 2;
        $this->manualBestsellingProductFacade->edit(
            $this->getReference(DemoCategoryDataFixture::CATEGORY_PHOTO),
            $domainId,
            [$this->getReference(DemoProductDataFixture::PRODUCT_PREFIX . '7')]
        );
    }
}
