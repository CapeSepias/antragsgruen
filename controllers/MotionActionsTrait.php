<?php

namespace app\controllers;

use app\models\consultationLog\ProposedProcedureChange;
use app\models\notifications\MotionProposedProcedure;
use app\components\{Tools, UrlHelper};
use app\models\db\{ConsultationLog,
    ConsultationSettingsTag,
    ConsultationUserGroup,
    IComment,
    Motion,
    MotionAdminComment,
    MotionComment,
    MotionSupporter,
    User,
    Consultation};
use app\models\exceptions\{DB, FormError, Internal, MailNotSent};
use app\models\forms\CommentForm;
use app\models\events\MotionEvent;
use app\models\settings\InitiatorForm;
use app\models\supportTypes\SupportBase;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;

/**
 * @property Consultation $consultation
 * @method redirect($uri)
 * @method Session getHttpSession()
 * @method Request getHttpRequest()
 * @method Response getHttpResponse()
 */
trait MotionActionsTrait
{
    /**
     * @throws Internal
     */
    private function getComment(Motion $motion, int $commentId, bool $needsScreeningRights): MotionComment
    {
        /** @var MotionComment $comment */
        $comment = MotionComment::findOne($commentId);
        if (!$comment || $comment->motionId != $motion->id || $comment->status != IComment::STATUS_VISIBLE) {
            throw new Internal(\Yii::t('comment', 'err_not_found'));
        }
        if ($needsScreeningRights) {
            if (!$this->consultation->havePrivilege(ConsultationUserGroup::PRIVILEGE_SCREENING)) {
                throw new Internal(\Yii::t('comment', 'err_no_screening'));
            }
        }
        return $comment;
    }

    private function writeComment(Motion $motion, array &$viewParameters): void
    {
        $postComment = $this->getHttpRequest()->post('comment');

        $replyTo = null;
        if (isset($postComment['parentCommentId']) && $postComment['parentCommentId']) {
            $replyTo = MotionComment::findOne([
                'id'              => $postComment['parentCommentId'],
                'motionId'        => $motion->id,
                'parentCommentId' => null,
            ]);
            if ($replyTo && $replyTo->status === IComment::STATUS_DELETED) {
                $replyTo = null;
            }
        }

        $commentForm = new CommentForm($motion, $replyTo);
        $commentForm->setAttributes($postComment, $motion->getActiveSections());

        try {
            $commentForm->saveNotificationSettings();
            $comment = $commentForm->saveMotionCommentWithChecks($motion);

            if ($comment->status === MotionComment::STATUS_SCREENING) {
                $this->getHttpSession()->setFlash('screening', \Yii::t('comment', 'created_needs_screening'));
            } else {
                $this->getHttpSession()->setFlash('screening', \Yii::t('comment', 'created'));
            }
            $this->redirect(UrlHelper::createMotionCommentUrl($comment));
            \Yii::$app->end();
        } catch (\Exception $e) {
            $viewParameters['commentForm'] = $commentForm;
            if (!isset($viewParameters['openedComments'][$commentForm->sectionId])) {
                $viewParameters['openedComments'][$commentForm->sectionId] = [];
            }
            $viewParameters['openedComments'][$commentForm->sectionId][] = $commentForm->paragraphNo;
            $this->getHttpSession()->setFlash('error', $e->getMessage());
        }
    }

    /**
     * @throws DB
     * @throws Internal
     */
    private function deleteComment(Motion $motion, int $commentId): void
    {
        $comment = $this->getComment($motion, $commentId, false);
        if (!$comment->canDelete(User::getCurrentUser())) {
            throw new Internal(\Yii::t('comment', 'err_no_del'));
        }

        $comment->status = IComment::STATUS_DELETED;
        if (!$comment->save(false)) {
            throw new DB($comment->getErrors());
        }
        ConsultationLog::logCurrUser(
            $motion->getMyConsultation(),
            ConsultationLog::MOTION_COMMENT_DELETE,
            $comment->id
        );

        $this->getHttpSession()->setFlash('success', \Yii::t('comment', 'del_done'));
    }

