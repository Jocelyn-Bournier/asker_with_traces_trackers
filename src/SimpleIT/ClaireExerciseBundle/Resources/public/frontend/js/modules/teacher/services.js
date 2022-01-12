var resourceServices = angular.module('resourceServices', ['ngResource']);

resourceServices.factory('Resource', ['$resource',
    function ($resource) {

        return $resource(

            BASE_CONFIG.urls.api.resources + ':id',
            { 'id': '@id'},
            {
                update: {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                duplicate: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.resources + ':id/duplicate'
                },
                import: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.resources + ':id/import'
                },
                subscribe: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.resources + ':id/subscribe'
                }
            }
        );

    }]);

var modelServices = angular.module('modelServices', ['ngResource']);

modelServices.factory('Model', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.models + ':id',
            { 'id': '@id' },
            {
                'update': {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                duplicate: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/duplicate'
                },
                import: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/import'
                },
                subscribe: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/subscribe'
                }
            }
        );

    }]);

var directoryServices = angular.module('directoryServices', ['ngResource']);

directoryServices.factory('Directory', ['$resource',
    function ($resource) {

        return $resource(
            BASE_CONFIG.urls.api.directories + ':id',
            { 'id': '@id' },
            {
                'update': {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                save: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
                },
                duplicate: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/duplicate'
                },
                import: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/import'
                },
                subscribe: {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    url: BASE_CONFIG.urls.api.models + ':id/subscribe'
                }
            }
        );

    }]);
var myDirectoryServices = angular.module('myDirectoryServices', ['ngResource']);
myDirectoryServices.factory('MyDirectory', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.mydirs+":id",
            { 'id': '@id' },
            {
                'delete': {
                    method:'DELETE',
                    headers:{
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    url: BASE_CONFIG.urls.api.directories +':id'
                },
                save: {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    url: BASE_CONFIG.urls.api.directories+"0"
                },
                'update': {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    url: BASE_CONFIG.urls.api.directories +':id'
                },
                visible: {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    url: BASE_CONFIG.urls.api.directories +'visible/:id'
                },
                savechild: {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    url: BASE_CONFIG.urls.api.directories+':id'
                }
            }
        );

    }]);
var availableManagersServices = angular.module('availableManagersServices', ['ngResource']);
availableManagersServices.factory('AvailableManagers', ['$resource',
    function ($resource) {
        return $resource(
            BASE_CONFIG.urls.api.users+"available/managers"
        );

    }]);
