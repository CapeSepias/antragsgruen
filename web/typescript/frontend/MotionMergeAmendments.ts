import { AntragsgruenEditor } from "../shared/AntragsgruenEditor";
import editor = CKEDITOR.editor;

const STATUS_ACCEPTED = 4;
const STATUS_MODIFIED_ACCEPTED = 6;
const STATUS_PROCESSED = 17;
const STATUS_ADOPTED = 8;
const STATUS_COMPLETED = 9;

class AmendmentStatuses {
    private static statuses: { [amendmentId: number]: number };
    private static statusListeners: { [amendmentId: number]: MotionMergeAmendmentsParagraph[] } = {};

    public static init(statuses: { [amendmentId: number]: number }) {
        console.log("Init statuses");
        AmendmentStatuses.statuses = statuses;
        Object.keys(statuses).forEach(amendmentId => {
            AmendmentStatuses.statusListeners[amendmentId] = [];
        });
    }

    public static getAmendmentStatus(amendmentId: number): number {
        return AmendmentStatuses.statuses[amendmentId];
    }

    public static registerParagraph(amendmentId: number, paragraph: MotionMergeAmendmentsParagraph) {
        AmendmentStatuses.statusListeners[amendmentId].push(paragraph);
    }

    public static setStatus(amendmentId: number, status: number) {
        AmendmentStatuses.statuses[amendmentId] = status;
        AmendmentStatuses.statusListeners[amendmentId].forEach(paragraph => {
            paragraph.onAmendmentStatusChanged(amendmentId, status);
        });
    }

    public static getAll(): { [amendmentId: number]: number } {
        return AmendmentStatuses.statuses;
    }
}

export class MotionMergeChangeActions {
    public static removeEmptyParagraphs() {
        $('.paragraphHolder').each((i, el) => {
            if (el.childNodes.length == 0) {
                $(el).remove();
            }
        });
    }

    public static accept(node: Element, onFinished: () => void = null) {
        let $node = $(node);
        if ($node.hasClass("ice-ins")) {
            MotionMergeChangeActions.insertAccept(node, onFinished);
        }
        if ($node.hasClass("ice-del")) {
            MotionMergeChangeActions.deleteAccept(node, onFinished);
        }
    }

    public static reject(node: Element, onFinished: () => void = null) {
        let $node = $(node);
        if ($node.hasClass("ice-ins")) {
            MotionMergeChangeActions.insertReject($node, onFinished);
        }
        if ($node.hasClass("ice-del")) {
            MotionMergeChangeActions.deleteReject($node, onFinished);
        }
    }

    public static insertReject($node: JQuery, onFinished: () => void = null) {
        let $removeEl: JQuery,
            name = $node[0].nodeName.toLowerCase();
        if (name == 'li') {
            $removeEl = $node.parent();
        } else {
            $removeEl = $node;
        }
        if (name == 'ul' || name == 'ol' || name == 'li' || name == 'blockquote' || name == 'pre' || name == 'p') {
            $removeEl.css("overflow", "hidden").height($removeEl.height());
            $removeEl.animate({"height": "0"}, 250, () => {
                $removeEl.remove();
                $(".collidingParagraph:empty").remove();
                MotionMergeChangeActions.removeEmptyParagraphs();
                if (onFinished) onFinished();
            });
        } else {
            $removeEl.remove();
            if (onFinished) onFinished();
        }
    }

    public static insertAccept(node: Element, onFinished: () => void = null) {
        let $this: JQuery = $(node);
        $this.removeClass("ice-cts ice-ins appendHint moved");
        $this.removeAttr("data-moving-partner data-moving-partner-id data-moving-partner-paragraph data-moving-msg");
        if (node.nodeName.toLowerCase() == 'ul' || node.nodeName.toLowerCase() == 'ol') {
            $this.children().removeClass("ice-cts").removeClass("ice-ins").removeClass("appendHint");
        }
        if (node.nodeName.toLowerCase() == 'li') {
            $this.parent().removeClass("ice-cts").removeClass("ice-ins").removeClass("appendHint");
        }
        if (node.nodeName.toLowerCase() == 'ins') {
            $this.replaceWith($this.html());
        }
        if (onFinished) onFinished();
    }

