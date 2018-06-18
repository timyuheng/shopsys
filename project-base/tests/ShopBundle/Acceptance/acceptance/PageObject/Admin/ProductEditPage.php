<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class ProductEditPage extends AbstractPage
{
    /**
     * @param int $productId
     */
    public function saveInvalidForm($productId)
    {
        $this->tester->amOnPage('/admin/product/edit/' . $productId);
        // scroll to have the button visible because of the fixed bars
        $this->tester->scrollTo(['css' => '.js-parameters-item-add'], null, -300);
        $this->tester->clickByCss('.js-parameters-item-add');
        $this->tester->clickByText('Save changes');
    }

    public function cleanUpWorkspaceAfterChanges()
    {
        $this->tester->amOnPage('/admin/');
        $this->tester->acceptPopup();
    }

    public function assertSaveFormErrorBoxVisible()
    {
        $this->tester->see('Please check the entered values.');
    }
}
