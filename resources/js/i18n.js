import {createI18n} from 'vue-i18n';
import ruMain from './Locales/ru/main.js'
import kzMain from './Locales/kz/main.js'

const messages = {
    kz: {
        main: kzMain
    },
    ru: {
        main: ruMain
    }
}

const i18n = createI18n({
    locale: 'ru',
    fallbackLocale: 'ru',
    messages,
});

export default i18n;