    public static deleteReject($node: JQuery, onFinished: () => void = null) {
        $node.removeClass("ice-cts ice-del appendHint");
        $node.removeAttr("data-moving-partner data-moving-partner-id data-moving-partner-paragraph data-moving-msg");
        let nodeName = $node[0].nodeName.toLowerCase();
        if (nodeName == 'ul' || nodeName == 'ol') {
            $node.children().removeClass("ice-cts").removeClass("ice-del").removeClass("appendHint");
        }
        if (nodeName == 'li') {
            $node.parent().removeClass("ice-cts").removeClass("ice-del").removeClass("appendHint");
        }
        if (nodeName == 'del') {
            $node.replaceWith($node.html());
        }
        if (onFinished) onFinished();
    }

    public static deleteAccept(node: Element, onFinished: () => void = null) {
        let name = node.nodeName.toLowerCase(),
            $removeEl: JQuery;
        if (name == 'li') {
            $removeEl = $(node).parent();
        } else {
            $removeEl = $(node);
        }

        if (name == 'ul' || name == 'ol' || name == 'li' || name == 'blockquote' || name == 'pre' || name == 'p') {
            $removeEl.css("overflow", "hidden").height($removeEl.height());
            $removeEl.animate({"height": "0"}, 250, () => {
                $removeEl.remove();
                $(".collidingParagraph:empty").remove();
                MotionMergeChangeActions.removeEmptyParagraphs();
                if (onFinished) onFinished();
            });
        } else {
            $removeEl.remove();
            if (onFinished) onFinished();
        }
    }
}


class MotionMergeChangeTooltip {
    constructor(private $element: JQuery, mouseX: number, mouseY: number, private parent: MotionMergeAmendmentsTextarea) {
        let positionX: number = null,
            positionY: number = null;
        $element.popover({
            'container': 'body',
            'animation': false,
            'trigger': 'manual',
            'placement': function (popover) {
                let $popover = $(popover);
                window.setTimeout(() => {
                    let width = $popover.width(),
                        elTop = $element.offset().top,
                        elHeight = $element.height();
                    if (positionX === null && width > 0) {
                        positionX = (mouseX - width / 2);
                        positionY = mouseY + 10;
                        if (positionY < (elTop + 19)) {
                            positionY = elTop + 19;
                        }
                        if (positionY > elTop + elHeight) {
                            positionY = elTop + elHeight;
                        }
                    }
                    $popover.css("left", positionX + "px");
                    $popover.css("top", positionY + "px");
                }, 1);
                return "bottom";
            },
            'html': true,
            'content': this.getContent.bind(this)
        });

        $element.popover('show');
        let $popover: JQuery = $element.find("> .popover");
        $popover.on("mousemove", (ev) => {
            ev.stopPropagation();
        });
        window.setTimeout(this.removePopupIfInactive.bind(this), 1000);
    }

    private getContent() {
        let $myEl: JQuery = this.$element,
            html,
            cid = $myEl.data("cid");
        if (cid == undefined) {
            cid = $myEl.parent().data("cid");
        }
        $myEl.parents(".texteditor").first().find("[data-cid=" + cid + "]").addClass("hover");

        html = '<div>';
        html += '<button type="button" class="accept btn btn-sm btn-default"></button>';
        html += '<button type="button" class="reject btn btn-sm btn-default"></button>';
        html += '<a href="#" class="btn btn-small btn-default opener" target="_blank"><span class="glyphicon glyphicon-new-window"></span></a>';
        html += '<div class="initiator" style="font-size: 0.8em;"></div>';
        html += '</div>';
        let $el: JQuery = $(html);
        $el.find(".opener").attr("href", $myEl.data("link")).attr("title", __t("merge", "title_open_in_blank"));
        $el.find(".initiator").text(__t("merge", "initiated_by") + ": " + $myEl.data("username"));
        if ($myEl.hasClass("ice-ins")) {
            $el.find("button.accept").text(__t("merge", "change_accept")).click(this.accept.bind(this));
            $el.find("button.reject").text(__t("merge", "change_reject")).click(this.reject.bind(this));
        } else if ($myEl.hasClass("ice-del")) {
            $el.find("button.accept").text(__t("merge", "change_accept")).click(this.accept.bind(this));
            $el.find("button.reject").text(__t("merge", "change_reject")).click(this.reject.bind(this));
        } else if ($myEl[0].nodeName.toLowerCase() == 'li') {
            let $list = $myEl.parent();
            if ($list.hasClass("ice-ins")) {
                $el.find("button.accept").text(__t("merge", "change_accept")).click(this.accept.bind(this));
                $el.find("button.reject").text(__t("merge", "change_reject")).click(this.reject.bind(this));
            } else if ($list.hasClass("ice-del")) {
                $el.find("button.accept").text(__t("merge", "change_accept")).click(this.accept.bind(this));
                $el.find("button.reject").text(__t("merge", "change_reject")).click(this.reject.bind(this));
            } else {
                console.log("unknown", $list);
            }
        } else {
            console.log("unknown", $myEl);
            alert("unknown");
        }
        return $el;
    }

