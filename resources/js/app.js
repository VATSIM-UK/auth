import {camelCase, upperFirst} from "lodash";
/*
  Import Required Classes
*/
import Vue from 'vue'
import VueRouter from 'vue-router'
import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import Cookie from 'js-cookie'
import App from './views/App'

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

require('./routes');

/*
   Initialise Custom Components
 */

const requireComponent = require.context(
    // The relative path of the components folder
    './components/ui',
    // Whether or not to look in subfolders
    false,
    // The regular expression used to match base component filenames
    /Base[A-Z]\w+\.(vue|js)$/
)

requireComponent.keys().forEach(fileName => {
    // Get component config
    const componentConfig = requireComponent(fileName);

    // Get PascalCase name of component
    const componentName = upperFirst(
        camelCase(
            fileName
                .split('/')
                .pop()
                .replace(/\.\w+$/, '')
        )
    )


    // Register component globally
    Vue.component(
        componentName,
        componentConfig.default || componentConfig
    )
});

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
