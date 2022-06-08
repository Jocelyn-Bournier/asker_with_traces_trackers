var newControllers = angular.module('newControllers', ['ui.router']);
newControllers.controller('newController',
    ['$scope','NewList','ExerciseByModel', 'AttemptByExercise', '$routeParams', '$location', '$stateParams','$sce',
    function ($scope,NewList,ExerciseByModel, AttemptByExercise, $routeParams, $location, $stateParams){
        $scope.section = 'news';
        $scope.imageUrl = BASE_CONFIG.urls.images.uploads;
        $scope.documentUrl = BASE_CONFIG.urls.documents.uploads;
        $scope.imageExoUrl = BASE_CONFIG.urls.images.exercise;

        console.log('news exercises loading...');
        $scope.news = NewList.query(
            function(){
                console.log('new exercises loaded');
            }
        );
        $scope.viewAttempt = function (attempt) {
            $location.path("/learner/attempt/" + attempt.id);
        };
        $scope.tryExercise = function (exercise) {
            // create attempt from exercise
            console.log('create attempt...');
            console.log(exercise.id);
            console.log(exercise);
            attempt = AttemptByExercise.create({exerciseId: exercise.id},
                function (attempt) {
                    console.log('redirection');
                    $scope.viewAttempt(attempt);
                });
        };
        $scope.tryModel = function (model) {
            // create exercise from model
            console.log('create exercise...');
            exercise = ExerciseByModel.try({modelId: model},
                function (exercise) {
                    $scope.tryExercise(exercise);
                });
        };


    }]);
