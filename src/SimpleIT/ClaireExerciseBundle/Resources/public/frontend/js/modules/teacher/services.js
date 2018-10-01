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

