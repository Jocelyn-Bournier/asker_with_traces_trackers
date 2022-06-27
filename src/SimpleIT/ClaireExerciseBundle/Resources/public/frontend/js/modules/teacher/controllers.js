/**
 *
 * Created by bryan on 25/06/14.
 */
var directoryControllers = angular.module('directoryControllers', ['ui.router']);
directoryControllers.controller('directoryController',  ['$scope', '$modal',
    function ($scope, $modal) {
        $scope.section = 'directory';
        $scope.directories = {};
    }
]);
directoryControllers.controller('directoryListController', ['$scope', 'MyDirectory', '$location',
    function ($scope, MyDirectory, $location) {

        $scope.test = document.location.toString().includes("app_dev");

        // retrieve models
        //MyDirectory.query({id: BASE_CONFIG.currentUserId}, function (data) {
        MyDirectory.query(function (datas) {
            for(var i = 0; i < datas.length; i++){
                $scope.directories[datas[i].id] = datas[i];
            }

        });
        $scope.setVisible = function(directory){
            MyDirectory.visible({id:directory.id},function (data){
                directory.is_visible = data.is_visible;
                $location.path('/teacher/directory/visible/' + data.id)
            });
        }
        $scope.deleteDirectory = function (directory) {
            directory.$delete({id: directory.id}, function () {
                delete $scope.directories[directory.id];
            });
        };
        $scope.createDirectory = function(parentid){
            if (parentid === undefined){
                MyDirectory.save( function (data) {
                    $location.path('/teacher/directory/' + data.id)
                });
            }else{
                MyDirectory.savechild({id:parentid}, function (data) {
                    $location.path('/teacher/directory/' + data.id)
                });
            }
        }
        $scope.editDirectory = function(directory){
            $location.path('/teacher/directory/' + directory.id)
        }
        $scope.getComperJWT = function (frameworkId, groupId = null) {

            if (groupId != null) {

                $.ajax({
                    //url:         "app_dev.php/api/directories/jwt/"+frameworkId+'/teacher',
                    url: BASE_CONFIG.urls.api.directories + "jwt/" + frameworkId + '/' + groupId + '/teacher',
                    type: "GET",
                    crossDomain: true,
                    async: false,
                    success: function (data, textStatus) {
                        let token = data['token'];
                        document.getElementById('comper-jwt-input').value = token;
                    }
                });
                return true;
            }
            else {
                $.ajax({
                    //url:         "app_dev.php/api/directories/jwt/"+frameworkId+'/teacher',
                    url: BASE_CONFIG.urls.api.directories + "jwt/" + frameworkId + '/teacher',
                    type: "GET",
                    crossDomain: true,
                    async: false,
                    success: function (data, textStatus) {
                        let token = data['token'];
                        document.getElementById('comper-jwt-input').value = token;
                    }
                });
                return true;
            }
        }
    }
]);
directoryControllers.controller('directoryEditController', ['$scope','$stateParams', 'MyDirectory', 'AvailableManagers',
    function ($scope, $stateParams, MyDirectory, AvailableManagers) {

        $scope.directory = MyDirectory.get({id: $stateParams.directoryid}, function () {
        });
        $scope.availableManagers = AvailableManagers.query({}, function(){
        });
        $scope.updateDirectory = function (directory) {
            directory.$update({id: directory.id}, function (dir) {
                $scope.directory = dir;
                if (directory.framework_id != null){
                    let directoryId = directory.id;
                    $.ajax({
                        url:         `${BASE_CONFIG.urls.api.directories}comper/${directoryId}/createGroup`,
                        type:        "PUT",
                        crossDomain: true,
                        async:       false,
                        success: function(data, textStatus) {
                            $scope.activateComper(directory);
                        }});
                }
            });
        };
        $scope.activateComper = function (directory) {
            document.getElementById("progress-bar-comper-creation").style.width= '100%';
            document.getElementById("progress-bar-comper-creation").classList.add("progress-bar-striped");
            document.getElementById("progress-bar-comper-creation").classList.add("active");
            document.getElementById("progress-bar-comper-creation").style.minWidth = "3em;";
            document.getElementById("action-creation").innerHTML = "Création du groupe";
            let directoryId = directory.id;
            $.ajax({
                url:         `${BASE_CONFIG.urls.api.directories}comper/${directoryId}/createGroup`,
                type:        "PUT",
                crossDomain: true,
                async:       false,
                success: function(data, textStatus) {}});
            let cpt = 1;
            let nbUsers;
            let users;

            document.getElementById("action-creation").innerHTML = "Ajout des enseignants";
            $.ajax({
                url: `${BASE_CONFIG.urls.api.directories}comper/${directoryId}/managers/addManagers`,
                type: "GET",
                crossDomain: true,
                async: false,
                success: function (data, textStatus) {
                }
            });

            document.getElementById("action-creation").innerHTML = "Récupération de la liste des étudiants";
            $.ajax({
                url: `${BASE_CONFIG.urls.api.directories}comper/${directoryId}/users`,
                type: "GET",
                crossDomain: true,
                async: false,
                success: function (data, textStatus) {

                    users = data["users"];
                    nbUsers = users.length;
                }});
            document.getElementById("action-creation").innerHTML = "Ajout des étudiants";
            for (let userId in users) {
                $.ajax({
                                    url: `${BASE_CONFIG.urls.api.directories}comper/${directoryId}/${users[userId]}`,
                                    type: "GET",
                                    crossDomain: true,
                                    async: true,
                                    success: function (data, textStatus) {
                                        document.getElementById("progress-bar-comper-creation").style.width = parseInt((cpt / nbUsers) * 100) + "%";
                                        document.getElementById("progress-bar-comper-creation").innerHTML = parseInt((cpt / nbUsers) * 100) + "%";
                                        cpt++;
                                        if (cpt == nbUsers){
                                            document.getElementById("action-creation").innerHTML = "Création des profils terminée";
                                        }
                                    }
                });

                            }
            document.getElementById("progress-bar-comper-creation").classList.remove('progress-bar-striped');
            document.getElementById("progress-bar-comper-creation").classList.remove('active');
            return true;
        }
        $scope.filterAlreadyAdded = function(item) {
            if ($scope.users != null ) {
                if (item.username !== $scope.users[$scope.directory.owner].user_name) {
                    if ($scope.directory.managers.map(
                        function (manager) {
                            return manager.username
                        }).indexOf(item.username) == -1) {
                        return item;
                    }
                }
            }
        };
        $scope.directoryAddModel = function (collection, id) {
            var isAlreadyAdded = false;
            angular.forEach(collection, function (res) {
                if (res.id == id) {
                    isAlreadyAdded = true;
                }
            });
            if (!isAlreadyAdded) {
                collection.splice(collection.length, 0, {"id": id});
            }
        };
        $scope.directoryAddManager = function(newManager, managers){
            managers.push(newManager);
        }
        $scope.deleteDirectory = function (directory) {
            directory.$delete({id: directory.id})
        };
        $scope.onDropModel = function(event, model, collection){
            if ($scope.directory.models === undefined){
                $scope.directory.models = [];
            }
            $scope.directory.models.push(model);
        };
        $scope.directoryRemoveField = function (collection, index) {
            collection.splice(index, 1);
        };
        //$scope.directoryRemoveModel = function (collection, index) {
        //    collection.splice(index, 1);
        //};
    }
]);

var resourceControllers = angular.module('resourceControllers', ['ui.router']);

