/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import axios from 'axios';
import Vue from 'vue'
import VueRouter from 'vue-router'
import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import Cookie from 'js-cookie'

Vue.use(VueRouter);
Vue.use(VueApollo);

const apolloClient = new ApolloClient({
    headers: {
        'X-XSRF-TOKEN': Cookie.get('XSRF-TOKEN'),
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
    },
    uri: apiUri
});
const apolloProvider = new VueApollo({
    defaultClient: apolloClient,
})

import App from './views/App'
import Passwords from './views/Passwords'
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'dashboard',
            component: Dashboard
        },
        {
            path: '/settings/password',
            name: 'settings.password',
            component: Passwords,
        },
        {
            path: '*',
            component: NotFound,
        }
    ],
});

Vue.component(
    'text-input',
    require('./components/ui/TextInput.vue').default
);
Vue.component(
    'success-message',
    require('./components/ui/Success.vue').default
);


const app = new Vue({
    el: '#app',
    components: { App },
    router,
    apolloProvider,
    render: h => h(App),
});


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

// Vue.component(
//     'passport-clients',
//     require('./components/passport/Clients.vue').default
// );
//
// Vue.component(
//     'passport-authorized-clients',
//     require('./components/passport/AuthorizedClients.vue').default
// );
//
// Vue.component(
//     'passport-personal-access-tokens',
//     require('./components/passport/PersonalAccessTokens.vue').default
// );

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
//
// const app = new Vue({
//     el: '#app',
// });
