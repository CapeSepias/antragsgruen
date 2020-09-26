define(["require","exports"],function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.ResponsibilitySetter=void 0;var r=function(){function e(e){(this.$list=e).on("click",".respHolder .respUser",this.userSelected.bind(this)),e.on("click",".respHolder .respCommentRow button",this.onCommentSaveBtn.bind(this)),e.on("keypress",".respHolder .respCommentRow input[type=text]",this.onKeyPressed.bind(this))}return e.prototype.userSelected=function(e){e.preventDefault();var t=$(e.currentTarget),r=t.parents(".respHolder").first(),s=t.data("user-id"),n=t.find(".name").text(),o=r.data("save-url");$.post(o,{_csrf:$("input[name=_csrf]").val(),user:s},function(e){e.success?(r.find(".respUser").removeClass("selected"),t.addClass("selected"),r.find(".responsibilityUser").text(n).data("user-id",s)):alert("An error occurred while saving")})},e.prototype.onCommentSaveBtn=function(e){e.preventDefault();var t=$(e.currentTarget).parents(".respHolder").first();this.saveComment(t)},e.prototype.onKeyPressed=function(e){if(13==e.keyCode){e.preventDefault(),e.stopPropagation();var t=$(e.currentTarget).parents(".respHolder").first();this.saveComment(t)}},e.prototype.saveComment=function(t){var r=t.find(".respCommentRow input[type=text]").val(),e=t.data("save-url");$.post(e,{_csrf:$("input[name=_csrf]").val(),comment:r},function(e){e.success?(t.find(".responsibilityComment").text(r),t.hasClass("open")&&t.find(".dropdown-toggle").dropdown("toggle")):alert("An error occurred while saving")})},e}();t.ResponsibilitySetter=r});
//# sourceMappingURL=ResponsibilitySetter.js.map