    /**
     * @throws Internal
     */
    private function screenCommentAccept(Motion $motion, int $commentId): void
    {
        /** @var MotionComment $comment */
        $comment = MotionComment::findOne($commentId);
        if (!$comment || $comment->motionId !== $motion->id) {
            throw new Internal(\Yii::t('comment', 'err_not_found'));
        }
        if (!$this->consultation->havePrivilege(ConsultationUserGroup::PRIVILEGE_SCREENING)) {
            throw new Internal(\Yii::t('comment', 'err_no_screening'));
        }

        $comment->status = IComment::STATUS_VISIBLE;
        $comment->save();

        $motion->refresh();

        ConsultationLog::logCurrUser(
            $motion->getMyConsultation(),
            ConsultationLog::MOTION_COMMENT_SCREEN,
            $comment->id
        );

        $comment->trigger(IComment::EVENT_PUBLISHED);
    }

    /**
     * @throws Internal
     */
    private function screenCommentReject(Motion $motion, int $commentId): void
    {
        /** @var MotionComment $comment */
        $comment = MotionComment::findOne($commentId);
        if (!$comment || $comment->motionId !== $motion->id) {
            throw new Internal(\Yii::t('comment', 'err_not_found'));
        }
        if (!$this->consultation->havePrivilege(ConsultationUserGroup::PRIVILEGE_SCREENING)) {
            throw new Internal(\Yii::t('comment', 'err_no_screening'));
        }

        $comment->status = IComment::STATUS_DELETED;
        $comment->save();

        $motion->refresh();

        ConsultationLog::logCurrUser(
            $motion->getMyConsultation(),
            ConsultationLog::MOTION_COMMENT_DELETE,
            $comment->id
        );
    }

    /**
     * @throws FormError
     * @throws Internal
     */
    private function motionLikeDislike(Motion $motion, string $role, string $string, string $name = '', string $orga = '', string $gender = '', bool $nonPublic = false)
    {
        $currentUser = User::getCurrentUser();
        if (!$motion->motionType->getMotionSupportPolicy()->checkCurrUser()) {
            throw new FormError('Supporting this motion is not possible');
        }

        MotionSupporter::createSupport($motion, $currentUser, $name, $orga, $role, $gender, $nonPublic);

        $this->getHttpSession()->setFlash('success', $string);
    }

