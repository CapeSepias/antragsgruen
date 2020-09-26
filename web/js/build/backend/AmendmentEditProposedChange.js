define(["require","exports","../shared/AntragsgruenEditor","../frontend/MotionMergeAmendments"],function(t,e,a,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.AmendmentEditProposedChange=void 0;var n=function(){function e(t){this.$form=t,this.hasChanged=!1,this.textEditCalled(),this.initCollisionDetection(),t.on("submit",function(){$(window).off("beforeunload",e.onLeavePage)})}return e.prototype.textEditCalled=function(){var o=this;$(".wysiwyg-textarea:not(#sectionHolderEditorial)").each(function(t,e){var n=$(e).find(".texteditor"),i=new a.AntragsgruenEditor(n.attr("id")).getEditor();n.parents("form").on("submit",function(){n.parent().find("textarea.raw").val(i.getData()),void 0!==i.plugins.lite&&(i.plugins.lite.findPlugin(i).acceptAll(),n.parent().find("textarea.consolidated").val(i.getData()))}),$("#"+n.attr("id")).on("keypress",o.onContentChanged.bind(o))}),this.$form.find(".resetText").on("click",function(t){var e=$(t.currentTarget).parents(".wysiwyg-textarea").find(".texteditor");window.CKEDITOR.instances[e.attr("id")].setData(e.data("original-html")),$(t.currentTarget).parents(".modifiedActions").addClass("hidden")})},e.prototype.initCollisionDetection=function(){var n=this;this.$collisionIndicator=this.$form.find("#collisionIndicator"),window.setInterval(function(){var t=n.getTextConsolidatedSections(),e=n.$form.data("collision-check-url");$.post(e,{_csrf:n.$form.find("> input[name=_csrf]").val(),sections:t},function(t){if(0==t.collisions.length)n.$collisionIndicator.addClass("hidden");else{n.$collisionIndicator.removeClass("hidden");var e="";t.collisions.forEach(function(t){e+=t.html}),n.$collisionIndicator.find(".collisionList").html(e)}})},5e3)},e.prototype.getTextConsolidatedSections=function(){var r={};return $(".proposedVersion .wysiwyg-textarea:not(#sectionHolderEditorial)").each(function(t,e){var n=$(e),i=n.find(".texteditor"),o=n.parents(".proposedVersion").data("section-id"),a=i.clone(!1);a.find(".ice-ins").each(function(t,e){s.MotionMergeChangeActions.insertAccept(e)}),a.find(".ice-del").each(function(t,e){s.MotionMergeChangeActions.deleteAccept(e)}),r[o]=a.html()}),r},e.onLeavePage=function(){return __t("std","leave_changed_page")},e.prototype.onContentChanged=function(){this.hasChanged||(this.hasChanged=!0,$("body").hasClass("testing")||$(window).on("beforeunload",e.onLeavePage))},e}();e.AmendmentEditProposedChange=n});
//# sourceMappingURL=AmendmentEditProposedChange.js.map
