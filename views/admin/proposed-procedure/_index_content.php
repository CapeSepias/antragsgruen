<?php

use app\components\HTMLTools;
use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\IMotion;
use app\models\proposedProcedure\Agenda;
use app\models\proposedProcedure\AgendaVoting;
use app\views\motion\LayoutHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var Agenda[] $proposedAgenda
 * @var bool $expandAll
 * @var null|string $expandId
 * @var null|string $tagId
 */

/** @var \app\controllers\Base $controller */
$controller = $this->context;

$hasResponsibilities = false;
foreach ($controller->consultation->motionTypes as $motionType) {
    if ($motionType->getSettingsObj()->hasResponsibilities) {
        $hasResponsibilities = true;
    }
}

$getRelevantItemsFromBlock = function(AgendaVoting $votingBlock) use ($tagId): array
{
    if ($tagId === null) {
        return $votingBlock->items;
    } else {
        return array_filter($votingBlock->items, function (IMotion $imotion) use ($tagId): bool {
            foreach ($imotion->getProposedProcedureTags() as $tag) {
                if ($tag->id === $tagId) {
                    return true;
                }
            }

            return false;
        });
    }
};

foreach ($proposedAgenda as $proposedItem) {
    if (count($proposedItem->votingBlocks) === 0) {
        continue;
    }

    if (!$expandAll && $tagId === null && $proposedItem->blockId !== $expandId) {
        $expandUrl   = UrlHelper::createUrl(['/admin/proposed-procedure/index', 'expandId' => $proposedItem->blockId]);
        $expandTitle = '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> ' . Html::encode($proposedItem->title);
        ?>
        <section class="motionHolder motionHolder<?= $proposedItem->blockId ?> proposedProcedureOverview openable">
            <h2 class="green">
                <?= Html::a($expandTitle, $expandUrl) ?>
            </h2>
        </section>
        <?php
        continue;
    }

    ?>
    <section class="motionHolder motionHolder<?= $proposedItem->blockId ?> proposedProcedureOverview">
        <h2 class="green">
            <?php
            if (!$expandAll) {
                echo '<span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> ';
            }
            ?>
            <?= Html::encode($proposedItem->title) ?>
        </h2>
        <div class="content">
            <?php
            foreach ($proposedItem->votingBlocks as $votingBlock) {
                $items = $getRelevantItemsFromBlock($votingBlock);
                if (count($items) === 0) {
                    continue;
                }
                ?>
                <table class="table votingTable votingTable<?= $votingBlock->getId() ?>">
                    <?php
                    if (count($proposedItem->votingBlocks) > 1 || $votingBlock->voting) {
                        ?>
                        <caption>
                            <?= Html::encode($votingBlock->title) ?>
                        </caption>
                        <?php
                    }
                    ?>
                    <thead>
                    <tr>
                        <th class="prefix"><?= Yii::t('con', 'proposal_table_motion') ?></th>
                        <th class="initiator"><?= Yii::t('con', 'proposal_table_initiator') ?></th>
                        <?php
                        if ($hasResponsibilities) {
                            ?>
                            <th class="responsibility"><?= Yii::t('con', 'proposal_table_response') ?></th>
                        <?php } ?>
                        <th class="procedure"><?= Yii::t('con', 'proposal_table_proposal') ?></th>
                        <th class="visible"><?= Yii::t('con', 'proposal_table_visible') ?></th>
                        <th class="comments"><?= Yii::t('con', 'proposal_table_comment') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $currentMotion = null;
                    foreach ($items as $item) {
                        if (is_a($item, Amendment::class)) {
                            $setVisibleUrl = UrlHelper::createUrl('admin/proposed-procedure/save-amendment-visible');
                            $type          = 'amendment';
                        } else {
                            $setVisibleUrl = UrlHelper::createUrl('admin/proposed-procedure/save-motion-visible');
                            $type          = 'motion';
                        }

                        $titlePre = '';
                        if (is_a($item, Amendment::class)) {
                            $classes = ['amendment' . $item->id];
                            if ($item->motionId == $currentMotion) {
                                $titlePre = '↳';
                            }
                        } else {
                            $classes       = ['motion' . $item->id];
                            $currentMotion = $item->id;
                        }
                        if ($item->status === IMotion::STATUS_WITHDRAWN) {
                            $classes[] = 'withdrawn';
                        }
                        if ($item->status === IMotion::STATUS_MOVED) {
                            $classes[] = 'moved';
                        }
                        if ($item->proposalUserStatus === IMotion::STATUS_ACCEPTED) {
                            $classes[] = 'accepted';
                        }
                        if ($item->proposalStatus === IMotion::STATUS_VOTE) {
                            $classes[] = 'vote';
                        }
                        ?>
                        <tr class="item <?= implode(' ', $classes) ?>" data-id="<?= $item->id ?>">
                            <td class="prefix">
                                <?php
                                if (is_a($item, Amendment::class)) {
                                    /** @var Amendment $item */
                                    echo HTMLTools::amendmentDiffTooltip($item, 'bottom');
                                }
                                echo Html::a(Html::encode($titlePre . $item->titlePrefix), $item->getLink())
                                ?>
                            </td>
                            <td class="initiator">
                                <?php
                                $consultation = $item->getMyConsultation();
                                echo LayoutHelper::formatInitiators($item->getInitiators(), $consultation, true, true);
                                ?>
                            </td>
                            <?php
                            if ($hasResponsibilities) {
                                echo '<td class="responsibilityCol">';
                                echo $this->render('../motion-list/_responsibility_dropdown', [
                                    'imotion' => $item,
                                    'type'    => $type,
                                ]);
                                echo '</td>';
                            }
                            ?>
                            <td class="procedure">
                                <?php
                                echo $this->render('_status_icons', ['entry' => $item, 'show_visibility' => false]);
                                echo Agenda::formatProposedProcedure($item, Agenda::FORMAT_HTML);
                                if (count($item->getProposedProcedureTags()) > 0) {
                                    $tags = [];
                                    foreach ($item->getProposedProcedureTags() as $tag) {
                                        $tags[] = Html::encode($tag->title);
                                    }
                                    echo '<small style="color: gray;">' . implode(', ', $tags) . '</small>';
                                }
                                ?></td>
                            <td class="visible">
                                <input type="checkbox" name="visible"
                                       title="<?= Yii::t('con', 'proposal_table_visible') ?>"
                                       data-save-url="<?= Html::encode($setVisibleUrl) ?>"
                                    <?= ($item->proposalVisibleFrom ? 'checked' : '') ?>>
                            </td>
                            <?= $this->render('_index_comment', ['item' => $item]) ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            }
            ?>
        </div>
    </section>
    <?php
}
