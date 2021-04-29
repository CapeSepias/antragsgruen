import "./MotionSupporterEdit";
import { MotionSupporterEdit } from "./MotionSupporterEdit";
import { AntragsgruenEditor } from "../shared/AntragsgruenEditor";
import editor = CKEDITOR.editor;
import { AmendmentEditSinglePara } from "../shared/AmendmentEditSinglePara";

export class AmendmentEdit {
    private lang: string;

    private $editTextCaller: JQuery;

    private textEditCalledMultiPara() {
        $(".wysiwyg-textarea").each(function () {
            let $holder = $(this),
                $textarea = $holder.find(".texteditor");

            let editor: AntragsgruenEditor = new AntragsgruenEditor($textarea.attr("id")),
                ckeditor: editor = editor.getEditor();

            $textarea.parents("form").on("submit", function () {
                $textarea.parent().find("textarea.raw").val(ckeditor.getData());
                if (typeof (ckeditor.plugins.lite) != 'undefined') {
                    ckeditor.plugins.lite.findPlugin(ckeditor).acceptAll();
                    $textarea.parent().find("textarea.consolidated").val(ckeditor.getData());
                }
            });
        });
    }

    private textEditCalled() {
        this.$editTextCaller.addClass("hidden");
        $("#amendmentTextEditHolder").removeClass("hidden");
        if (this.$editTextCaller.data("multiple-paragraphs")) {
            this.textEditCalledMultiPara();
        } else {
            new AmendmentEditSinglePara();
        }
        $("#amendmentUpdateForm").append("<input type='hidden' name='edittext' value='1'>");
    };

    private initVotingFunctions() {
        const $closer = $(".votingResultCloser"),
            $opener = $(".votingResultOpener"),
            $inputRows = $(".contentVotingResult, .contentVotingResultComment");
        $opener.on("click", () => {
            $closer.removeClass("hidden");
            $opener.addClass("hidden");
            $inputRows.removeClass("hidden");
        });
        $closer.on("click", () => {
            $closer.addClass("hidden");
            $opener.removeClass("hidden");
            $inputRows.addClass("hidden");
        });
    }

    constructor() {
        this.lang = $("html").attr("lang");
        this.$editTextCaller = $("#amendmentTextEditCaller");

        $("#amendmentDateCreationHolder").datetimepicker({
            locale: this.lang
        });
        $("#amendmentDateResolutionHolder").datetimepicker({
            locale: this.lang
        });
        $('#resolutionDateHolder').datetimepicker({
            locale: $('#resolutionDate').data('locale'),
            format: 'L'
        });

        this.$editTextCaller.find("button").on("click", this.textEditCalled.bind(this));

        $('.wysiwyg-textarea .resetText').on("click", (ev) => {
            let $text: JQuery = $(ev.currentTarget).parents('.wysiwyg-textarea').find('.texteditor');
            window['CKEDITOR']['instances'][$text.attr('id')].setData($text.data('original-html'));

            $(ev.currentTarget).parents('.modifiedActions').addClass('hidden');
        });

        $(".amendmentDeleteForm").on("submit", function (ev, data) {
            if (data && typeof (data.confirmed) && data.confirmed === true) {
                return;
            }
            let $form = $(this);
            ev.preventDefault();
            bootbox.confirm(__t("admin", "delAmendmentConfirm"), function (result) {
                if (result) {
                    $form.trigger("submit", {'confirmed': true});
                }
            });
        });

        this.initVotingFunctions();

        new MotionSupporterEdit($("#motionSupporterHolder"));
    }
}
