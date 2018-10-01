mainApp.config(
    ['$routeProvider', '$locationProvider', '$stateProvider',
        function ($routeProvider, $locationProvider, $stateProvider) {

            $stateProvider.state('model', {
                url: '/teacher/model',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-model-list.html'
            });

            $stateProvider.state('modelEdit', {
                url: '/teacher/model/:modelid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-model-edit.html'
            });

            $stateProvider.state('modelEdit.resource', {
                url: '/resource',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-list.html'
            });

            $stateProvider.state('modelEdit.resourceEdit', {
                url: '/resource/:resourceid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-edit.html'
            });

            $stateProvider.state('resource', {
                url: '/teacher/resource',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-list.html'
            });

            $stateProvider.state('resourceEdit', {
                url: '/teacher/resource/:resourceid',
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-edit.html'
            });
        }
    ]
);