resourceControllers.controller('resourceController', ['$scope', '$modal',
    function ($scope, $modal) {

        $scope.section = 'resource';

        /*
         * Here is a contextual client-side object used to specify user's filters information.
         * These values are bi-directionally data-bound to filters section fields in list views.
         */
        $scope.filters = {
            search: '', // search field
            archived: false, // select archived resources or not (boolean)
            public: false, // select public resources or not (boolean)
            type: { // resources types to be selected
                multiple_choice_question: 'multiple-choice-question', text_with_holes: 'text-with-holes', text: 'text', picture: 'picture', open_ended_question: 'open-ended-question', sequence: ''
            },
            keywords: [], // list of keywords that a resource must have to be selected
            metadata: [] // list of metadata objects that a resource must have to be selected
        };

        $scope.$parent.$watch("subSection", function (newValue) {
            if (newValue == 'pair-items') {
                $scope.filters.type.multiple_choice_question = '';
                $scope.filters.type.text = 'text';
                $scope.filters.type.picture = 'picture';
                $scope.filters.type.open_ended_question = '';
                $scope.filters.type.sequence = '';
                $scope.filters.type.text_with_holes = '';
            } else if (newValue == 'multiple-choice') {
                $scope.filters.type.multiple_choice_question = 'multiple-choice-question';
                $scope.filters.type.text = '';
                $scope.filters.type.picture = '';
                $scope.filters.type.open_ended_question = '';
                $scope.filters.type.sequence = '';
                $scope.filters.type.text_with_holes = '';
            } else if (newValue == 'group-items') {
                $scope.filters.type.multiple_choice_question = '';
                $scope.filters.type.text = 'text';
                $scope.filters.type.picture = 'picture';
                $scope.filters.type.open_ended_question = '';
                $scope.filters.type.sequence = '';
            } else if (newValue == 'sequence') {
                $scope.filters.type.multiple_choice_question = '';
                $scope.filters.type.text = '';
                $scope.filters.type.picture = '';
                $scope.filters.type.open_ended_question = '';
                $scope.filters.type.sequence = '';
                $scope.filters.type.text_with_holes = '';
            } else if (newValue == 'open-ended-question') {
                $scope.filters.type.multiple_choice_question = '';
                $scope.filters.type.text = '';
                $scope.filters.type.picture = '';
                $scope.filters.type.open_ended_question = 'open-ended-question';
                $scope.filters.type.sequence = '';
                $scope.filters.type.text_with_holes = '';
            } else if (newValue == 'text-with-holes') {
                $scope.filters.type.multiple_choice_question = '';
                $scope.filters.type.text = '';
                $scope.filters.type.picture = '';
                $scope.filters.type.open_ended_question = '';
                $scope.filters.type.sequence = '';
                $scope.filters.type.text_with_holes = 'text-with-holes';
            }
        });

        if (typeof $scope.$parent.section === 'undefined') {
            $scope.parentSection = '';
        } else {
            $scope.parentSection = $scope.$parent.section;
        }

        $scope.resourceContext = {
            "formula": {
                "newVariable": {
                    "type": "integer",
                    "value_type": "values",
                    "values": []
                },
                "newFormula": {
                    "variables": [
                        {
                            "type": "integer",
                            "value_type": "values",
                            "values": []
                        }
                    ]
                }
            },
            "newResources": {
                "text": {
                    "type": "text",
                    "title": "Nouveau texte",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "text": null,
                        "object_type": "text"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "text_with_holes": {
                    "type": "text-with-holes",
                    "title": "Nouveau texte annoté",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "answerModality": 'holes',
                        "annotationsList": [],
                        "errorsList": [],
                        "bold": [],
                        "italize": [],
                        "underline": [],
                        "annotations": [],
                        "text": "Nouveau texte.",
                        "object_type": "text_with_holes"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "picture": {
                    "type": "picture",
                    "title": "Nouvelle image",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "source": null,
                        "object_type": "picture"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "multiple_choice_question": {
                    "type": "multiple-choice-question",
                    "title": "Nouvelle question",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "formulas": [],
                        "do_not_shuffle": true,
                        "question": null,
                        "propositions": [
                            {
                                "text": null,
                                "right": true
                            }
                        ],
                        "comment": null,
                        "max_number_of_propositions": 0,
                        "max_number_of_right_propositions": 0,
                        "object_type": "multiple_choice_question"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "open_ended_question": {
                    "type": "open-ended-question",
                    "title": "Nouvelle question",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "formulas": [],
                        "question": null,
                        "solutions": [],
                        "comment": null,
                        "object_type": "open_ended_question"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                }
            }
        };

        $scope.viewResource = function (resource) {
            $modal.open({
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-preview.html',
                controller: 'resourceViewController',
                size: 'lg',
                resolve: {
                    resource: function () {
                        return resource;
                    },
                    users: function () {
                        return $scope.users;
                    }
                }
            });
        };
    }]);

resourceControllers.controller('resourceViewController', ['$scope', 'BASE_CONFIG', '$modalInstance', 'resource', 'users',
    function ($scope, BASE_CONFIG, $modalInstance, resource, users) {
        $scope.resourcePanelContext = "resourceEdit";

        $scope.BASE_CONFIG = BASE_CONFIG;
        $scope.resource = resource;
        $scope.users = users;
    }]);


resourceControllers.controller('resourceListController', ['$scope', '$state', 'Resource',
    function ($scope, $state, Resource) {
        $scope.resourcePanelContext = "list";

        // delete resource method
        $scope.deleteResource = function (resource) {
            resource.$delete({id: resource.id}, function () {
                delete $scope.resources[resource.id];
            });
        };

        $scope.importResource = function (resource) {
            Resource.import({id: resource.id}, function () {
                $scope.loadResourcesAndUsers();
            });
        };

        $scope.subscribeResource = function (resource) {
            Resource.subscribe({id: resource.id}, function (data) {
                $scope.resources[data.id] = data;
            });
        };

        $scope.archiveResource = function (resource) {
            console.log('archiving...');
            var archived = new Resource;
            archived.archived = true;
            archived.metadata = resource.metadata;
            archived.$update({id: resource.id}, function () {
                resource.archived = true;
            });
        };

        $scope.restoreResource = function (resource) {
            console.log('restoring...');
            var archived = new Resource;
            archived.archived = false;
            archived.metadata = resource.metadata
            archived.$update({id: resource.id}, function () {
                resource.metadata = archived.metadata;
                resource.archived = false;
            });
        };

        $scope.duplicateResource = function (resource) {
            Resource.duplicate({id: resource.id}, function (data) {
                $scope.resources[data.id] = data;
                if ($scope.parentSection === 'model') {
                    $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                } else {
                    $state.go('resourceEdit', {resourceid: data.id});
                }
            });
        };

        $scope.resourceAddKeywordsField = function (collection) {
            var keyword = $('#resourceAddKeyword');
            collection.push(keyword[0].value);
            keyword[0].value = '';
        };

        $scope.resourceAddMetadataField = function (collection) {
            var key = $("#resourceAddMetadataKey"), val = $("#resourceAddMetadataValue");
            var newElement = {key: key[0].value, value: val[0].value};
            collection.splice(collection.length, 0, newElement);
            key[0].value = '';
            val[0].value = '';
        };

        $scope.resourceRemoveField = function (collection, index) {
            collection.splice(index, 1);
        };

        // create resource method
        $scope.createResource = function (type) {
            if (type == 'text') {
                Resource.save($scope.resourceContext.newResources.text, function (data) {
                    $scope.resources[data.id] = data;
                    if ($scope.parentSection === 'model') {
                        $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                    } else {
                        $state.go('resourceEdit', {resourceid: data.id});
                    }
                });
            }
            else if (type == 'text-with-holes') {
                Resource.save($scope.resourceContext.newResources.text_with_holes, function (data) {
                    $scope.resources[data.id] = data;
                    if ($scope.parentSection === 'model') {
                        $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                    } else {
                        $state.go('resourceEdit', {resourceid: data.id});
                    }
                });
            }  else if (type == 'picture') {
                Resource.save($scope.resourceContext.newResources.picture, function (data) {
                    $scope.resources[data.id] = data;
                    if ($scope.parentSection === 'model') {
                        $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                    } else {
                        $state.go('resourceEdit', {resourceid: data.id});
                    }
                });
            } else if (type == 'multiple-choice-question') {
                Resource.save($scope.resourceContext.newResources.multiple_choice_question, function (data) {
                    $scope.resources[data.id] = data;
                    if ($scope.parentSection === 'model') {
                        $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                    } else {
                        $state.go('resourceEdit', {resourceid: data.id});
                    }
                });
            } else if (type == 'open-ended-question') {
                Resource.save($scope.resourceContext.newResources.open_ended_question, function (data) {
                    $scope.resources[data.id] = data;
                    if ($scope.parentSection === 'model') {
                        $state.go('modelEdit.resourceEdit', {resourceid: data.id});
                    } else {
                        $state.go('resourceEdit', {resourceid: data.id});
                    }
                });
            }
        };
    }]);

/*
 Filters, what for? Filters are used here to select specific resources in a collection of resources retrieved from the backend.
 Parameters :
 - String : specify here angular filter's name.
 - Callback function : here is defined an anonymous function for filtering process.

 Filtering process function :
 Parameters :
 - Javascript Object : here is a collection of resources pre-filtered on keyword field value (cf. Partial-{model, resource}-list.html).
 - Javascript Object : this object is initialized in {model, resource}Controller and contains specific attributs
 such as archived, public or a collection of types that a user wants to see.

 */
resourceControllers.filter('myFilters', function () {
    return function (collection, filters) {
        var items = []; // collection to be returned
        var ids = []; // temporary IDs collection to maintain atomic items in collection to be returned
        angular.forEach(collection, function (value) { // iterate on each item in given collection
            angular.forEach(filters.type, function (type) { // iterate on each filter type specified by user to keep match users parameters.
                // remove archived items by default
                if (value.type == type && ((filters.archived && value.archived) || !value.archived)) {
                    // remove public items by default. Allow to display private user's items or public only items.
                    if ((filters.public && value.owner != BASE_CONFIG.currentUserId) || (!filters.public && value.owner == BASE_CONFIG.currentUserId)) {
                        // Check if user specified keywords filter
                        if (filters.keywords.length) {
                            // Check if the current item as at least one keyword
                            if (value.keywords.length) {
                                angular.forEach(filters.keywords, function (keyword) {
                                    if ($.inArray(keyword, value.keywords) != -1) {
                                        // So check if user specify at least one metadata filter
                                        if (filters.metadata.length) {
                                            // Check if the current item as at least one metadata
                                            if (value.metadata.length) {
                                                angular.forEach(filters.metadata, function (meta1) {
                                                    angular.forEach(value.metadata, function (meta2) {
                                                        // Metadata keys and values matchs
                                                        if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value.toLowerCase() == meta2.value.toLowerCase()) {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                        // Metadata keys matchs
                                                        if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value == '') {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                        // Metadata values matchs
                                                        if (meta1.value.toLowerCase() == meta2.value.toLowerCase() && meta1.key == '') {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                    });
                                                });
                                            }
                                        } else {
                                            if ($.inArray(value.id, ids) == -1) {
                                                items.push(value);
                                                ids.push(value.id)
                                            }
                                        }
                                    }
                                });
                            }
                        } else {
                            // User did not specify keyword filter
                            // So check if user specify at least one metadata filter
                            if (filters.metadata.length) {
                                // Check if the current item as at least one metadata
                                if (value.metadata.length) {
                                    angular.forEach(filters.metadata, function (meta1) {
                                        angular.forEach(value.metadata, function (meta2) {
                                            // Metadata keys and values matchs
                                            if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value.toLowerCase() == meta2.value.toLowerCase()) {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                            // Metadata keys matchs
                                            if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value == '') {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                            // Metadata values matchs
                                            if (meta1.value.toLowerCase() == meta2.value.toLowerCase() && meta1.key == '') {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                        });
                                    });
                                }
                            } else {
                                // User did not specity metadata filter
                                if ($.inArray(value.id, ids) == -1) {
                                    items.push(value);
                                    ids.push(value.id)
                                }
                            }
                        }
                    }
                }
            });
        });
        return items; // returns filtered collection
    };
});

resourceControllers.controller('resourceEditController', ['$scope', '$modal', 'Resource', 'Upload', '$location', '$stateParams', 'User', '$upload',
    function ($scope, $modal, Resource, Upload, $location, $stateParams, User, $upload) {
        $scope.resourcePanelContext = "resourceEdit";

        // retrieve resource
        if (typeof $scope.resources === "undefined") {
            $scope.editedResource = Resource.get({id: $stateParams.resourceid});
        } else {
            $scope.editedResource = jQuery.extend(true, {}, $scope.resources[$stateParams.resourceid]);
        }

        // resource for md link
        $scope.resource = null;
        $scope.resourceAddMD = {key: '', value: ''};


        $scope.errorsName = "";
        $scope.selectedErrors = {
            "name": "",
            "nbErrors": 1,
            "errors": [],
            "constraintName": ""
        };
        $scope.errorLists = [];
        $scope.nbErrors = [];
        $scope.states = ["",""];

        $scope._handleDown = function (event){
            $scope.states[0] = $scope.states[1];
            $scope.states[1] = event.key;

            if ($scope.states[0] == "Control" && $scope.states[1] == "x"){
                event.preventDefault();
                alert("Il est impossible d'utiliser la commande Ctrl + X sur cette page");
            }
            if ($scope.states[0] == "Control" && $scope.states[1] == "z"){
                event.preventDefault();
                alert("Il est impossible d'utiliser la commande Ctrl + Z sur cette page");
            }
            if (event.key == "Backspace"){
                $scope._updateIndices(event, "backspace");
            }
        }

        $scope._updateIndices = function (event, action){
            let positionStart = event.target.selectionStart;
            let positionStop = event.target.selectionEnd;
            let diff = positionStop - positionStart;
            let addDiffValue, addValue;

            if(action == "paste"){
                texte = event.clipboardData.getData("text");
                let tailleTexte = texte.length;
                addDiffValue = tailleTexte - diff;
                addValue = tailleTexte;
            } else if (action == "key"){
                addDiffValue = 1 - diff;
                addValue = 1;
            } else if (action == "backspace"){
                addDiffValue = -diff;
                addValue = -1;
            }

            for(indice of $scope.editedResource.content.bold){
                if (positionStart < indice[0]){
                    if (positionStart != positionStop){
                        indice[0] = indice[0] + addDiffValue;
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[0] += addValue;
                        indice[1] += addValue;
                    }
                } else if (positionStart >= indice[0] && positionStart <= indice[1]) {
                    if (positionStart != positionStop){
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[1] += addValue;
                    }
                }
            }
            for(indice of $scope.editedResource.content.italize){
                if (positionStart < indice[0]){
                    if (positionStart != positionStop){
                        indice[0] = indice[0] + addDiffValue;
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[0] += addValue;
                        indice[1] += addValue;
                    }
                } else if (positionStart >= indice[0] && positionStart <= indice[1]) {
                    if (positionStart != positionStop){
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[1] += addValue;
                    }
                }
            }
            for(indice of $scope.editedResource.content.underline){
                if (positionStart < indice[0]){
                    if (positionStart != positionStop){
                        indice[0] = indice[0] + addDiffValue;
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[0] += addValue;
                        indice[1] += addValue;
                    }
                } else if (positionStart >= indice[0] && positionStart <= indice[1]) {
                    if (positionStart != positionStop){
                        indice[1] = indice[1] + addDiffValue;
                    } else {
                        indice[1] += addValue;
                    }
                }
            }
            for(tag of $scope.editedResource.content.annotations){
                if (positionStart < tag["indiceDebut"]){
                    if (positionStart != positionStop){
                        tag["indiceDebut"] = tag["indiceDebut"] + addDiffValue;
                        tag["indiceFin"] = tag["indiceFin"] + addDiffValue;
                    } else {
                        tag["indiceDebut"] += addValue;
                        tag["indiceFin"] += addValue;
                    }
                } else if (positionStart >= tag["indiceDebut"] && positionStart <= tag["indiceFin"]+1) {
                    if (positionStart != positionStop){
                        tag["indiceFin"] = tag["indiceFin"] + addDiffValue;
                    } else {
                        tag["indiceFin"] += addValue;
                    }
                }
            }
            for(errors of $scope.editedResource.content.errors_list) {
                for (err of errors.errors) {
                    if (positionStart < err["indiceDebut"]) {
                        if (positionStart != positionStop) {
                            err["indiceDebut"] = err["indiceDebut"] + addDiffValue;
                            err["indiceFin"] = err["indiceFin"] + addDiffValue;
                        } else {
                            err["indiceDebut"] += addValue;
                            err["indiceFin"] += addValue;
                        }
                    } else if (positionStart >= err["indiceDebut"] && positionStart <= err["indiceFin"] + 1) {
                        if (positionStart != positionStop) {
                            err["indiceFin"] = err["indiceFin"] + addDiffValue;
                        } else {
                            err["indiceFin"] += addValue;
                        }
                    }
                }
            }
        }

        $scope._updateErrorList = function () {
            let found = false;
            $scope.selectedErrors = {
                "name": "",
                "nbErrors": 1,
                "errors": [],
                "constraintName": ""
            };
            for(eList of $scope.editedResource.content.errors_list){
                if (eList.name == $scope.selectedErrors.name){
                    found = true;
                }
            }
            if(!found) {
                $scope.editedResource.content.errors_list.push($scope.selectedErrors);
            }
        }

        $scope._updateErrorsInput = function () {
            //$scope.selectedErrors.nbErrors = document.getElementById("input-nb-error").value;
            for (error of $scope.selectedErrors.errors){
                console.log(error);
                error.errors.length = document.getElementById("input-nb-error").value;
            }
        }

        $scope._selectErrorList = function (errorName) {
            for(errorList of $scope.editedResource.content.errors_list){
                if (errorList.name == errorName){
                    console.log($scope.editedResource.content.errors_list);
                    $scope.selectedErrors = errorList;
                }
            }
        }

        $scope._changeError = function (erreur, index) {
            console.log($scope.selectedErrors);
            for(err of $scope.selectedErrors.errors){
                if(err.indiceDebut == erreur.indiceDebut && err.indiceFin == erreur.indiceFin){
                    err.errors[index] = document.getElementById(`error-${erreur.indiceDebut}-${erreur.indiceFin}-${index}`).value;
                    return;
                }
            }
            console.log($scope.selectedErrors);
        }


        $scope._selectErrors = function (errorName, apply=false) {
            $scope.errorsName = document.getElementById('select-constraint-list').value;
            let constraintList = $scope.editedResource.content.annotations_list;
            let annotations = $scope.editedResource.content.annotations;
            for(constraint of constraintList) {
                if (constraint.name == $scope.errorsName) {
                    $scope.selectedErrors.errors = filterByConstraint(annotations, constraint.constraint);
                    $scope.selectedErrors.errors = filterByUniqueIndices($scope.selectedErrors.errors);
                    for (err of $scope.selectedErrors.errors){
                        err.errors = Array();
                        err.errors.length= document.getElementById('input-nb-error').value;
                    }
                    if(apply){$scope.$apply();}
                    return;
                }
            }
        }

        function filterByUniqueIndices(errors){
            newErrors = [];
            for(err of errors){
                if(newErrors.length == 0){
                    newErrors.push({"indiceDebut": err.indiceDebut, "indiceFin" : err.indiceFin});
                } else {
                    let finded = false
                    for (newErr of newErrors){
                        if(newErr.indiceFin == err.indiceFin && newErr.indiceDebut == err.indiceDebut){
                            finded = true;
                            break;
                        }
                    }
                    if (!finded){
                        newErrors.push({"indiceDebut": err.indiceDebut, "indiceFin" : err.indiceFin});
                    }
                }
            }
            return newErrors;
        }

        function filterByConstraint(annotations, constraint){
            if (constraint.cle != null){
                if(constraint.valeur == ""){
                    let tabCle = annotations.filter((annotation) => {return annotation.cle == constraint.cle});
                    let tabCleValeur = [];
                    for(annotation of annotations) {
                        for(cle of tabCle){
                            if(cle.indiceDebut == annotation.indiceDebut && cle.indiceFin == annotation.indiceFin){
                                if(tabCleValeur.indexOf(cle) === -1 ){tabCleValeur.push(cle);}
                                if(tabCleValeur.indexOf(annotation) === -1){tabCleValeur.push(annotation);}
                            }
                        }
                    }
                    return tabCleValeur;
                } else {
                    let tabCle = annotations.filter((annotation) => {return annotation.cle == constraint.cle});
                    let tabValeur = [];
                    let tabCleValeur = [];
                    let tabValeurToRemove = [];
                    switch (constraint.operateur){
                        case "=" :
                            tabValeur = annotations.filter((annotation) => {return annotation.cle == constraint.cle && annotation.valeur == constraint.valeur});
                            break;
                        case "≠" :
                            tabValeur = annotations.filter((annotation) => {return annotation.valeur != constraint.valeur});
                            tabValeurToRemove = annotations.filter((annotation) => {return annotation.valeur == constraint.valeur});
                            break;
                        case "<" :
                            tabValeur = annotations.filter((annotation) => {return annotation.cle == constraint.cle && annotation.valeur < constraint.valeur});
                            break;
                        case ">" :
                            tabValeur = annotations.filter((annotation) => {return annotation.cle == constraint.cle && annotation.valeur > constraint.valeur});
                            break;
                        case "≤" :
                            tabValeur = annotations.filter((annotation) => {return annotation.cle == constraint.cle && annotation.valeur <= constraint.valeur});
                            break;
                        case "≥" :
                            tabValeur = annotations.filter((annotation) => {return annotation.cle == constraint.cle && annotation.valeur >= constraint.valeur});
                            break;

                    }
                    for(cle of tabCle) {
                        for(valeur of tabValeur){
                            if(valeur.indiceDebut == cle.indiceDebut && valeur.indiceFin == cle.indiceFin){
                                if(tabCleValeur.indexOf(cle) === -1 ){tabCleValeur.push(cle);}
                                if(tabCleValeur.indexOf(valeur) === -1){tabCleValeur.push(valeur);}
                            }
                        }
                    }
                    for(annotation of annotations){
                        for(valeur of tabValeur){
                            if(tabCleValeur.indexOf(annotation) === -1 && annotation.indiceDebut == valeur.indiceDebut && annotation.indiceFin == valeur.indiceFin) {
                                tabCleValeur.push(annotation);
                            }
                        }
                    }
                    if (tabValeurToRemove != []){
                        for(valeur of tabValeurToRemove){
                            tabCleValeur = tabCleValeur.filter((annotation) => {return (annotation.indiceDebut != valeur.indiceDebut || annotation.indiceFin != valeur.indiceFin)});
                        }
                    }
                    return tabCleValeur;
                }
            } else if (constraint.operateur == "ET") {
                return filterByConstraint(filterByConstraint(annotations, constraint.filsGauche), constraint.filsDroit);

            } else if (constraint.operateur == "OU") {
                let left = filterByConstraint(annotations, constraint.filsGauche);
                let right = filterByConstraint(annotations, constraint.filsDroit);
                for(elem of right){
                    if(left.indexOf(elem) === -1){
                        left.push(elem);
                    }
                }
                return left;

            } else if (constraint.operateur == "OU EXCLUSIF") {
                return;
            }
        }

        // update resource method
        $scope.updateResource = function () {

            var keyword = $("#resourceAddKeyword");
            if (keyword[0].value != '') {
                $scope.editedResource.keywords.push(keyword[0].value);
                keyword[0].value = '';
            }

            if ($scope.resourceAddMD.key != '' && $scope.resourceAddMD.value != '') {
                $scope.editedResource.metadata.push($scope.resourceAddMD);
                $scope.resourceAddMD = {key: '', value: ''};
            }

            var newResource = jQuery.extend(true, {}, $scope.editedResource);

            delete newResource.id;
            delete newResource.type;
            delete newResource.author;
            delete newResource.owner;
            delete newResource.complete;
            delete newResource.required_exercise_resources;
            delete newResource.required_knowledges;
            delete newResource.complete_error;

            newResource.$update({id: $stateParams.resourceid}, function (resource) {
                $scope.resources[resource.id] = resource;
                $scope.editedResource = resource;
            });
        };

        $scope.onFileSelect = function ($files) {
            var file = $files[0];
            $scope.upload = $upload.upload({
                url: BASE_CONFIG.urls.api.uploads,
                method: 'POST',
                // withCredentials: true,
                data: {myObj: $scope.myModelObj},
                file: file
            }).progress(function (evt) {
                console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
            }).success(function (data) {
                // file is uploaded successfully
                $scope.editedResource.content.source = data.fileName;
            });
        };

        // delete resource method
        $scope.deleteResource = function (resource) {
            resource.$delete({id: resource.id}, function () {
                delete $scope.resources[resource.id];
            });
        };

        $scope.removeFromCollection = function (collection, index) {
            collection.splice(index, 1);
        };

        $scope.addProposition = function (collection) {
            var newProposition = {"text": "Nouvelle proposition", "right": false};
            collection.splice(collection.length, 0, newProposition);
        };

        $scope.addSolution = function (collection) {
            var newSolution = $('#resourceAddSolution');
            collection.push(newSolution[0].value);
            newSolution[0].value = '';
        };

        $scope.resourceAddKeywordsField = function (collection) {
            var keyword = $("#resourceAddKeyword");
            collection.push(keyword[0].value);
            keyword[0].value = '';
        };

        $scope.resourceAddMetadataField = function (collection) {
            collection.push($scope.resourceAddMD);
            $scope.resourceCancelMD();
        };

        $scope.resourceRemoveField = function (collection, index) {
            collection.splice(index, 1);
        };

        $scope.resourceCancelMD = function () {
            $scope.resourceAddMD = {key: '', value: ''};
            $scope.resource = null;
        };

        $scope.openSelectResource = function () {
            var modalInstance = $modal.open({
                templateUrl: BASE_CONFIG.urls.partials.teacher + '/partial-resource-list.html',
                controller: 'resourceSelectListController',
                size: 'lg',
                resolve: {
                    resources: function () {
                        return $scope.resources;
                    },
                    users: function () {
                        return $scope.users;
                    }
                }
            });

            modalInstance.result.then(function (val) {
                $scope.resourceAddMD.value = '__' + val;
                $scope.resource = $scope.resources[val];
            });
        };

        $scope.formulaAddVariable = function (collection) {
            collection.splice(collection.length,
                0,
                jQuery.extend(true, {}, $scope.resourceContext.formula.newVariable)
            );
        };

        $scope.resourceAddFormula = function (collection) {
            collection.splice(collection.length,
                0,
                jQuery.extend(true, {}, $scope.resourceContext.formula.newFormula)
            );
        };

    }]);

resourceControllers.controller('resourceSelectListController', ['$scope', 'BASE_CONFIG', '$modalInstance', 'resources', 'users',
    function ($scope, BASE_CONFIG, $modalInstance, resources, users) {
        $scope.isSelectResource = true;

        $scope.BASE_CONFIG = BASE_CONFIG;
        $scope.resources = resources;
        $scope.users = users;

        $scope.selectResource = function (resource) {
            $modalInstance.close(resource.id);
        };
    }]);


var modelControllers = angular.module('modelControllers', ['ui.router']);

modelControllers.controller('modelController', ['$scope', 'ExerciseByModel', 'AttemptByExercise', 'DirectorySelect', '$routeParams', '$location',
    function ($scope, ExerciseByModel, AttemptByExercise,DirectorySelect, $routeParams, $location) {

        $scope.section = 'model';
        $scope.href= `${window.location.protocol}//${window.location.hostname}${window.location.pathname}#/learner/model/`;

        /*
         * Here is a contextual client-side object used to specify user's filters information.
         * These values are bi-directionally data-bound to filters section fields in list views.
         */
        $scope.filters = {
            search: '', // search field
            archived: false, // select archived resources or not (boolean)
            public: false, // select public resources or not (boolean)
            type: { // resources types to be selected
                multiple_choice: 'multiple-choice', text_with_holes: 'text-with-holes', pair_items: 'pair-items', order_items: 'order-items', open_ended_question: 'open-ended-question', group_items: 'group-items'
            },
            keywords: [], // list of keywords that a resource must have to be selected
            metadata: [] // list of metadata objects that a resource must have to be selected
        };

        $scope.modelContext = {
            "newModel": {
                "block_constraint": {
                    "exists": {"key": '', "values": [], "comparator": 'exists'},
                    "in": {"key": '', "values": [], "comparator": 'in'},
                    "between": {"key": '', "values": ['', ''], "comparator": 'between'},
                    "other": {"key": '', "values": [''], "comparator": ''}
                },
                "pair_items": {
                    "type": "pair-items",
                    "title": "Nouvel appariement",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "wording": null,
                        "documents": [],
                        "pair_blocks": [
                            {
                                "is_list": true,
                                "number_of_occurrences": 0,
                                "resources": [],
                                "pair_meta_key": ""
                            }
                        ],
                        "exercise_model_type": "pair-items"
                    }
                },
                "sub_pair_items": {
                    "block_field": {
                        "is_list": true,
                        "number_of_occurrences": 0,
                        "resources": [],
                        "meta_key": "",
                        "resource_constraint": {
                            "metadata_constraints": [],
                            "excluded": []
                        }
                    }
                },
                "order_items": {
                    "type": "order-items",
                    "title": "Nouvel ordonnancement",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "wording": null,
                        "documents": [],
                        "object_blocks": [
                            {
                                "is_list": true,
                                "number_of_occurrences": 0,
                                "resources": [],
                                "meta_key": ""
                            }
                        ],
                        "is_sequence": false,
                        "give_first": false,
                        "give_last": false,
                        "order": "asc",
                        "show_values": false,
                        "exercise_model_type": "order-items"
                    }
                },
                "sub_order_items": {
                    "block_field": {
                        "is_list": true,
                        "number_of_occurrences": 0,
                        "resources": [],
                        "meta_key": "",
                        "resource_constraint": {
                            "metadata_constraints": [],
                            "excluded": []
                        }
                    }
                },
                "multiple_choice": {
                    "type": "multiple-choice",
                    "title": "Nouveau QCM",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "wording": null,
                        "documents": [],
                        "question_blocks": [
                            {
                                "number_of_occurrences": 0,
                                "resources": [],
                                "is_list": true,
                                "max_number_of_propositions": 0,
                                "max_number_of_right_propositions": 0
                            }
                        ],
                        "shuffle_questions_order": true,
                        "exercise_model_type": "multiple-choice"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "sub_multiple_choice": {
                    "block_field": {
                        "number_of_occurrences": 0,
                        "resources": [],
                        "is_list": true,
                        "max_number_of_propositions": 0,
                        "max_number_of_right_propositions": 0,
                        "resource_constraint": {
                            "metadata_constraints": [],
                            "excluded": []
                        }
                    }
                },
                "group_items": {
                    "type": "group-items",
                    "title": "Nouveau groupement",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "wording": null,
                        "documents": [],
                        "object_blocks": [
                            {
                                "number_of_occurrences": 0,
                                "resources": [],
                                "is_list": true
                            }
                        ],
                        "display_group_names": "ask",
                        "classif_constr": {
                            "other": "misc",
                            "meta_keys": [],
                            "groups": []
                        },
                        "exercise_model_type": "group-items"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "sub_group_items": {
                    "block_field": {
                        "number_of_occurrences": 0,
                        "resources": [],
                        "is_list": true,
                        "resource_constraint": {
                            "metadata_constraints": [],
                            "excluded": []
                        }
                    }
                },
                "group_items_group": {
                    "name": "",
                    "metadata_constraints": []
                },
                "open_ended_question": {
                    "type": "open-ended-question",
                    "title": "Nouveau modèle d'exercice de question à réponse ouverte courte",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {
                        "wording": null,
                        "documents": [],
                        "question_blocks": [
                            {
                                "number_of_occurrences": 0,
                                "resources": [],
                                "is_list": true
                            }
                        ],
                        "shuffle_questions_order": true,
                        "exercise_model_type": "open-ended-question"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                },
                "sub_open_ended_question": {
                    "block_field": {
                        "number_of_occurrences": 0,
                        "resources": [],
                        "is_list": true,
                        "resource_constraint": {
                            "metadata_constraints": [],
                            "excluded": []
                        }
                    }
                },
                "text_with_holes": {
                    "type": "text-with-holes",
                    "title": "Nouveau modèle d'exercice de texte à trous",
                    "public": false,
                    "archived": false,
                    "draft": false,
                    "complete": null,
                    "metadata": [],
                    "keywords": [],
                    "content": {

                        "wording": null,
                        "learnersVisualisation": {
                            'indicationShowed': false,
                            'initialElementAsIndication': true,
                            'keyAsIndication': null
                        },
                        "ressources": [],
                        "sharedTags": [],
                        "sharedConstraint": [],
                        'annotationsLists': [],
                        'coverages': [{
                            'isGlobal': true,
                            'listName': null,
                            'type': 'percent',
                            'value': 100
                        }],
                        "generateIndication": false,
                        "initAsIndication": true,
                        "indicationKey": null,
                        'responsesTag': "",
                        "documents": [],
                        "exercise_model_type": "text-with-holes"
                    },
                    "required_exercise_resources": null,
                    "required_knowledges": null
                }
            }
        };

        $scope.modelAddDirectoryField = function (newDir, model) {
            model.push(newDir.name);
            //DirectorySelect.save({
            //        modelId:model,
            //        directoryId:newDir.id
            //    },
            //    function(){
            //        console.log('saved model in directory');

            //});
        };
        $scope.modelAddKeywordsField = function (collection) {
            var keyword = $('#modelAddKeyword');
            collection.push(keyword[0].value);
            keyword[0].value = '';
        };

        $scope.modelAddMetadataField = function (collection) {
            var key = $("#modelAddMetadataKey"), val = $("#modelAddMetadataValue");
            var newElement = {key: key[0].value, value: val[0].value};
            collection.splice(collection.length, 0, newElement);
            key[0].value = '';
            val[0].value = '';
        };

        $scope.modelAddBlockResourceField = function (collection, id) {
            var isAlreadyAdded = false;
            angular.forEach(collection, function (res) {
                if (res.id == id) {
                    isAlreadyAdded = true;
                }
            });
            if (!isAlreadyAdded) {
                collection.splice(collection.length, 0, {"id": id});
            }
        };

        $scope.modelAddBlockResourceConstraint = function (metadata_constraints, type) {
            var newElement;

            if (type == 'exists') {
                newElement = jQuery.extend(true, {}, $scope.modelContext.newModel.block_constraint.exists);
            } else if (type == 'in') {
                newElement = jQuery.extend(true, {}, $scope.modelContext.newModel.block_constraint.in);
            } else if (type == 'between') {
                newElement = jQuery.extend(true, {}, $scope.modelContext.newModel.block_constraint.between);
            } else {
                newElement = jQuery.extend(true, {}, $scope.modelContext.newModel.block_constraint.other);
                newElement.comparator = type;
            }
            metadata_constraints.splice(metadata_constraints.length, 0, newElement);
        };

        $scope.modelAddBlockResourceConstraintValue = function (collection, val) {
            collection.push(val);
        };

        $scope.modelRemoveField = function (collection, index) {
            collection.splice(index, 1);
        };
        //$scope.directoryRemoveField = function (collection, index) {
        //    collection.splice(index, 1);
        //};

        $scope.viewAttempt = function (attempt) {
            $location.path("/learner/attempt/" + attempt.id);
        };

        $scope.tryExercise = function (exercise) {
            // create attempt from exercise
            AttemptByExercise.create({exerciseId: exercise.id},
                function (attempt) {
                    $scope.viewAttempt(attempt);
                });
        };
        $scope.typeToComper = function (type) {
            switch (type) {
                case 'multiple-choice':
                    return "choice";
                case 'open-ended-question':
                    return  "fill-in";
                case 'pair-items':
                    return "matching";
                case 'order-items':
                    return "sequencing";
                case 'group-items':
                    return "matching";
            }
        };

        $scope.tryModel = function (model) {
            // create exercise from model
            ExerciseByModel.try({modelId: model.id},
                function (exercise) {
                    $scope.tryExercise(exercise);
                });
        };
    }])
;

modelControllers.filter('myFilters', function () {
    return function (collection, filters) {
        var items = []; // collection to be returned
        var ids = []; // temporary IDs collection to maintain atomic items in collection to be returned
        angular.forEach(collection, function (value) { // iterate on each item in given collection
            angular.forEach(filters.type, function (type) { // iterate on each filter type specified by user to keep match users parameters.
                // remove archived items by default
                if (value.type == type && ((filters.archived && value.archived) || !value.archived)) {
                    // remove public items by default. Allow to display private user's items or public only items.
                    if ((filters.public && value.owner != BASE_CONFIG.currentUserId) || (!filters.public && value.owner == BASE_CONFIG.currentUserId)) {
                        // Check if user specified keywords filter
                        if (filters.keywords.length) {
                            // Check if the current item as at least one keyword
                            if (value.keywords.length) {
                                angular.forEach(filters.keywords, function (keyword) {
                                    if ($.inArray(keyword, value.keywords) != -1) {
                                        // So check if user specify at least one metadata filter
                                        if (filters.metadata.length) {
                                            // Check if the current item as at least one metadata
                                            if (value.metadata.length) {
                                                angular.forEach(filters.metadata, function (meta1) {
                                                    angular.forEach(value.metadata, function (meta2) {
                                                        // Metadata keys and values matchs
                                                        if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value.toLowerCase() == meta2.value.toLowerCase()) {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                        // Metadata keys matchs
                                                        if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value == '') {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                        // Metadata values matchs
                                                        if (meta1.value.toLowerCase() == meta2.value.toLowerCase() && meta1.key == '') {
                                                            if ($.inArray(value.id, ids) == -1) {
                                                                items.push(value);
                                                                ids.push(value.id)
                                                            }
                                                        }
                                                    });
                                                });
                                            }
                                        } else {
                                            if ($.inArray(value.id, ids) == -1) {
                                                items.push(value);
                                                ids.push(value.id)
                                            }
                                        }
                                    }
                                });
                            }
                        } else {
                            // User did not specify keyword filter
                            // So check if user specify at least one metadata filter
                            if (filters.metadata.length) {
                                // Check if the current item as at least one metadata
                                if (value.metadata.length) {
                                    angular.forEach(filters.metadata, function (meta1) {
                                        angular.forEach(value.metadata, function (meta2) {
                                            // Metadata keys and values matchs
                                            if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value.toLowerCase() == meta2.value.toLowerCase()) {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                            // Metadata keys matchs
                                            if (meta1.key.toLowerCase() == meta2.key.toLowerCase() && meta1.value == '') {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                            // Metadata values matchs
                                            if (meta1.value.toLowerCase() == meta2.value.toLowerCase() && meta1.key == '') {
                                                if ($.inArray(value.id, ids) == -1) {
                                                    items.push(value);
                                                    ids.push(value.id)
                                                }
                                            }
                                        });
                                    });
                                }
                            } else {
                                // User did not specity metadata filter
                                if ($.inArray(value.id, ids) == -1) {
                                    items.push(value);
                                    ids.push(value.id)
                                }
                            }
                        }
                    }
                }
            });
        });
        return items; // returns filtered collection
    };
});

modelControllers.controller('modelListController', ['$scope', 'Model', '$location', '$rootScope',
    function ($scope, Model, $location,$rootScope) {

        // retrieve models
        //Model.query({owner: BASE_CONFIG.currentUserId}, function (data) {
        //    // load an id indexed array of the models
        //    var privateModels = [];
        //    for (var i = 0; i < data.length; ++i) {
        //        privateModels[data[i].id] = data[i];
        //    }
        //    //code ajouté juste car j'en avais marre du public load
        //    //$scope.models = privateModels;

        //    Model.query({'public-except-user': BASE_CONFIG.currentUserId}, function (data) {
        //        // load an id indexed array of the models
        //        var publicModels = [];
        //        for (var i = 0; i < data.length; ++i) {
        //            publicModels[data[i].id] = data[i];
        //        }

        //        $scope.models =jQuery.extend(publicModels, privateModels);

        //        $scope.loadUsers($scope.models);
        //    });
        //});
        //$scope.loadModelsAndUsers = function(){
        //    // retrieve models
        //    Model.query({owner: BASE_CONFIG.currentUserId}, function (data) {
        //        // load an id indexed array of the models
        //        var privateModels = [];
        //        for (var i = 0; i < data.length; ++i) {
        //            privateModels[data[i].id] = data[i];
        //        }
        //        //$scope.models = privateModels;
        //        // uncomment and comment below if you are bored with heavy loading

        //        Model.query({'public-except-user': BASE_CONFIG.currentUserId}, function (data) {
        //            // load an id indexed array of the models
        //            var publicModels = [];
        //            for (var i = 0; i < data.length; ++i) {
        //                publicModels[data[i].id] = data[i];
        //            }

        //            $scope.models =jQuery.extend(publicModels, privateModels);

        //            $scope.loadUsers($scope.models);
        //    console.log(`models is empty so i load it4 ${$scope.models}`);
        //        });
        //    });
        //};
        if ($rootScope.models === null){
            $rootScope.models = 1;
            //console.log(`models is empty so i load it2 ${$scope.models}`);
            //$scope.loadModelsAndUsers();
            //console.log(`models is empty so i load it3 ${$scope.models}`);
            Model.query({owner: BASE_CONFIG.currentUserId}, function (data) {
                // load an id indexed array of the models
                var privateModels = [];
                for (var i = 0; i < data.length; ++i) {
                    privateModels[data[i].id] = data[i];
                }
                //$rootScope.models = privateModels;
                //uncomment below if you are bored with heavy loading
                Model.query({'public-except-user': BASE_CONFIG.currentUserId}, function (data) {
                    // load an id indexed array of the models
                    var publicModels = [];
                    for (var i = 0; i < data.length; ++i) {
                        publicModels[data[i].id] = data[i];
                    }
                    $rootScope.models =jQuery.extend(publicModels, privateModels);

                    $scope.loadUsers($rootScope.models);
                });
            });
        }

        $scope.deleteModel = function (model) {
            model.$delete({id: model.id}, function () {
                delete $scope.models[model.id];
            });
        };

        $scope.duplicateModel = function (model) {
            Model.duplicate({id: model.id}, function (data) {
                $location.path('/teacher/model/' + data.id)
            });
        };

        $scope.importModel = function (model) {
            Model.import({id: model.id}, function (data) {
                $scope.models[data.id] = data;
                $scope.loadResourcesAndUsers();
            });
        };

        $scope.subscribeModel = function (model) {
            Model.subscribe({id: model.id}, function (data) {
                $scope.models[data.id] = data;
            });
        };

        $scope.archiveModel = function (model) {
            console.log('archiving...');
            var archived = new Model;
            archived.archived = true;
            archived.metadata = model.metadata;
            archived.directories = model.directories;
            archived.$update({id: model.id}, function () {
                model.archived = true;
            });
        };

        //$scope.restoreResource = function (resource) {
        //    console.log('restoring...');
        //    var archived = new Resource;
        //    archived.archived = false;
        //    archived.metadata = resource.metadata
        //    archived.$update({id: resource.id}, function () {
        //        resource.metadata = archived.metadata;
        //        resource.archived = false;
        //    });
        //};

        $scope.restoreModel = function (model) {
            console.log('restoring...');
            var archived = new Model;
            archived.archived = false;
            archived.directories = model.directories;
            archived.metadata = model.metadata;
            archived.$update({id: model.id}, function () {
                //model.archived = archived.metadata;
                model.archived = false;
                //model.archved = archived.directories;
            });
        };

        $scope.createModel = function (type) {
            if (type == 'pair-items') {
                Model.save($scope.modelContext.newModel.pair_items, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            } else if (type == 'order-items') {
                Model.save($scope.modelContext.newModel.order_items, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            } else if (type == 'multiple-choice') {
                Model.save($scope.modelContext.newModel.multiple_choice, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            } else if (type == 'group-items') {
                Model.save($scope.modelContext.newModel.group_items, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            } else if (type == 'open-ended-question') {
                Model.save($scope.modelContext.newModel.open_ended_question, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            } else if (type == 'text-with-holes') {
                Model.save($scope.modelContext.newModel.text_with_holes, function (data) {
                    $location.path('/teacher/model/' + data.id)
                });
            }
        };
    }]);

modelControllers.controller('modelEditController', ['$scope', 'Model','ModelDirectory','DirectoryList','DirectorySelect', 'Resource', '$location', '$stateParams',
    function ($scope, Model,ModelDirectory,DirectoryList, DirectorySelect, Resource, $location, $stateParams) {


        $scope.freeDirectories =DirectoryList.query(
            function(){
                console.log('load free directories');
            }
        );
        $scope.filterAlreadyAdded = function(item) {
            if (typeof $scope.model.directories !== 'undefined'){
                if ($scope.model.directories.indexOf(item.name) == -1){
                    return item;
                }
            }
        };

        $scope.addItem = function () {
            $scope.initAnswerLists();
            $scope.initTagsList();
        }

        $scope.initAnswerLists = function () {
            $scope.answerLists = [];
            for (let resource of $scope.model.content.ressources){
                for (let annotationlist of $scope.resources[resource.id].content.annotations_list){
                        $scope.answerLists.push(annotationlist.name);
                }
            }
            $scope.answerLists = [...new Set($scope.answerLists)];
            for(let answer of $scope.answerLists) {
                let present = false;
                for (let resource of $scope.model.content.ressources) {
                    let presentInRessource = false;
                    for (let annotationList of $scope.resources[resource.id].content.annotations_list) {
                        if (annotationList.name == answer) {
                            presentInRessource = true;
                        }
                    }
                    if (!presentInRessource) {
                        if (!present) {
                            $scope.answerLists.splice($scope.answerLists.indexOf(answer), 1);
                        }
                    }
                }
            }
        };

        $scope.initTagsList = function () {
            $scope.tagsList = [];
            for (let resource of $scope.model.content.ressources){
                for (let annotation of $scope.resources[resource.id].content.annotations){
                    if($scope.tagsList.indexOf(annotation.cle) == -1) {
                        $scope.tagsList.push(annotation.cle);
                    }
                }
            }
            for(let tag of $scope.tagsList){
                let present = false;
                for(let resource of $scope.model.content.ressources){
                    let presentInRessource = false;
                    for (let annotation of $scope.resources[resource.id].content.annotations){
                        if(annotation.cle == tag){
                            presentInRessource = true;
                        }
                    }
                    if (!presentInRessource){
                        if (!present){
                            $scope.tagsList.splice($scope.tagsList.indexOf(tag),1);
                        }
                    }
                }
            }
        };

        $scope.initIndications = function () {
          $scope.keyAsIndication = "";
          $scope.generateIndications = false;
        };

        $scope._checkGenerateIndications = function () {
            $scope.generateIndications = document.getElementById('generateIndications').checked;
            console.log($scope.generateIndications);
        }

        $scope.model = Model.get({id: $stateParams.modelid}, function () {
            // fill each block with empty constraints
            $scope.fillBlockConstraints($scope.model);
            $scope.$parent.subSection = $scope.model.type;

            // determine accepted resource types
            switch ($scope.model.type) {
                case 'multiple-choice':
                    $scope.acceptedTypes = ['multiple-choice-question'];
                    $scope.comperType = "choice";
                    break;
                case 'open-ended-question':
                    $scope.acceptedTypes = ['open-ended-question'];
                    $scope.comperType = "fill-in";
                    break;
                case 'pair-items':
                    $scope.acceptedTypes = ['picture', 'text'];
                    $scope.comperType = "matching";
                    break;
                case 'order-items':
                    $scope.acceptedTypes = ['picture', 'text'];
                    $scope.comperType = "sequencing";
                    break;
                case 'group-items':
                    $scope.acceptedTypes = ['picture', 'text'];
                    $scope.comperType = "matching";
                    break;
                case 'text-with-holes':
                    $scope.acceptedTypes = ['text-with-holes'];
                    $scope.comperType = "fill-in";
                    $scope.initAnswerLists();
                    $scope.initTagsList();
                    $scope.initIndications();
                    $scope.selectedAnnotationList = "";
                    break;
            }
        });

        $scope._selectAnnotation = function() {
            $scope.selectedAnnotationList = document.getElementById('select-annotation-list').value
        }

        $scope._removeAnnotationList = function(listName){
            $scope.model.content.annotations_lists.splice($scope.model.content.annotations_lists.indexOf(listName),1);
            for(let coverage in $scope.model.content.coverages){
                console.log(coverage);
                if($scope.model.content.coverages[coverage].listName == listName){
                    $scope.model.content.coverages.splice(coverage,1);
                }
            }
        }

        $scope.addCoverage = function(annotationList) {
            $scope.model.content.coverages.push({
                'isGlobal': false,
                'listName': annotationList,
                'type': 'percent',
                'value': 100
            });
        }

        $scope.addAnnotationList = function() {
            if ($scope.model.content.annotations_lists.indexOf($scope.selectedAnnotationList) == -1) {
                $scope.model.content.annotations_lists.push($scope.selectedAnnotationList);
                $scope.addCoverage($scope.selectedAnnotationList);
            }
        }
        //$scope.usedDirectories =ModelDirectory.query({modelId:$stateParams.modelid},
        //    function(){
        //        console.log('load directories for model');
        //    }
        //);


        $scope.fillBlockConstraints = function (model) {
            switch (model.type) {
                case 'pair-items':
                    $scope.fillConstraints(model.content.pair_blocks);
                    break;
                case 'order-items':
                    $scope.fillConstraints(model.content.object_blocks);
                    break;
                case 'group-items':
                    $scope.fillConstraints(model.content.object_blocks);
                    break;
                case 'multiple-choice':
                    $scope.fillConstraints(model.content.question_blocks);
                    break;
                case 'open-ended-question':
                    $scope.fillConstraints(model.content.question_blocks);
                    break;
            }
        };

        $scope.fillConstraints = function (blocks) {
            for (var i = 0; i < blocks.length; ++i) {
                if (typeof blocks[i].resource_constraint === "undefined") {
                    blocks[i].resource_constraint = {
                        "metadata_constraints": [],
                        "excluded": []
                    };
                }
                if (typeof blocks[i].resource_constraint.metadata_constraints === "undefined") {
                    blocks[i].resource_constraint.metadata_constraints = [];
                }
                if (typeof blocks[i].resource_constraint.excluded === "undefined") {
                    blocks[i].resource_constraint.excluded = [];
                }
            }
        };

        $scope.saveAndTry = function () {
            var newModel = $scope.preUpdate();
            newModel.$update({id: $stateParams.modelid}, function (model) {
                $scope.model = model;
                $scope.models[model.id] = model;
                if (model.complete) {
                    $scope.tryModel(model);
                }
            });
        };

        $scope.preUpdate = function () {
            var keyword = $("#modelAddKeyword");
            if (keyword[0].value != '') {
                $scope.model.keywords.push(keyword[0].value);
                keyword[0].value = '';
            }

            var key = $("#modelAddMetadataKey"), val = $("#modelAddMetadataValue");
            if (key[0].value != '' && val[0].value != '') {
                var newElement = {key: key[0].value, value: val[0].value};
                $scope.model.metadata.splice($scope.model.metadata.length, 0, newElement);
                key[0].value = '';
                val[0].value = '';
            }

            var newModel = new Model();
            newModel = jQuery.extend(true, {}, $scope.model);

            delete newModel.id;
            delete newModel.author;
            delete newModel.owner;
            delete newModel.required_exercise_resources;
            delete newModel.required_knowledges;
            delete newModel.complete_error;

            return newModel;
        };

        $scope.updateModel = function () {
            var newModel = $scope.preUpdate();
            newModel.$update({id: $stateParams.modelid}, function (model) {
                $scope.model = model;
                $scope.models[model.id] = model;
            });
        };

        $scope.onDropMetadataKey = function (event, metakey, collection, field) {
            collection[field] = metakey;
        };

        $scope.deleteModel = function (model) {
            model.$delete({id: model.id}, function () {
            });
        };

        $scope.usedDocuments = [];

        $scope.onDropDocument = function (event, resource, documents) {
            if (resource.type == 'text' || resource.type == 'picture') {
                $scope.modelAddBlockResourceField(documents, resource.id);
            }
        };

        $scope.openFirstBlocks = {};

        $scope.openFirst = function (selector, index) {
            if (!$scope.openFirstBlocks.hasOwnProperty(selector)) {
                $scope.openFirstBlocks[selector] = [];
            }
            if (index == 0) {
                $scope.openFirstBlocks[selector].splice(index, 0, true);
            } else {
                $scope.openFirstBlocks[selector].splice(index, 0, false);
            }
        };

        $scope.initResourceConstraints = function (block) {
            if (!block.hasOwnProperty('resource_constraint')) {
                block.resource_constraint = {};
            }
            if (!block.resource_constraint.hasOwnProperty('metadata_constraints')) {
                block.resource_constraint.metadata_constraints = [];
            }
            if (!block.resource_constraint.hasOwnProperty('excluded')) {
                block.resource_constraint.excluded = [];
            }
        };

        $scope.onDropResourceToBlock = function (event, resource, collection) {
            if ($scope.acceptedTypes.indexOf(resource.type) != -1) {
                if (resource.owner === BASE_CONFIG.currentUserId) {
                    $scope.modelAddBlockResourceField(collection, resource.id);
                    $scope.addItem();
                } else {
                    var dial = confirm("Pour utiliser cette ressource qui ne vous appartient pas, vous devez l'importer dans votre espace personnel. Pour cela, vous pouvez vous abonner à cette ressource. Vous bénéficierez des modifications apportées par l'auteur et ne pourrez la modifier de votre côté. Il faudrait pour cela l'importer.\n\nVoulez-vous vous abonner à cette ressource ?");
                    if (dial == true) {
                        Resource.subscribe({id: resource.id}, function (data) {
                            $scope.resources[data.id] = data;
                            $scope.modelAddBlockResourceField(collection, data.id);
                            $scope.addItem();
                        });
                    }
                }
            }
        };

        $scope.onDropResource = function (event, resource, collection) {
            if ($scope.acceptedTypes.indexOf(resource.type) != -1) {
                if (resource.owner === BASE_CONFIG.currentUserId) {
                    $scope.modelAddBlockResourceField(collection, resource.id);
                    $scope.addItem();
                } else {
                    var dial = confirm("Pour utiliser cette ressource qui ne vous appartient pas, vous devez l'importer dans votre espace personnel. Pour cela, vous pouvez vous abonner à cette ressource. Vous bénéficierez des modifications apportées par l'auteur et ne pourrez la modifier de votre côté. Il faudrait pour cela l'importer.\n\nVoulez-vous vous abonner à cette ressource ?");
                    if (dial == true) {
                        Resource.subscribe({id: resource.id}, function (data) {
                            $scope.resources[data.id] = data;
                            $scope.modelAddBlockResourceField(collection, data.id);
                            $scope.addItem();
                        });
                    }
                }
            }
            console.log(collection);
            console.log($scope.resources[resource.id]);
        };
    }]);


modelControllers.controller('modelEditPairItemsController', ['$scope',
    function ($scope) {

        $scope.modelAddBlockField = function (collection) {
            collection.splice(
                collection.length,
                0,
                jQuery.extend(true, {}, $scope.modelContext.newModel.sub_pair_items.block_field)
            );
        };



        $scope.getMobilePart = function (collection, key) {
            var returnValue = '';
            angular.forEach(collection.metadata, function (meta) {
                if (meta.key == key) {
                    returnValue = meta.value;
                }
            });
            return returnValue;
        };
    }]);

modelControllers.controller('modelEditOrderItemsController', ['$scope',
    function ($scope) {

        $scope.modelAddBlockField = function (collection) {
            collection.splice(collection.length,
                0,
                jQuery.extend(true, {}, $scope.modelContext.newModel.sub_order_items.block_field)
            );
        };

        $scope.getOrderValue = function (collection, key) {
            var returnValue = '';
            angular.forEach(collection.metadata, function (meta) {
                if (meta.key == key) {
                    returnValue = meta.value;
                }
            });
            return returnValue;
        };
    }]);

modelControllers.controller('modelEditMultipleChoiceController', ['$scope',
    function ($scope) {

        $scope.modelAddBlockField = function (collection) {
            collection.splice(
                collection.length,
                0,
                jQuery.extend(true, {}, $scope.modelContext.newModel.sub_multiple_choice.block_field)
            );
        };
    }]);

modelControllers.controller('modelEditGroupItemsController', ['$scope',
    function ($scope) {

        $scope.modelAddBlockField = function (collection) {
            collection.splice(
                collection.length,
                0, jQuery.extend(true, {}, $scope.modelContext.newModel.sub_group_items.block_field)
            );
        };

        $scope.addGroup = function () {
            $scope.model.content.classif_constr.groups.splice(
                $scope.model.content.classif_constr.groups.length,
                0,
                jQuery.extend(true, {}, $scope.modelContext.newModel.group_items_group)
            );
        };

        $scope.findGroup = function (resource) {
            // test all the groups
            for (var i = 0; i < $scope.model.content.classif_constr.groups.length; ++i) {
                var group = $scope.model.content.classif_constr.groups[i];
                var belongs = true;

                // test all the constaints
                for (var j = 0; j < group.metadata_constraints.length; ++j) {
                    var mc = group.metadata_constraints[j];
                    var value = $scope.findMDValue(resource, mc.key);
                    if (value === null) {
                        belongs = false;
                    }

                    switch (mc.comparator) {
                        case 'in':
                            var isIn = false;
                            for (var k = 0; k < mc.values.length; ++k) {
                                if (mc.values[k] === value) {
                                    isIn = true;
                                }
                            }

                            if (isIn === false) {
                                belongs = false;
                            }
                            break;

                        case 'between':
                            if (value < mc.values[0] || value > mc.values[1]) {
                                belongs = false;
                            }
                            break;

                        case 'lt':
                            if (value >= mc.values[0]) {
                                belongs = false;
                            }
                            break;

                        case 'lte':
                            if (value > mc.values[0]) {
                                belongs = false;
                            }
                            break;

                        case 'gt':
                            if (value <= mc.values[0]) {
                                belongs = false;
                            }
                            break;

                        case 'gte':
                            if (value < mc.values[0]) {
                                belongs = false;
                            }
                            break;
                    }
                }

                if (belongs) {
                    return group.name;
                }
            }
            return 'Autre';
        };

        $scope.findMDValue = function (resource, key) {
            for (var i = 0; i < resource.metadata.length; ++i) {
                if (resource.metadata[i].key === key) {
                    return resource.metadata[i].value;
                }
            }

            return null;
        }
    }]);

modelControllers.controller('modelEditOpenEndedQuestionController', ['$scope',
    function ($scope) {

        $scope.modelAddBlockField = function (collection) {
            collection.splice(
                collection.length,
                0,
                jQuery.extend(true, {}, $scope.modelContext.newModel.sub_open_ended_question.block_field)
            );
        }

    }]);
