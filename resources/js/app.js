import Vue from 'vue';
import ElementUI from 'element-ui';
import VueEcho from 'vue-echo-laravel';
import 'element-ui/lib/theme-chalk/index.css';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

Vue.prototype.$http = window.axios;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue')
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue')
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue')
);

Vue.component(
    'stock-row-card',
    require('./components/pages/stocks/RowCard.vue').default
);

Vue.component(
    'event-receiver',
    require('./components/global/EventReceiver').default
);

Vue.component(
    'stock-today-card',
    require('./components/pages/stocks/TodayCard').default
);

Vue.component(
    'dashboard-portfolios',
    require('./components/pages/home/DashboardPortfolios').default
);

Vue.component(
    'dashboard-portfolio-card',
    require('./components/pages/home/DashboardPortfolioCard').default
);

Vue.component(
    'stock-summary-projections-card',
    require('./components/pages/stocks/SummaryProjectionsCard').default
);

Vue.component(
    'stock-recommendations-card',
    require('./components/pages/stocks/RecommendationsCard').default
);

Vue.component(
    'profile-nav',
    require('./components/pages/profile/Nav').default
);

Vue.component(
    'stock-watchlist-button',
    require('./components/pages/stocks/WatchlistButtonComponent').default
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(ElementUI);

Vue.use(VueEcho, window.Echo);

const app = new Vue({
    el: '#app',
    components: ElementUI
});

