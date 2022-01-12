var userServices = angular.module('userServices', ['ngResource']);

userServices.factory('User', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.users +':userId',
            { 'userId': '@userId'}
        );

    }]);

var uploadServices = angular.module('uploadServices', ['ngResource']);

userServices.factory('Upload', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.uploads,
            {},
            {
                upload: {method: 'POST', headers: {enctype:'multipart/form-data'}}
            }
        );

    }]);
