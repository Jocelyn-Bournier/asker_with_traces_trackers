var mainAppControllers = angular.module('mainAppControllers', ['ui.router']);

mainAppControllers.controller('mainManagerController', ['$scope', '$sce', '$routeParams', '$location', 'BASE_CONFIG', 'User', 'Resource','Model', '$rootScope','UserSharedDataService',
    function ($scope, $sce, $routeParams, $location, BASE_CONFIG, User, Resource,Model, $rootScope,UserSharedDataService) {
        // Error codes for complete
        $scope.completeError = {
            '101': 'Le modèle parent n\'est pas public',
            '201': 'Il faut préciser si les questions doivent être mélangées',
            '202': 'Il faut au moins un bloc de ressources dans le modèle',
            '203': 'L\'option d\'affichage des noms de groupe est invalide',
            '204': 'L\'option pour donner le premier ou le dernier est invalide',
            '205': 'Le modèle doit être basé sur une séquence ou des blocs d\'objets',
            '206': 'Le tri doit être croissant ou décroissant',
            '207': 'L\'option montrer les valeurs doit être renseignée',
            '301': 'Le nombre de propositions justes doit être positif ou nul (option ingnorée)',
            '302': 'La clé d\'appariement doit être précisée dans chaque bloc',
            '303': 'Option KeepAll invalide',
            '304': 'Les options pour utiliser le premier et le dernier doivent être renseignées',
            '305': 'La clé de métadonnées de chaque bloc doit être précisée',
            '306': 'Le nombre d\'objets à piocher dans le bloc doit être strictement positif',
            '307': 'Chaque bloc dit contenir au moins une ressource ou avoir des contraintes',
            '308': 'Un bloc est en mode liste mais ne contient aucune ressource dans la liste',
            '309': 'Il manque une ressource Ordre',
            '401': 'Le type de ressource choisit en contrainte n\'est pas valide pour ce type d\'exercise',
            '402': 'Il faut au moins une contrainte dans chaque bloc ou l\'option contrainte est choisie',
            '501': 'Une ressource comporte un id vide',
            '502': 'Une ressource est introuvable',
            '503': 'Une ressource est d\'un type non valide pour ce type d\'exercice',
            '504': 'Une ressource ne dispose pas de la clé de métadonnée requise par le bloc',
            '601': 'Contrainte invalide : le comparateur est vide',
            '602': 'Contrainte invalide : la clé est vide',
            '701': 'Il faut choisir quoi faire des ressources invalides : les placer dans le groupe autre ou ne pas les utiliser.',
            '702': 'Un nom de groupe ne peut pas être vide',
            '703': 'Chaque groupe doit comporter au moins une contrainte',
            '801': 'La source d\'une image ne doit pas être vide',
            '802': 'Le contenu d\'un texte ne doit pas être vide',
            '803': 'Le contenu d\'une question ne doit pas être vide',
            '804': 'Il faut préciser au moins une solution',
            '805': 'Il faut préciser au moins une proposition',
            '806': 'Le contenu d\'une porposition ne peut pas être vide',
            '807': 'Le type de séquence doit être précisé',
            '808': 'Impossible de valider le contenu de la séquence',
            '809': 'Chaque formule doit posséder un nom',
            '810': 'Chaque formule doit posséder une équation ou faire référence à une connaissance du domaine',
            '812': 'Au moins une des listes de contraintes ne permet pas de générer de "trous"',
            '901': "Il faut préciser le Titre",
            '1001': 'Il est nécessaire d\'avoir au moins une ressource',
            '1002': 'Il est nécessaire de choisir au moins une liste d\'annotation',
            '1003': 'Au moins une des ressources sélectionnées ne permet pas de générer sufisament d\'éléments pour le taux de couverture indiqué'
        };

        /*
         * Here is a contextual client-side object used to specify user's filters information.
         * These values are bi-directionally data-bound to filters section fields in list views.
         */
        $scope.filters = {
            search: '', // search field
            archived: false, // select archived resources or not (boolean)
            public: false, // select public resources or not (boolean)
            type: { // resources types to be selected
              multiple_choice_question: 'multiple-choice-question',
              text_with_holes: 'text-with-holes',
              document: 'document',
              text: 'text',
              order: 'order',
              picture: 'picture',
              open_ended_question: 'open-ended-question',
              sequence: ''
            },
            keywords: [], // list of keywords that a resource must have to be selected
            metadata: [] // list of metadata objects that a resource must have to be selected
        };
        $scope.filters.model = {
            search: '', // search field
            archived: false, // select archived resources or not (boolean)
            public: false, // select public resources or not (boolean)
            type: { // resources types to be selected
              multiple_choice_question: 'multiple-choice-question',
              text_with_holes: 'text-with-holes',
              document: 'document',
              text: 'text',
              order: 'order',
              picture: 'picture',
              open_ended_question: 'open-ended-question',
              sequence: ''
            },
            keywords: [], // list of keywords that a resource must have to be selected
            metadata: [] // list of metadata objects that a resource must have to be selected
        };

        if (typeof $rootScope.models === 'undefined') {
            $rootScope.models = null;
        }
        // load only once every necessary user
        $scope.loadUsers = function (resourcesData) {
            if (typeof $scope.users === 'undefined') {
                $scope.users = [];
            }

            var userIds = [];
            for (var i in resourcesData) {
                if (resourcesData.hasOwnProperty(i) && i != "$promise" && i != "$resolved") {
                    if (userIds.indexOf(resourcesData[i].author) == -1) {
                        userIds.push(resourcesData[i].author);
                    }
                    if (userIds.indexOf(resourcesData[i].owner) == -1) {
                        userIds.push(resourcesData[i].owner);
                    }
                }
            }

            for (i in userIds) {
                if (typeof $scope.users[userIds[i]] === 'undefined') {
                    $scope.users[userIds[i]] = User.get({userId: userIds[i]});
                }
            }
        };

        $scope.loadResourcesAndUsers = function () {
            Resource.query({owner: BASE_CONFIG.currentUserId}, function (data) {
                // load an id indexed array of the resources
                var privateResources = [];
                for (var i = 0; i < data.length; ++i) {
                    privateResources[data[i].id] = data[i];
                }
                $scope.resources = privateResources;

                Resource.query({'public-except-user': BASE_CONFIG.currentUserId}, function (data) {
                    // load an id indexed array of the resources
                    var publicResources = [];
                    for (var i = 0; i < data.length; ++i) {
                        publicResources[data[i].id] = data[i];
                    }

                    $scope.resources = jQuery.extend(publicResources, privateResources);
                    // migrate to UserSharedDataService RC 11/07/2023
                    //$scope.loadUsers($scope.resources);
                    UserSharedDataService.initData($scope.resources, BASE_CONFIG.currentUserId, User);
                    $scope.users = UserSharedDataService.getUsers();
                });
            });
        };

        $scope.to_trusted = function(html_code) {
            return $sce.trustAsHtml(html_code);
        }

        // initial loading
        $scope.BASE_CONFIG = BASE_CONFIG;
        $scope.loadResourcesAndUsers();
        //$scope.loadModelsAndUsers();
    }]);