    /**
     * @throws FormError
     * @throws Internal
     */
    private function motionLike(Motion $motion)
    {
        if (!($motion->getLikeDislikeSettings() & SupportBase::LIKEDISLIKE_LIKE)) {
            throw new FormError('Not supported');
        }
        $this->motionLikeDislike($motion, MotionSupporter::ROLE_LIKE, \Yii::t('motion', 'like_done'));
        ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_LIKE, $motion->id);
    }

    /**
     * @throws FormError
     * @throws Internal
     */
    private function motionSupport(Motion $motion): void
    {
        if (!$motion->isSupportingPossibleAtThisStatus()) {
            throw new FormError('Not possible given the current motion status');
        }
        foreach ($motion->getSupporters(true) as $supporter) {
            if (User::getCurrentUser() && $supporter->userId === User::getCurrentUser()->id) {
                $this->getHttpSession()->setFlash('success', \Yii::t('motion', 'support_already'));
                return;
            }
        }
        $supportType = $motion->getMyMotionType()->getMotionSupportTypeClass();
        $role = MotionSupporter::ROLE_SUPPORTER;
        $user = User::getCurrentUser();
        $gender = $this->getHttpRequest()->post('motionSupportGender', '');
        $nonPublic = ($supportType->getSettingsObj()->offerNonPublicSupports && $this->getHttpRequest()->post('motionSupportPublic') === null);
        if ($user && $user->fixedData) {
            $name = $user->name;
            $orga = $user->organization;
        } else {
            $name = $this->getHttpRequest()->post('motionSupportName', '');
            $orga = $this->getHttpRequest()->post('motionSupportOrga', '');
        }
        if ($supportType->getSettingsObj()->hasOrganizations && $orga === '') {
            $this->getHttpSession()->setFlash('error', 'No organization entered');
            return;
        }
        if (trim($name) === '') {
            $this->getHttpSession()->setFlash('error', 'You need to enter a name');
            return;
        }
        $validGenderKeys = array_keys(SupportBase::getGenderSelection());
        if ($supportType->getSettingsObj()->contactGender === InitiatorForm::CONTACT_REQUIRED) {
            if (!in_array($gender, $validGenderKeys)) {
                $this->getHttpSession()->setFlash('error', 'You need to fill the gender field');
                return;
            }
        }
        if (!in_array($gender, $validGenderKeys)) {
            $gender = '';
        }

        $this->motionLikeDislike($motion, $role, \Yii::t('motion', 'support_done'), $name, $orga, $gender, $nonPublic);
        ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_SUPPORT, $motion->id);
    }

    /**
     * @throws FormError
     * @throws Internal
     */
    private function motionDislike(Motion $motion): void
    {
        if (!($motion->getLikeDislikeSettings() & SupportBase::LIKEDISLIKE_DISLIKE)) {
            throw new FormError('Not supported');
        }
        $this->motionLikeDislike($motion, MotionSupporter::ROLE_DISLIKE, \Yii::t('motion', 'dislike_done'));
        ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_DISLIKE, $motion->id);
    }

    /**
     * @throws FormError
     */
    private function motionSupportRevoke(Motion $motion): void
    {
        $currentUser          = User::getCurrentUser();
        $loginlessSupported = MotionSupporter::getMyLoginlessSupportIds();
        foreach ($motion->motionSupporters as $supp) {
            if (($currentUser && $supp->userId === $currentUser->id) || in_array($supp->id, $loginlessSupported)) {
                if ($supp->role === MotionSupporter::ROLE_SUPPORTER) {
                    if (!$motion->isSupportingPossibleAtThisStatus()) {
                        throw new FormError('Not possible given the current motion status');
                    }
                }
                $motion->unlink('motionSupporters', $supp, true);
            }
        }

        ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_UNLIKE, $motion->id);
        $this->getHttpSession()->setFlash('success', \Yii::t('motion', 'neutral_done'));
    }

    private function motionSupportFinish(Motion $motion): void
    {
        if (!$motion->canFinishSupportCollection()) {
            $this->getHttpSession()->setFlash('error', \Yii::t('motion', 'support_finish_err'));
            return;
        }

        $motion->trigger(Motion::EVENT_SUBMITTED, new MotionEvent($motion));

        if ($motion->status == Motion::STATUS_SUBMITTED_SCREENED) {
            $motion->trigger(Motion::EVENT_PUBLISHED, new MotionEvent($motion));
        }

        ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_SUPPORT_FINISH, $motion->id);
        $this->getHttpSession()->setFlash('success', \Yii::t('motion', 'support_finish_done'));
    }

    /**
     * @throws Internal
     */
    private function motionAddTag(Motion $motion): void
    {
        if (!$this->consultation->havePrivilege(ConsultationUserGroup::PRIVILEGE_SCREENING)) {
            throw new Internal(\Yii::t('comment', 'err_no_screening'));
        }
        foreach ($motion->getMyConsultation()->getSortedTags(ConsultationSettingsTag::TYPE_PUBLIC_TOPIC) as $tag) {
            if ($tag->id == $this->getHttpRequest()->post('tagId')) {
                $motion->link('tags', $tag);
            }
        }
    }

    /**
     * @throws Internal
     */
    private function motionDelTag(Motion $motion): void
    {
        if (!$this->consultation->havePrivilege(ConsultationUserGroup::PRIVILEGE_SCREENING)) {
            throw new Internal(\Yii::t('comment', 'err_no_screening'));
        }
        foreach ($motion->getMyConsultation()->getSortedTags(ConsultationSettingsTag::TYPE_PUBLIC_TOPIC) as $tag) {
            if ($tag->id === intval($this->getHttpRequest()->post('tagId'))) {
                $motion->unlink('tags', $tag, true);
            }
        }
    }

    private function setProposalAgree(Motion $motion): void
    {
        $procedureToken = $this->getHttpRequest()->get('procedureToken');
        if (!$motion->canSeeProposedProcedure($procedureToken) || !$motion->proposalFeedbackHasBeenRequested()) {
            $this->getHttpSession()->setFlash('error', 'Not allowed to perform this action');
            return;
        }

        $motion->proposalUserStatus = Motion::STATUS_ACCEPTED;
        $motion->save();
        $this->getHttpSession()->setFlash('success', \Yii::t('amend', 'proposal_user_saved'));
    }

    /**
     * @throws \Throwable
     */
    private function savePrivateNote(Motion $motion): void
    {
        $user      = User::getCurrentUser();
        $noteText  = trim($this->getHttpRequest()->post('noteText', ''));
        $paragraph = IntVal($this->getHttpRequest()->post('paragraphNo', -1));
        if (!$user) {
            return;
        }

        if ($this->getHttpRequest()->post('sectionId', 0) > 0) {
            $section = IntVal($this->getHttpRequest()->post('sectionId', 0));
        } else {
            $section = null;
        }

        $comment = $motion->getPrivateComment($section, $paragraph);
        if ($comment && $noteText === '') {
            $comment->delete();
            $motion->refresh();
            return;
        }

        if (!$comment) {
            $comment = new MotionComment();
        }
        $comment->motionId     = $motion->id;
        $comment->userId       = $user->id;
        $comment->text         = $noteText;
        $comment->status       = IComment::STATUS_PRIVATE;
        $comment->paragraph    = $paragraph;
        $comment->sectionId    = $section;
        $comment->dateCreation = date('Y-m-d H:i:s');
        $comment->name         = ($user->name ? $user->name : '-');
        $comment->save();

        $motion->refresh();

        $this->redirect(UrlHelper::createMotionCommentUrl($comment));
        \Yii::$app->end();
    }

    /**
     * @throws \Throwable
     */
    private function performShowActions(Motion $motion, int $commentId, array &$viewParameters): void
    {
        $post = $this->getHttpRequest()->post();
        if ($commentId === 0 && isset($post['commentId'])) {
            $commentId = intval($post['commentId']);
        }
        if (isset($post['deleteComment'])) {
            $this->deleteComment($motion, $commentId);
        } elseif (isset($post['commentScreeningAccept'])) {
            $this->screenCommentAccept($motion, $commentId);
        } elseif (isset($post['commentScreeningReject'])) {
            $this->screenCommentReject($motion, $commentId);
        } elseif (isset($post['motionLike'])) {
            $this->motionLike($motion);
        } elseif (isset($post['motionDislike'])) {
            $this->motionDislike($motion);
        } elseif (isset($post['motionSupport'])) {
            $this->motionSupport($motion);
        } elseif (isset($post['motionSupportRevoke'])) {
            $this->motionSupportRevoke($motion);
        } elseif (isset($post['motionSupportFinish'])) {
            $this->motionSupportFinish($motion);
        } elseif (isset($post['addTag'])) {
            $this->motionAddTag($motion);
        } elseif (isset($post['delTag'])) {
            $this->motionDelTag($motion);
        } elseif (isset($post['writeComment'])) {
            $this->writeComment($motion, $viewParameters);
        } elseif (isset($post['setProposalAgree'])) {
            $this->setProposalAgree($motion);
        } elseif (isset($post['savePrivateNote'])) {
            $this->savePrivateNote($motion);
        }
    }

    /**
     * @param string $motionSlug
     * @return string
     * @throws \Throwable
     * @throws \Yii\db\StaleObjectException
     */
    public function actionDelProposalComment($motionSlug)
    {
        $this->getHttpResponse()->format = Response::FORMAT_RAW;
        $this->getHttpResponse()->headers->add('Content-Type', 'application/json');

        $motion = $this->getMotionWithCheck($motionSlug);
        if (!$motion) {
            return json_encode(['success' => false, 'error' => 'Motion not found']);
        }

        $commentId = $this->getHttpRequest()->post('id');
        $comment   = MotionAdminComment::findOne(['id' => $commentId, 'motionId' => $motion->id]);
        if ($comment && User::isCurrentUser($comment->getMyUser())) {
            $comment->delete();
            return json_encode(['success' => true]);
        } else {
            return json_encode(['success' => false, 'error' => 'No permission to delete this comment']);
        }
    }

    /**
     * @param string $motionSlug
     * @return string
     * @throws Internal
     */
    public function actionSaveProposalStatus($motionSlug)
    {
        $this->getHttpResponse()->format = Response::FORMAT_RAW;
        $this->getHttpResponse()->headers->add('Content-Type', 'application/json');

        $motion = $this->consultation->getMotion($motionSlug);
        if (!$motion) {
            $this->getHttpResponse()->statusCode = 404;
            return 'Motion not found';
        }
        if (!User::havePrivilege($this->consultation, ConsultationUserGroup::PRIVILEGE_CHANGE_PROPOSALS)) {
            $this->getHttpResponse()->statusCode = 403;
            return 'Not permitted to change the status';
        }

        $response = [];
        $msgAlert = null;
        $ppChanges = new ProposedProcedureChange(null);

        if ($this->getHttpRequest()->post('setStatus', null) !== null) {
            $setStatus = IntVal($this->getHttpRequest()->post('setStatus'));
            if ($motion->proposalStatus !== $setStatus) {
                $ppChanges->setProposalStatusChanges($motion->proposalStatus, $setStatus);
                if ($motion->proposalUserStatus !== null) {
                    $msgAlert = \Yii::t('amend', 'proposal_user_change_reset');
                }
                $motion->proposalUserStatus = null;
            }
            $motion->proposalStatus  = $setStatus;

            $ppChanges->setProposalCommentChanges($motion->proposalComment, $this->getHttpRequest()->post('proposalComment', ''));
            $motion->proposalComment = $this->getHttpRequest()->post('proposalComment', '');

            $oldTags = $motion->getProposedProcedureTags();
            $newTags = [];
            $changed = false;
            foreach ($this->getHttpRequest()->post('tags', []) as $newTag) {
                $tag = $motion->getMyConsultation()->getExistingTagOrCreate(ConsultationSettingsTag::TYPE_PROPOSED_PROCEDURE, $newTag, 0);
                if (!isset($oldTags[$tag->getNormalizedName()])) {
                    $motion->link('tags', $tag);
                    $changed = true;
                }
                $newTags[] = ConsultationSettingsTag::normalizeName($newTag);
            }
            foreach ($oldTags as $tagKey => $tag) {
                if (!in_array($tagKey, $newTags)) {
                    $motion->unlink('tags', $tag, true);
                    $changed = true;
                }
            }
            if ($changed) {
                $ppChanges->setProposalTagsHaveChanged(array_keys($oldTags), $newTags);
            }

            $proposalExplanationPre = $motion->proposalExplanation;
            if ($this->getHttpRequest()->post('proposalExplanation', null) !== null) {
                if (trim($this->getHttpRequest()->post('proposalExplanation', '') === '')) {
                    $motion->proposalExplanation = null;
                } else {
                    $motion->proposalExplanation = $this->getHttpRequest()->post('proposalExplanation', '');
                }
            } else {
                $motion->proposalExplanation = null;
            }
            $ppChanges->setProposalExplanationChanges($proposalExplanationPre, $motion->proposalExplanation);

            if ($this->getHttpRequest()->post('visible', 0)) {
                $motion->setProposalPublished();
            } else {
                $motion->proposalVisibleFrom = null;
            }

            try {
                $motion->setProposalVotingPropertiesFromRequest(
                    $this->getHttpRequest()->post('votingStatus', null),
                    $this->getHttpRequest()->post('votingBlockId', null),
                    $this->getHttpRequest()->post('votingItemBlockId', []),
                    $this->getHttpRequest()->post('votingItemBlockName', ''),
                    $this->getHttpRequest()->post('votingBlockTitle', ''),
                    true,
                    $ppChanges
                );
            } catch (FormError $e) {
                $response['success'] = false;
                $response['error']   = $e->getMessage();
                return json_encode($response);
            }

            if ($ppChanges->hasChanges()) {
                ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_SET_PROPOSAL, $motion->id, $ppChanges->jsonSerialize());
            }

            $response['success'] = false;
            if ($motion->save()) {
                $response['success'] = true;
            }

            $this->consultation->refresh();
            $response['html']        = $this->renderPartial('_set_proposed_procedure', [
                'motion'   => $motion,
                'msgAlert' => $msgAlert,
            ]);
            $response['proposalStr'] = $motion->getFormattedProposalStatus(true);
        }

        if ($this->getHttpRequest()->post('notifyProposer') || $this->getHttpRequest()->post('sendAgain')) {
            try {
                new MotionProposedProcedure(
                    $motion,
                    $this->getHttpRequest()->post('text'),
                    $this->getHttpRequest()->post('fromName'),
                    $this->getHttpRequest()->post('replyTo')
                );
                $motion->proposalNotification = date('Y-m-d H:i:s');
                $motion->save();
                $response['success'] = true;
                $response['html']    = $this->renderPartial('_set_proposed_procedure', [
                    'motion'   => $motion,
                    'msgAlert' => $msgAlert,
                    'context'  => $this->getHttpRequest()->post('context', 'view'),
                ]);
            } catch (MailNotSent $e) {
                $response['success'] = false;
                $response['error']   = 'The mail could not be sent: ' . $e->getMessage();
            }
        }

        if ($this->getHttpRequest()->post('setProposerHasAccepted')) {
            $motion->proposalUserStatus = Motion::STATUS_ACCEPTED;
            $motion->save();
            ConsultationLog::logCurrUser($motion->getMyConsultation(), ConsultationLog::MOTION_ACCEPT_PROPOSAL, $motion->id);
            $response['success'] = true;
            $response['html']    = $this->renderPartial('_set_proposed_procedure', [
                'motion'   => $motion,
                'msgAlert' => $msgAlert,
            ]);
        }

        if ($this->getHttpRequest()->post('writeComment')) {
            $adminComment               = new MotionAdminComment();
            $adminComment->userId       = User::getCurrentUser()->id;
            $adminComment->text         = $this->getHttpRequest()->post('writeComment');
            $adminComment->status       = MotionAdminComment::PROPOSED_PROCEDURE;
            $adminComment->dateCreation = date('Y-m-d H:i:s');
            $adminComment->motionId     = $motion->id;
            if (!$adminComment->save()) {
                $this->getHttpResponse()->statusCode = 500;
                $response['success']             = false;
                return json_encode($response);
            }

            $response['success'] = true;
            $response['comment'] = [
                'username'      => $adminComment->getMyUser()->name,
                'id'            => $adminComment->id,
                'text'          => $adminComment->text,
                'delLink'       => UrlHelper::createMotionUrl($motion, 'del-proposal-comment'),
                'dateFormatted' => Tools::formatMysqlDateTime($adminComment->dateCreation),
            ];
        }

        return json_encode($response);
    }
}
