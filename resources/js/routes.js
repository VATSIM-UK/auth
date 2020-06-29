
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'

import ProfilePasswords from './views/Profile/Passwords'

import AdminRolesIndex from './views/Admin/Roles/Index'
import AdminRolesShow from "./views/Admin/Roles/Show";
import AdminUsersIndex from "./views/Admin/Users/Index";
import AdminUsersShow from "./views/Admin/Users/Show";

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
        component: ProfilePasswords,
    },


    /*
        Admin Routes
     */

    // Roles
    {
        path: '/admin/roles',
        name: 'admin.roles',
        component: AdminRolesIndex,
        meta: {
            permission: 'auth.roles'
        }
    },
    {
        path: '/admin/role/new',
        name: 'admin.role.create',
        component: AdminRolesShow,
        meta: {
            permission: 'auth.roles.create'
        }
    },
    {
        path: '/admin/role/:id',
        name: 'admin.role.update',
        component: AdminRolesShow,
        meta: {
            permission: 'auth.roles.update'
        }
    },

    // User Management
    {
        path: '/admin/users',
        name: 'admin.users',
        component: AdminUsersIndex,
        meta: {
            permission: 'auth.users'
        }
    },

    {
        path: '/admin/users/:id',
        name: 'admin.users.view',
        component: AdminUsersShow,
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

// Route Permissions
// These can be added by specifying the permission meta field
// meta: {
//     permission: 'auth.users'
// }
