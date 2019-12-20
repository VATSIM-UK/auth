/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
 /*
    Import Required Classes
  */
import Vue from 'vue'
import VueRouter from 'vue-router'
import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import Cookie from 'js-cookie'

Vue.use(VueRouter);
Vue.use(VueApollo);

/*
   Setup GraphQL Client with Authentication Headers
 */

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

/*
   Import Router Views
 */

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

/*
   Initialise Custom Components
 */

Vue.component(
    'text-input',
    require('./components/ui/TextInput.vue').default
);
Vue.component(
    'success-message',
    require('./components/ui/Success.vue').default
);

/*
   Create App
 */

const app = new Vue({
    el: '#app',
    components: { App },
    router,
    apolloProvider,
    render: h => h(App),
});
