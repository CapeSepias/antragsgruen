<?php

namespace app\plugins\frauenrat\controllers;

use app\components\UrlHelper;
use app\controllers\Base;
use app\models\mergeAmendments\Init;
use app\plugins\frauenrat\pdf\Frauenrat;
use app\views\pdfLayouts\IPdfWriter;
use app\models\db\{ConsultationSettingsTag, Motion, User};
use PhpMyAdmin\Pdf;

class MotionController extends Base
{
    /**
     * @param string $motionSlug
     *
     * @return string
     * @throws \Yii\base\ExitException
     */
    public function actionSaveTag($motionSlug)
    {
        $motion = $this->consultation->getMotion($motionSlug);
        if (!$motion) {
            \Yii::$app->response->statusCode = 404;
            return 'Motion not found';
        }
        if (!User::havePrivilege($this->consultation, User::PRIVILEGE_SCREENING)) {
            \Yii::$app->response->statusCode = 403;
            return 'Not permitted to change the tag';
        }

        foreach ($motion->getPublicTopicTags() as $tag) {
            $motion->unlink('tags', $tag, true);
        }
        foreach ($this->consultation->getSortedTags(ConsultationSettingsTag::TYPE_PUBLIC_TOPIC) as $tag) {
            if ($tag->id === intval(\Yii::$app->request->post('newTag'))) {
                $motion->link('tags', $tag);
            }
        }

        return $this->redirect(UrlHelper::createMotionUrl($motion));
    }

    /**
     * @param string $motionSlug
     *
     * @return string
     * @throws \Yii\base\ExitException
     */
    public function actionSaveProposal($motionSlug)
    {
        $motion = $this->consultation->getMotion($motionSlug);
        if (!$motion) {
            \Yii::$app->response->statusCode = 404;
            return 'Motion not found';
        }
        if (!User::havePrivilege($this->consultation, User::PRIVILEGE_CHANGE_PROPOSALS)) {
            \Yii::$app->response->statusCode = 403;
            return 'Not permitted to change the status';
        }

        $newStatus = \Yii::$app->request->post('newProposal');
        $motion->proposalVisibleFrom = date("Y-m-d H:i:s");
        switch ($newStatus) {
            case 'accept':
                $motion->proposalStatus = Motion::STATUS_ACCEPTED;
                $motion->proposalComment = '';
                break;
            case 'reject':
                $motion->proposalStatus = Motion::STATUS_REJECTED;
                $motion->proposalComment = '';
                break;
            case 'modified':
                $motion->proposalStatus = Motion::STATUS_MODIFIED_ACCEPTED;
                $motion->proposalComment = '';
                break;
            case 'voting':
                $motion->proposalStatus = Motion::STATUS_VOTE;
                $motion->proposalComment = '';
                break;
            case '':
                $motion->proposalVisibleFrom = null;
                break;
            default:
                $motion->proposalStatus = Motion::STATUS_CUSTOM_STRING;
                $motion->proposalComment = $newStatus;
        }
        $motion->save();

        return $this->redirect(UrlHelper::createMotionUrl($motion));
    }

    /**
     * @param Motion[] $motions
     */
    private function createPdfFromMotions(array $motions, string $title, string $topPageFile): IPdfWriter
    {
        $motionType = $motions[0]->getMyMotionType();
        $pdfLayout = new Frauenrat($motionType);
        $pdf        = $pdfLayout->createPDFClass();

        $pdf->SetCreator('Deutscher Frauenrat');
        $pdf->SetAuthor('Deutscher Frauenrat');
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);

        $pageCount = $pdf->setSourceFile($topPageFile);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId, ['adjustPageSize' => true]);
        }

        foreach ($motions as $motion) {
            $form = Init::forEmbeddedAmendmentsExport($motion);
            \app\views\motion\LayoutHelper::printMotionWithEmbeddedAmendmentsToPdf($form, $pdfLayout, $pdf);

            foreach ($motion->getVisibleAmendmentsSorted(false, false) as $amendment) {
                \app\views\amendment\LayoutHelper::printToPDF($pdf, $pdfLayout, $amendment);
            }
        }

        return $pdf;
    }

    public function actionSchwerpunktthemen()
    {
        $motions = [];
        foreach ($this->consultation->getVisibleIMotionsSorted(false) as $motion) {
            if (strpos($motion->titlePrefix, 'A') === 0 && is_a($motion, Motion::class)) {
                $motions[] = $motion;
            }
        }
        $topPageFile = __DIR__ . '/../assets/top5_antragsspiegel.pdf';

        $pdf = $this->createPdfFromMotions($motions, 'Schwerpunktthemen', $topPageFile);
        $pdf->Output();
    }

    public function actionSachantraege()
    {
        $motions = [];
        foreach ($this->consultation->getVisibleIMotionsSorted(false) as $motion) {
            if (strpos($motion->titlePrefix, 'A') !== 0 && is_a($motion, Motion::class)) {
                $motions[] = $motion;
            }
        }
        $topPageFile = __DIR__ . '/../assets/top6_antragsspiegel.pdf';

        $pdf = $this->createPdfFromMotions($motions, 'Sachanträge', $topPageFile);
        $pdf->Output();
    }
}