    private removePopupIfInactive() {
        if (this.$element.is(":hover")) {
            return window.setTimeout(this.removePopupIfInactive.bind(this), 1000);
        }
        if ($("body").find(".popover:hover").length > 0) {
            return window.setTimeout(this.removePopupIfInactive.bind(this), 1000);
        }
        this.destroy();
    }

    private affectedChangesets() {
        let cid = this.$element.data("cid");
        if (cid == undefined) {
            cid = this.$element.parent().data("cid");
        }
        return this.$element.parents(".texteditor").find("[data-cid=" + cid + "]");
    }

    private performActionWithUI(action) {
        let scrollX = window.scrollX,
            scrollY = window.scrollY;

        this.parent.saveEditorSnapshot();
        this.destroy();
        action.call(this);
        this.parent.focusTextarea();

        window.scrollTo(scrollX, scrollY);
    }

    private accept() {
        this.performActionWithUI(() => {
            this.affectedChangesets().each((i, el) => {
                MotionMergeChangeActions.accept(el, () => {
                    this.parent.onChanged();
                });
            });
        });
    }

    private reject() {
        this.performActionWithUI(() => {
            this.affectedChangesets().each((i, el) => {
                MotionMergeChangeActions.reject(el, () => {
                    this.parent.onChanged();
                });
            });
        });
    }

    public destroy() {
        this.$element.popover("hide").popover("destroy");

        let cid = this.$element.data("cid");
        if (cid == undefined) {
            cid = this.$element.parent().data("cid");
        }
        this.$element.parents(".texteditor").first().find("[data-cid=" + cid + "]").removeClass("hover");

        try {
            // Remove stale objects that were not removed correctly previously
            const $popovers = $(".popover");
            $popovers.popover("hide").popover("destroy");
            $popovers.remove();
        } catch (e) {
        }
    }
}

class MotionMergeAmendmentsTextarea {
    private texteditor: editor;
    private unchangedText: string = null;
    private hasChanged: boolean = false;

    private prepareText(html: string) {
        let $text: JQuery = $('<div>' + html + '</div>');

        // Move the amendment-Data from OL's and UL's to their list items
        $text.find("ul.appendHint, ol.appendHint").each((i, el) => {
            let $this: JQuery = $(el),
                appendHint = $this.data("append-hint");
            $this.find("> li").addClass("appendHint").attr("data-append-hint", appendHint)
                .attr("data-link", $this.data("link"))
                .attr("data-username", $this.data("username"));
            $this.removeClass("appendHint").removeData("append-hint");
        });

        // Remove double markup
        $text.find(".moved .moved").removeClass('moved');
        $text.find(".moved").each(this.markupMovedParagraph.bind(this));

        // Add hints about starting / ending collisions
        $text.find(".hasCollisions")
            .attr("data-collision-start-msg", __t('merge', 'colliding_start'))
            .attr("data-collision-end-msg", __t('merge', 'colliding_end'));

        let newText = $text.html();
        this.texteditor.setData(newText);
        this.unchangedText = this.normalizeHtml(this.texteditor.getData());
        this.texteditor.fire('saveSnapshot');
        this.onChanged();
    }

