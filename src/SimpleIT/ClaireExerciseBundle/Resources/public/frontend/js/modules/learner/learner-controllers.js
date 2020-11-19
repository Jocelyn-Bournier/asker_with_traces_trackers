var learnerControllers = angular.module('learnerControllers', ['ui.router']);

learnerControllers.controller('directoryModelListController', ['$scope', '$stateParams','DirectoryModelList',
    function ($scope,$stateParams,DirectoryModelList) {
        $scope.directory= DirectoryModelList.get({id: $stateParams.dirId}, function () {
        });
        //alert('hello'+$stateParams.dirId);
        $scope.requestRecommendations = function (directory) {
            let frameworkId = directory.framework_id;
            $.ajax({
                url:         "/app_dev.php/api/directories/jwt/"+frameworkId,
                type:        "GET",
                crossDomain: true,
                async:       true,
                success: function(data, textStatus){
                    console.log(data);
                },
                error: function(message, textStatus){
                    console.log(message);
                }
            });
            $.ajax({
                url:         "http://192.168.1.3:8080/jwt/recomm.php",
                type:        "GET",
                crossDomain: true,
                async:       true,
                success: function(data, textStatus){
                    data.map(x => {
                        x.tag    = x.tag.replace('Tag_', '');
                        x.weight = (Math.round(x.weight * 10000)/100).toString()+' %'; 
                    })
                    $scope.recommendations_list = data;
                    $scope.$apply();
                },
                error: function(message, textStatus){
                    console.log(message);
                }
            });
            
        };
    }
]);
learnerControllers.controller('learnerController', ['$scope', 'User', 'AttemptByExercise', 'ExerciseByModel', 'AttemptList', '$routeParams', '$location', '$stateParams',
    function ($scope, User, AttemptByExercise, ExerciseByModel, AttemptList, $routeParams, $location, $stateParams) {
        $scope.section = 'attempts';
        $scope.imageUrl = BASE_CONFIG.urls.images.uploads;
        $scope.imageExoUrl = BASE_CONFIG.urls.images.exercise;

        console.log('attempts loading...');

        // retrieve attempts
        if ($stateParams.modelId == '' || $stateParams.modelId == null) {
            $scope.models = AttemptList.query(
                function () {
                    // when data loaded
                    console.log('attempts loaded');
                    $scope.loadUsers($scope.models);
                });
        } else {
            $scope.models = [];
            $scope.models[0] = AttemptList.get({modelId: $stateParams.modelId},
                function () {
                    // when data loaded
                    console.log('attempt loaded');
                    $scope.loadUsers($scope.models);
                });

        }

        $scope.viewAttempt = function (attempt) {
            $location.path("/learner/attempt/" + attempt.id);
        };

        $scope.tryExercise = function (exercise) {
            // create attempt from exercise
            console.log('create attempt...');
            attempt = AttemptByExercise.create({exerciseId: exercise.id},
                function (attempt) {
                    console.log('redirection');
                    $scope.viewAttempt(attempt);
                });
        };

        $scope.tryModel = function (model) {
            // create exercise from model
            console.log('create exercise...');
            exercise = ExerciseByModel.try({modelId: model.id},
                function (exercise) {
                    $scope.tryExercise(exercise);
                });
        };
    }]);

