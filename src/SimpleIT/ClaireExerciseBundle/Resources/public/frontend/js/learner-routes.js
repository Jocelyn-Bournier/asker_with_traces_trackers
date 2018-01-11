mainApp.config(
    ['$routeProvider', '$locationProvider', '$stateProvider', '$urlRouterProvider', '$resourceProvider',
        function ($routeProvider, $locationProvider, $stateProvider, $urlRouterProvider, $resourceProvider) {

            $resourceProvider.defaults.stripTrailingSlashes = false;

            //$urlRouterProvider.otherwise('/learner/directories/news/');
            $urlRouterProvider.otherwise('/learner/');


            $stateProvider.state('all-attempt-list', {
                url: '/learner/model/:modelId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-attempt-list.html'
            });
            $stateProvider.state('dir-model-list', {
                url: '/learner/directory/models/:dirId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-directory-models-list.html'
            });
            $stateProvider.state('all-new-list', {
                url: '/learner/directories/news/',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-new-list.html'
            });

            $stateProvider.state('attempt', {
                url: '/learner/attempt/:attemptId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-attempt.html'
            });

            $stateProvider.state('attempt.pair-items', {
                url: '/pair-items/:itemId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-pair-items.html'
            });

            $stateProvider.state('attempt.order-items', {
                url: '/order-items/:itemId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-order-items.html'
            });

            $stateProvider.state('attempt.group-items', {
                url: '/group-items/:itemId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-group-items.html'
            });

            $stateProvider.state('attempt.multiple-choice', {
                url: '/multiple-choice/:itemId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-multiple-choice.html'
            });

            $stateProvider.state('attempt.open-ended-question', {
                url: '/open-ended-question/:itemId',
                templateUrl: BASE_CONFIG.urls.partials.learner + '/partial-open-ended-question.html'
            });
        }
    ]
);