mainAppControllers.controller('mainUserController', ['$scope', '$sce', '$routeParams', '$location', 'BASE_CONFIG', 'User',
    function ($scope, $sce, $routeParams, $location, BASE_CONFIG, User) {
        // load only once every necessary user
        $scope.loadUsers = function (resourcesData) {
            if (typeof $scope.users === 'undefined') {
                $scope.users = [];
            }

            var userIds = [];

            for (var i in resourcesData) {
                if (resourcesData.hasOwnProperty(i) && i != "$promise" && i != "$resolved") {
                    if (userIds.indexOf(resourcesData[i].author) == -1) {
                        userIds.push(resourcesData[i].author);
                    }
                    if (userIds.indexOf(resourcesData[i].owner) == -1) {
                        userIds.push(resourcesData[i].owner);
                    }
                }
            }

            for (i in userIds) {
                if (typeof $scope.users[userIds[i]] === 'undefined') {
                    $scope.users[userIds[i]] = User.get({userId: userIds[i]});
                }
            }
        };

        $scope.to_trusted = function(html_code) {
            return $sce.trustAsHtml(html_code);
        }

        $scope.BASE_CONFIG = BASE_CONFIG;
        $location.path($location.path());
    }]);

// Définir un service pour stocker les données
mainAppControllers.service('UserSharedDataService', function() {
  var sharedUsers = [];

  return {
    getUsers: function() {
      return sharedUsers;
    },
    initData: function(resourcesData, currentUserId, User){
      var userIds = [];
      for (var i in resourcesData) {
          if (resourcesData.hasOwnProperty(i) && i != "$promise" && i != "$resolved") {
              if (userIds.indexOf(resourcesData[i].author) == -1) {
                  userIds.push(resourcesData[i].author);
              }
              if (userIds.indexOf(resourcesData[i].owner) == -1) {
                  userIds.push(resourcesData[i].owner);
              }
          }
      }

      sharedUsers[currentUserId] =  User.get({userId: currentUserId})
      for (i in userIds) {
          if (typeof sharedUsers[userIds[i]] === 'undefined') {
              sharedUsers[userIds[i]] = User.get({userId: userIds[i]});
          }
      }
    }
  };
});

