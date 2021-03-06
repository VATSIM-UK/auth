/*
  Import Required Classes
*/
import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import Cookie from 'js-cookie'
import App from './views/App'

import Routes from './routes'

import PermissionMet from '../../vendor/vatsimuk/auth-package/src/Js/permissionValidity'

require('./bootstrap');
require('../../node_modules/nprogress/nprogress');

Vue.use(VueRouter);
Vue.use(VueApollo);
Vue.use(Vuex);

const store = new Vuex.Store({
    state: {
        authUser: {
            name_first: null,
            name_last: null,
            permissions: []
        }
    },
    mutations: {
        initAuthUser(state, authUser) {
            state.authUser = authUser;
        }
    }
});

Vue.mixin({
    methods: {
        hasPermissionTo: function (permission) {
            return PermissionMet(permission, this.$store.state.authUser.permissions);
        }
    }
});

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
    watchLoading(isLoading) {
        this.$root.dataIsLoading = isLoading;
    }
});

/*
   Import and Setup Router
 */

const router = new VueRouter({
    mode: 'history',
    routes: Routes
});

router.beforeResolve((to, from, next) => {
    // If this isn't an initial page load.
    if (to.name) {
        // Start the route progress bar.
        NProgress.start()
    }
    next()
});

router.afterEach((to, from) => {
    // Complete the animation of the route progress bar.
    NProgress.done()
});


/*
   Initialise Custom Components
 */

Vue.component(
    'text-input',
    require('./components/ui/TextInput').default
);
Vue.component(
    'success-message',
    require('./components/ui/SuccessMessage').default
);
Vue.component(
    'editable-text',
    require('./components/ui/EditableText').default
);

/*
   Create App
 */

const app = new Vue({
    el: '#app',
    data: {
        dataIsLoading: false
    },
    components: {App},
    router,
    store,
    apolloProvider,
    render: h => h(App),
});
