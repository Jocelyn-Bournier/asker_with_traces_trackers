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
var newListServices = angular.module('newListServices', ['ngResource']);

newListServices.factory('NewList', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.news + "new/"  +BASE_CONFIG.currentUserId
        );

    }]);
var modelDirectoryServices = angular.module('modelDirectoryServices', ['ngResource']);

modelDirectoryServices.factory('ModelDirectory', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.news + "model/:modelId" ,{modelId:"@modelId"}
        );

    }]);
var directoryListServices = angular.module('directoryListServices', ['ngResource']);
directoryListServices.factory('DirectoryList', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.news
        );

    }]);
var directorySelectServices = angular.module('directorySelectServices', ['ngResource']);
directorySelectServices.factory('DirectorySelect', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.news + "model/:modelId/:directoryId",
            {modelId:"@modelId", directoryId:"@directoryId"}
        );

    }]);
//var directoryServices = angular.module('directorySelectServices', ['ngResource']);
directorySelectServices.factory('DirectorySelect', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.news + "model/:modelId/:directoryId",
            {modelId:"@modelId", directoryId:"@directoryId"}
        );

    }]);
directorySelectServices.factory('DirectoryModelList', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.directories+":id",
            { 'id': '@id' }//,
            //{
            //    'delete': {
            //        method:'DELETE',
            //        headers:{
            //            'Content-Type': 'application/json',
            //            'Accept': 'application/json'
            //        },
            //        url: BASE_CONFIG.urls.api.directories +':id'
            //    },
            //    save: {
            //        method: 'POST',
            //        headers: {
            //            'Content-Type': 'application/json',
            //            'Accept': 'application/json'
            //        },
            //        url: BASE_CONFIG.urls.api.directories+"0"
            //    },
            //    'update': {
            //        method: 'PUT',
            //        headers: {
            //            'Content-Type': 'application/json',
            //            'Accept': 'application/json'
            //        },
            //        url: BASE_CONFIG.urls.api.directories +':id'
            //    },
            //    savechild: {
            //        method: 'POST',
            //        headers: {
            //            'Content-Type': 'application/json',
            //            'Accept': 'application/json'
            //        },
            //        url: BASE_CONFIG.urls.api.directories+':id'
            //    }
            //}
        );

    }]);