    private markupMovedParagraph(i, el) {
        let $node = $(el),
            paragraphNew = $node.data('moving-partner-paragraph'),
            msg: string;

        if ($node.hasClass('inserted')) {
            msg = __t('std', 'moved_paragraph_from');
        } else {
            msg = __t('std', 'moved_paragraph_to');
        }
        msg = msg.replace(/##PARA##/, (paragraphNew + 1));

        if ($node[0].nodeName === 'LI') {
            $node = $node.parent();
        }

        $node.attr("data-moving-msg", msg);
    }

    private initializeTooltips() {
        this.$holder.on("mouseover", ".appendHint", (ev) => {
            if (MotionMergeAmendments.activePopup) {
                MotionMergeAmendments.activePopup.destroy();
            }
            MotionMergeAmendments.activePopup = new MotionMergeChangeTooltip(
                $(ev.currentTarget), ev.pageX, ev.pageY, this
            );
        });
    }

    private acceptAll() {
        this.texteditor.fire('saveSnapshot');
        this.$holder.find(".collidingParagraph").each((i, el) => {
            let $this = $(el);
            $this.find(".collidingParagraphHead").remove();
            $this.replaceWith($this.children());
        });
        this.$holder.find(".ice-ins").each((i, el) => {
            MotionMergeChangeActions.insertAccept(el);
        });
        this.$holder.find(".ice-del").each((i, el) => {
            MotionMergeChangeActions.deleteAccept(el);
        });
    }

    private rejectAll() {
        this.texteditor.fire('saveSnapshot');
        this.$holder.find(".collidingParagraph").each((i, el) => {
            $(el).remove();
        });
        this.$holder.find(".ice-ins").each((i, el) => {
            MotionMergeChangeActions.insertReject($(el));
        });
        this.$holder.find(".ice-del").each((i, el) => {
            MotionMergeChangeActions.deleteReject($(el));
        });
    }

    public saveEditorSnapshot() {
        this.texteditor.fire('saveSnapshot');
    }

    public focusTextarea() {
        //this.$holder.find(".texteditor").focus();
        // This lead to strange cursor behavior, e.g. when removing a colliding paragraph
    }

    public getContent(): string {
        return this.texteditor.getData();
    }

    public setText(html: string) {
        this.prepareText(html);
        this.initializeTooltips();
    }

    private normalizeHtml(html: string) {
        const entities = {
            '&nbsp;': ' ',
            '&ndash;': '-',
            '&auml;': 'ä',
            '&ouml;': 'ö',
            '&uuml;': 'ü',
            '&Auml;': 'Ä',
            '&Ouml;': 'Ö',
            '&Uuml;': 'Ü',
            '&szlig;': 'ß',
            '&bdquo;': '„',
            '&ldquo;': '“',
            '&bull;': '•',
            '&sect;': '§',
            '&eacute;': 'é',
            '&rsquo;': '’',
            '&euro;': '€'
        };
        Object.keys(entities).forEach(ent => {
            html = html.replace(new RegExp(ent, 'g'), entities[ent]);
        });

        return html.replace(/\s+</g, '<').replace(/>\s+/g, '>').replace(/<[^>]*>/g, '');
    }

    public onChanged() {
        if (this.normalizeHtml(this.texteditor.getData()) === this.unchangedText) {
            this.$changedIndicator.addClass("hidden");
            this.hasChanged = false;
        } else {
            this.$changedIndicator.removeClass("hidden");
            this.hasChanged = true;
        }
    }

    public hasChanges(): boolean {
        return this.hasChanged;
    }

    constructor(private $holder: JQuery, private $changedIndicator: JQuery) {
        let $textarea = $holder.find(".texteditor");
        let edit = new AntragsgruenEditor($textarea.attr("id"));
        this.texteditor = edit.getEditor();
        MotionMergeAmendments.addSubmitListener(() => {
            $holder.find("textarea.raw").val(this.texteditor.getData());
            $holder.find("textarea.consolidated").val(this.texteditor.getData());
        });

        this.setText(this.texteditor.getData());

        this.$holder.find(".acceptAllChanges").click(this.acceptAll.bind(this));
        this.$holder.find(".rejectAllChanges").click(this.rejectAll.bind(this));

        this.texteditor.on('change', this.onChanged.bind(this));
    }
}

class MotionMergeAmendmentsParagraph {
    public sectionId: number;
    public paragraphId: number;
    public textarea: MotionMergeAmendmentsTextarea;

