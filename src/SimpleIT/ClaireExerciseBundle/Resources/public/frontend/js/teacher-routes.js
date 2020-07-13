mainApp.config(
    ['$routeProvider', '$locationProvider', '$stateProvider',
        function ($routeProvider, $locationProvider, $stateProvider) {

            $stateProvider.state('model', {
                url: '/teacher/model',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-model-list.html?v=465'
            });

            $stateProvider.state('modelEdit', {
                url: '/teacher/model/:modelid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-model-edit.html?v=465'
            });

            $stateProvider.state('modelEdit.resource', {
                url: '/resource',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-list.html?v=465'
            });

            $stateProvider.state('modelEdit.resourceEdit', {
                url: '/resource/:resourceid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-edit.html?v=465'
            });

            $stateProvider.state('resource', {
                url: '/teacher/resource',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-list.html?v=465'
            });

            $stateProvider.state('resourceEdit', {
                url: '/teacher/resource/:resourceid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-edit.html?v=465'
            });
            $stateProvider.state('directories', {
                url: '/teacher/directories',
                templateUrl: `${BASE_CONFIG.urls.partials.teacher}/partial-directory-list.html?v=${buildVersion}`
            });
            $stateProvider.state('directoryEdit', {
                url: '/teacher/directory/:directoryid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-directory-edit.html?v=465'
            });
        }
    ]
);
