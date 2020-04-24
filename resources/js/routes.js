import Passwords from './views/Passwords'
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'
import Roles from './views/Roles'
import Role from "./views/Role";
import Users from "./views/Users";
import User from "./views/User";

export default [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
    },

    /*
        Self-management Routes
     */

    {
        path: '/settings/password',
        name: 'settings.password',
        component: Passwords,
    },


    /*
        Admin Routes
     */

    // Roles
    {
        path: '/admin/roles',
        name: 'admin.roles',
        component: Roles,
        meta: {
            permission: 'auth.roles'
        }
    },
    {
        path: '/admin/role/new',
        name: 'admin.role.create',
        component: Role,
        meta: {
            permission: 'auth.roles.create'
        }
    },
    {
        path: '/admin/role/:id',
        name: 'admin.role.update',
        component: Role,
        meta: {
            permission: 'auth.roles.update'
        }
    },

    // User Management
    {
        path: '/admin/users',
        name: 'admin.users',
        component: Users,
        meta: {
            permission: 'auth.users'
        }
    },

    {
        path: '/admin/users/:id',
        name: 'admin.users.view',
        component: User,
        meta: {
            permission: 'auth.users'
        }
    },


    // Catch-all 404
    {
        path: '*',
        component: NotFound,
    }
];

// Don't want the globally applied loading spinner on a route?
// add in the following meta field:

// meta: {
//     globalLoadState: false
// }