    constructor(private $holder: JQuery) {
        this.sectionId = parseInt($holder.data('sectionId'));
        this.paragraphId = parseInt($holder.data('paragraphId'));

        const $textarea = $holder.find(".wysiwyg-textarea");
        const $changed = $holder.find(".changedIndicator");
        this.textarea = new MotionMergeAmendmentsTextarea($textarea, $changed);

        this.initButtons();
        $holder.find(".amendmentStatus").each((i: number, element) => {
            AmendmentStatuses.registerParagraph($(element).data("amendment-id"), this);
        });
    }

    private initButtons() {
        this.$holder.find('.toggleAmendment').click((ev) => {
            const $input = $(ev.currentTarget).find(".amendmentActive");
            const doToggle = () => {
                if (parseInt($input.val()) === 1) {
                    $input.val("0");
                    $input.parents(".btn-group").find(".btn").addClass("btn-default").removeClass("btn-success");
                } else {
                    $input.val("1");
                    $input.parents(".btn-group").find(".btn").removeClass("btn-default").addClass("btn-success");
                }
                this.reloadText();
            };

            if (this.textarea.hasChanges()) {
                bootbox.confirm(__t('merge', 'reloadParagraph'), (result) => {
                    if (result) {
                        doToggle();
                    }
                });
            } else {
                doToggle();
            }
        });

        const initTooltip = ($holder: JQuery) => {
            const amendmentId = parseInt($holder.data("amendment-id"));
            const currentStatus = AmendmentStatuses.getAmendmentStatus(amendmentId);

            $holder.find(".dropdown-menu .selected").removeClass("selected");
            $holder.find(".dropdown-menu .status" + currentStatus).addClass("selected");
        };

        this.$holder.find('.btn-group.amendmentStatus').on('show.bs.dropdown', ev => {
            initTooltip($(ev.currentTarget))
        });

        this.$holder.find(".btn-group .setStatus").click(ev => {
            ev.preventDefault();
            const $holder = $(ev.currentTarget).parents(".btn-group");
            const amendmentId = parseInt($holder.data("amendment-id"));
            AmendmentStatuses.setStatus(amendmentId, parseInt($(ev.currentTarget).data("status")));
            initTooltip($holder);
        });
    }

    public onAmendmentStatusChanged(amendmentId: number, status: number) {
        if (this.textarea.hasChanges()) {
            console.log("Skipping, as there are changes");
            return;
        }
        const $holder = this.$holder.find(".amendmentStatus[data-amendment-id=" + amendmentId + "]");
        const $btn = $holder.find(".btn");
        const $input = $holder.find("input.amendmentActive");
        if ([
            STATUS_ACCEPTED,
            STATUS_MODIFIED_ACCEPTED,
            STATUS_PROCESSED,
            STATUS_ADOPTED,
            STATUS_COMPLETED
        ].indexOf(status) !== -1) {
            $input.val("1");
            $btn.removeClass("btn-default").addClass("btn-success");
        } else {
            $input.val("0");
            $btn.addClass("btn-default").removeClass("btn-success");
        }
        this.reloadText();
    }

    private reloadText() {
        const amendmentIds = [];
        this.$holder.find(".amendmentActive[value='1']").each((i, el) => {
            amendmentIds.push(parseInt($(el).data('amendment-id')));
        });
        const url = this.$holder.data("reload-url").replace('DUMMY', amendmentIds.join(","));
        $.get(url, (data) => {
            this.textarea.setText(data.text);

            let collisions = '';
            data.collisions.forEach(str => {
                collisions += str;
            });

            this.$holder.find(".collisionsHolder").html(collisions);
            if (data.collisions.length > 0) {
                this.$holder.addClass("hasCollisions");
            } else {
                this.$holder.removeClass("hasCollisions");
            }
        });
    }

    public getDraftData() {
        const amendmentToggles = {};
        this.$holder.find(".amendmentStatus").each((id, el) => {
            const $el = $(el);
            amendmentToggles[$el.data("amendment-id")] = ($el.find(".btn-success").length > 0);
        });
        return {
            amendmentToggles,
            text: this.textarea.getContent(),
        };
    }
}

/**
 * Singleton object
 */
export class MotionMergeAmendments {
    public static activePopup: MotionMergeChangeTooltip = null;
    public static currMouseX: number = null;
    public static $form;

    public $draftSavingPanel: JQuery;
    private paragraphs: MotionMergeAmendmentsParagraph[] = [];

