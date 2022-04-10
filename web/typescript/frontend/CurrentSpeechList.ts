import { VueConstructor } from 'vue';

declare var Vue: VueConstructor;

export class CurrentSpeechList {
    private widget;

    constructor(private $element: JQuery) {
        const $vueEl = this.$element.find(".currentSpeechList");
        const data = {
            queue: $element.data('queue'),
            user: $element.data('user'),
            csrf: $("head").find("meta[name=csrf-token]").attr("content") as string,
            title: $element.data('title'),
            adminUrl: $element.data('admin-url'),
        };
        if ($element.hasClass('currentSpeechFullPage')) {
            this.widget = new Vue({
                el: $vueEl[0],
                template: `
                    <speech-user-full-list-widget :queue="queue" :user="user" :csrf="csrf" :title="title"></speech-user-full-list-widget>`,
                data
            });
        } else if ($element.hasClass('currentSpeechInline')) {
            this.widget = new Vue({
                el: $vueEl[0],
                template: `
                    <speech-user-inline-widget :queue="queue" :user="user" :csrf="csrf" :title="title"></speech-user-inline-widget>`,
                data
            });
        } else {
            this.widget = new Vue({
                el: $vueEl[0],
                template: `
                    <speech-user-footer-widget :queue="queue" :user="user" :csrf="csrf" :title="title" :adminUrl="adminUrl"></speech-user-footer-widget>`,
                data
            });
        }
    }
}
