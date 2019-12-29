/*
  Import Required Classes
*/
import Vue from 'vue'
import VueRouter from 'vue-router'
import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import Cookie from 'js-cookie'
import App from './views/App'

import Routes from './routes'

require('./bootstrap');

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

const router = new VueRouter({
    mode: 'history',
    routes: Routes
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

/*
   Create App
 */

const app = new Vue({
    el: '#app',
    components: {App},
    router,
    apolloProvider,
    render: h => h(App),
});