    constructor($form: JQuery) {
        MotionMergeAmendments.$form = $form;
        AmendmentStatuses.init($form.data("amendment-statuses"));

        $(".paragraphWrapper").each((i, el) => {
            const $para = $(el);
            $para.find(".wysiwyg-textarea").on("mousemove", (ev) => {
                MotionMergeAmendments.currMouseX = ev.offsetX;
            });

            this.paragraphs.push(new MotionMergeAmendmentsParagraph($para));
        });

        MotionMergeAmendments.$form.on("submit", () => {
            $(window).off("beforeunload", MotionMergeAmendments.onLeavePage);
        });
        $(window).on("beforeunload", MotionMergeAmendments.onLeavePage);

        this.initDraftSaving();
    }

    public static onLeavePage(): string {
        return __t("std", "leave_changed_page");
    }

    public static addSubmitListener(cb) {
        this.$form.submit(cb);
    }

    private setDraftDate(date: Date) {
        this.$draftSavingPanel.find(".lastSaved .none").hide();

        let options = {
                year: 'numeric', month: 'numeric', day: 'numeric',
                hour: 'numeric', minute: 'numeric',
                hour12: false
            },
            lang: string = $("html").attr("lang"),
            formatted = new Intl.DateTimeFormat(lang, options).format(date);

        this.$draftSavingPanel.find(".lastSaved .value").text(formatted);
    }

    private saveDraft() {
        let data = {
            "amendmentStatuses": AmendmentStatuses.getAll(),
            "paragraphs": {},
        };
        this.paragraphs.forEach(para => {
            data.paragraphs[para.sectionId + '_' + para.paragraphId] = para.getDraftData();
        });
        let isPublic: boolean = this.$draftSavingPanel.find('input[name=public]').prop('checked');

        $.ajax({
            type: "POST",
            url: MotionMergeAmendments.$form.data('draftSaving'),
            data: {
                'public': (isPublic ? 1 : 0),
                'data': JSON.stringify(data),
                '_csrf': MotionMergeAmendments.$form.find('> input[name=_csrf]').val()
            },
            success: (ret) => {
                if (ret['success']) {
                    this.$draftSavingPanel.find('.savingError').addClass('hidden');
                    this.setDraftDate(new Date(ret['date']));
                    if (isPublic) {
                        MotionMergeAmendments.$form.find('.publicLink').removeClass('hidden');
                    } else {
                        MotionMergeAmendments.$form.find('.publicLink').addClass('hidden');
                    }
                } else {
                    this.$draftSavingPanel.find('.savingError').removeClass('hidden');
                    this.$draftSavingPanel.find('.savingError .errorNetwork').addClass('hidden');
                    this.$draftSavingPanel.find('.savingError .errorHolder').text(ret['error']).removeClass('hidden');
                }
            },
            error: () => {
                this.$draftSavingPanel.find('.savingError').removeClass('hidden');
                this.$draftSavingPanel.find('.savingError .errorNetwork').removeClass('hidden');
                this.$draftSavingPanel.find('.savingError .errorHolder').text('').addClass('hidden');
            }
        });
    }

    private initAutosavingDraft() {
        let $toggle: JQuery = this.$draftSavingPanel.find('input[name=autosave]');

        window.setInterval(() => {
            if ($toggle.prop('checked')) {
                this.saveDraft();
            }
        }, 5000);

        if (localStorage) {
            let state = localStorage.getItem('merging-draft-auto-save');
            if (state !== null) {
                $toggle.prop('checked', (state == '1'));
            }
        }
        $toggle.change(() => {
            let active: boolean = $toggle.prop('checked');
            if (localStorage) {
                localStorage.setItem('merging-draft-auto-save', (active ? '1' : '0'));
            }
        }).trigger('change');
    }

    private initDraftSaving() {
        this.$draftSavingPanel = MotionMergeAmendments.$form.find('#draftSavingPanel');
        this.$draftSavingPanel.find('.saveDraft').on('click', this.saveDraft.bind(this));
        this.$draftSavingPanel.find('input[name=public]').on('change', this.saveDraft.bind(this));
        this.initAutosavingDraft();

        if (this.$draftSavingPanel.data("resumed-date")) {
            let date = new Date(this.$draftSavingPanel.data("resumed-date"));
            this.setDraftDate(date);
        }

        $("#yii-debug-toolbar").remove();
    }
}
