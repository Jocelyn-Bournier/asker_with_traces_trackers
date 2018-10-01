var itemServices = angular.module('itemServices', ['ngResource']);

itemServices.factory('Item', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.attempts + ':attemptId/items/:itemId',
            {'attemptId': '@attemptId', 'itemId': '@itemId'}
        );

    }]);

var exerciseServices = angular.module('exerciseServices', ['ngResource']);

exerciseServices.factory('Exercise', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.exercises + ':exerciseId',
            {'exerciseId': '@exerciseId'}
        );

    }]);

var answerServices = angular.module('answerServices', ['ngResource']);

answerServices.factory('Answer', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.attempts + ':attemptId/items/:itemId/answers/',
            {'attemptId': '@attemptId', 'itemId': '@itemId'},
            {
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                }
            }
        );

    }]);

var attemptServices = angular.module('attemptServices', ['ngResource']);

attemptServices.factory('Attempt', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.attempts + ':attemptId',
            {'attemptId': '@attemptId'}
        );

    }]);


var exerciseByModelServices = angular.module('exerciseByModelServices', ['ngResource']);

exerciseByModelServices.factory('ExerciseByModel', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.models + ':modelId/exercises/',
            {'modelId': '@modelId'},
            {
                try: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                }
            }
        );

    }]);

var attemptByExerciseServices = angular.module('attemptByExerciseServices', ['ngResource']);

attemptByExerciseServices.factory('AttemptByExercise', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.exercises + ':exerciseId/attempts/',
            {'exerciseId': '@exerciseId'},
            {
                create: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                }
            }
        );

    }]);

var attemptListServices = angular.module('attemptListServices', ['ngResource']);

attemptListServices.factory('AttemptList', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.attemptedModels + ':modelId'
        );

    }]);
