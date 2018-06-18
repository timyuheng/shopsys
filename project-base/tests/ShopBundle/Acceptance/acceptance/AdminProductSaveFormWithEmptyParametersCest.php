<?php

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\ProductEditPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class AdminProductSaveFormWithEmptyParametersCest
{
    public function testSaveForm(
        AcceptanceTester $me,
        LoginPage $loginPage,
        ProductEditPage $productEditPage
    ) {
        $me->wantTo('see that I am not able to save an invalid form');
        $loginPage->loginAsAdmin();

        $productEditPage->saveInvalidForm(5);
        $productEditPage->assertSaveFormErrorBoxVisible();

        $productEditPage->cleanUpWorkspaceAfterChanges();
    }
}
