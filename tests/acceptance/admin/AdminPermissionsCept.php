<?php

/** @var \Codeception\Scenario $scenario */

use app\tests\_pages\{AdminAdminConsultationsPage, AdminConsultationPage, AdminMotionListPage, AdminMotionTypePage, AdminSiteAccessPage, AdminTranslationPage};

$I = new AcceptanceTester($scenario);
$I->populateDBData1();


$I->gotoConsultationHome();
$I->loginAsConsultationAdmin();
$I->gotoStdAdminPage();

$I->wantTo('see the consultation-specific admin pages');
$I->seeElement('#consultationLink');
$I->seeElement('#translationLink');
$I->seeElement('#contentPages');
$I->seeElement('.motionType1');
$I->dontSeeElement('.siteAccessLink');
$I->dontSeeElement('.siteConsultationsLink');
$I->dontSeeElement('.siteUserList');
$I->dontSeeElement('.siteConfigLink');

$I->wantTo('access these pages');
$I->openPage(AdminConsultationPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->seeElement('#consultationSettingsForm');
$I->openPage(AdminMotionListPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->seeElement('.motionListForm');
$I->openPage(AdminTranslationPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->seeElement('#wordingBaseForm');
$I->openPage(AdminMotionTypePage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag', 'motionTypeId' => 1]);
$I->seeElement('.adminTypeForm');
$I->openPage(AdminSiteAccessPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->see('Kein Zugriff auf diese Seite');
$I->dontSeeElement('#siteSettingsForm');
$I->openPage(AdminAdminConsultationsPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->see('Kein Zugriff auf diese Seite');
$I->dontSeeElement('.consultationEditForm');



$I->wantTo('get permission by the more powerful admin');
$I->gotoConsultationHome();
$I->logout();


$adminPage = $I->loginAndGotoStdAdminPage();
$accessPage = $adminPage->gotoSiteAccessPage();
$I->seeCheckboxIsChecked('.admin7 .typeCon input');
$I->checkOption('.admin7 .typeSite input');
$I->submitForm('#adminForm', [], 'saveAdmin');

$I->logout();

$I->gotoConsultationHome();
$I->loginAsConsultationAdmin();
$I->gotoStdAdminPage();



$I->wantTo('see the rest of the admin pages as well');
$I->seeElement('#consultationLink');
$I->seeElement('#translationLink');
$I->seeElement('#contentPages');
$I->seeElement('.motionType1');
$I->seeElement('.siteAccessLink');
$I->seeElement('.siteConsultationsLink');

$I->openPage(AdminSiteAccessPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->dontSee('Kein Zugriff auf diese Seite');
$I->seeElement('#siteSettingsForm');
$I->openPage(AdminAdminConsultationsPage::class, ['subdomain' => 'stdparteitag', 'consultationPath' => 'std-parteitag']);
$I->dontSee('Kein Zugriff auf diese Seite');
$I->seeElement('.consultationEditForm');



$I->wantTo('be made to an proposed procedure admin');
$I->gotoConsultationHome();
$I->logout();

$adminPage = $I->loginAndGotoStdAdminPage();
$accessPage = $adminPage->gotoSiteAccessPage();
$I->see('consultationadmin@example.org');
$I->wait(1);
$I->uncheckOption('.admin7 .typeSite input');
$I->uncheckOption('.admin7 .typeCon input');
$I->checkOption('.admin7 .typeProposal input');

$I->submitForm('#adminForm', [], 'saveAdmin');

$I->logout();

$I->gotoConsultationHome();
$I->loginAsConsultationAdmin();

$I->seeElement('#motionListLink');
$I->dontSeeElement('#adminLink');
$I->gotoMotionList();
$I->dontSeeElement('.actionCol');
$I->seeElement('.proposalCol');



$I->wantTo('be resigned from being an admin');
$I->gotoConsultationHome();
$I->logout();


$adminPage = $I->loginAndGotoStdAdminPage();
$accessPage = $adminPage->gotoSiteAccessPage();
$I->see('consultationadmin@example.org');
$I->wait(1);
$I->executeJS('$(".removeAdmin7").click();');
$I->wait(1);
$I->seeBootboxDialog('Admin-Rechte entziehen');
$I->acceptBootboxConfirm();
$I->wait(1);
$I->dontSee('consultationadmin@example.org');


$I->logout();
$I->gotoConsultationHome();
$I->loginAsConsultationAdmin();
$I->gotoStdAdminPage();
$I->see('Kein Zugriff auf diese Seite');
$I->dontSeeElement('.adminIndex');



$I->wantTo('be an admin like at the beginning');
$I->gotoConsultationHome();
$I->logout();

$adminPage = $I->loginAndGotoStdAdminPage();
$accessPage = $adminPage->gotoSiteAccessPage();
$I->wait(1);
$I->dontSee('consultationadmin@example.org');
$I->selectOption('#adminAddForm select', 'email');
$I->fillField('#addUsername', 'consultationadmin@example.org');
$I->submitForm('#adminAddForm', [], 'addAdmin');
$I->see('consultationadmin@example.org', '.admin7');

$I->logout();
$I->gotoConsultationHome();
$I->loginAsConsultationAdmin();
$I->gotoStdAdminPage();
$I->seeElement('#consultationLink');
