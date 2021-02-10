var learnerControllers = angular.module('learnerControllers', ['ui.router']);

learnerControllers.controller('directoryModelListController', ['$scope', '$stateParams','DirectoryModelList',
    function ($scope,$stateParams,DirectoryModelList) {
        $scope.directory= DirectoryModelList.get({id: $stateParams.dirId}, function () {
        });
        //alert('hello'+$stateParams.dirId);
        $scope.requestRecommendations = function (directory) {
            let frameworkId  = directory.framework_id;
            let recommEngine = "https://traffic.irit.fr/comper/recommendations/api/generate/";
            $.ajax({
                url:         "app_dev.php/api/directories/jwt/"+frameworkId+'/learner',
                type:        "GET",
                crossDomain: true,
                async:       true,
                success: function(data, textStatus){
                    $.ajax({
                        url:         recommEngine,
                        type:        "POST",
                        crossDomain: true,
                        async:       true,
                        headers: {          
                            "Accept": "application/json",
                            "Accept-Language": "*",
                            "Accept-Charset": "*",
                        },  
                        beforeSend: function(xhr){
                          xhr.setRequestHeader("Authorization", "Bearer "+data['token']);  
                        },
                        success: function(data, textStatus){
                            let protocol  = location.protocol;
                            let slashes   = protocol.concat("//");
                            let host      = slashes.concat(window.location.hostname);
                            let urlPrefix = host+'/app.php/front/#/learner/model/';
                            let findModel = (resourceLocation, directory) =>{
                                for(let i = 0; i < directory.models.length; i++){
                                    model = directory.models[i];
                                    if(urlPrefix+model.id === resourceLocation) return model;
                                }
                                if(directory.subs !== undefined){
                                    for(let i = 0; i < directory.subs.length; i++){
                                        let sub = directory.subs[i];
                                        let model = findModel(resourceLocation, sub);
                                        if(model !== null) return model;
                                    }
                                }
                                return undefined;
                            }
                            console.log(data);
                            data.map(x => {
                                x.tag      = x.tag.replace('Tag_', '');
                                x.weight   = (Math.round(x.weight * 10000)/100).toString()+' %';
                                x.has_attempts = 0;
                                let model = (x.learning_platform.toLowerCase() === 'asker') ? findModel(x.location, directory) : {has_attempts: 1};
                                (model === undefined) ? x.has_attempts = 0 : x.has_attempts = model.has_attempts;
                            });
                            let recommendations = [];
                            data.forEach(element => {
                                let exists = false;
                                for(let i = 0 ; i < recommendations.length; i++){
                                    if(recommendations[i].objectiveNode === element.objectiveNode){
                                        recommendations[i].recommendation.push(element);
                                        exists = true;
                                    }
                                }
                                if(!exists){
                                    recommendations.push({
                                        "objectiveNode":  element.objectiveNode,
                                        "recommendation": [element]
                                    });
                                }
                            });
                            console.log(recommendations);
                            $scope.recommendations_list = recommendations;
                            $scope.$apply();
                        },
                        error: function(message, textStatus){
                            console.log(message);
                        }
                    });
                },
                error: function(message, textStatus){
                    console.log(message);
                }
            });
        };
        $scope.sendRecommendationStatement = function (directory, recommendationLocation, recommendationTitle) {
            let encodedTitle    = encodeURIComponent(recommendationTitle);
            let encodedLocation = encodeURIComponent(recommendationLocation);
            $.ajax({
                url:         "/app_dev.php/api/recommendations/"+directory+'/'+encodedTitle+'?location='+encodedLocation,
                type:        "GET",
                async:       true,
                success: function(data, textStatus){}
            });
        };
        $scope.requestProfile = function (directory) {
            document.getElementById('olm-target-loader').classList.remove('hidden');
            let frameworkId   = directory.framework_id;
            $.ajax({
                url:         "app_dev.php/api/profile/request/"+frameworkId,
                type:        "GET",
                crossDomain: true,
                async:       true,
                success: function(data, textStatus){
                    document.getElementById('olm-target-loader').classList.add('hidden');
                    data = JSON.parse(data);
                    let OLM = document._OLM;
                    // Creates a sample framework randomly scored. This should be replaced with some framework retrieving function. 
                    let framework = data;
                    // Creates a tree based on the framework.
                    let fw_tree = new OLM.CORE.FrameworkTree();
                    fw_tree.buildFromFramework(framework);
                    document._OLM.currentTree = fw_tree;
                    // Creates the treeIndented object. The config is editable on the right =>  
                    let treeIndented  = new OLM.TreeIndented(document.getElementById('olm-target'), fw_tree, {
                        "fontHoverColor":  "rgba(0, 0, 0, 1)",
                        "fontColor":       "rgba(0, 0, 0, .85)",
                        "backgroundColor": "rgba(255, 255, 255, .95)"
                    });
                    treeIndented.onClick = (node) => {
                    // Your click behavior here. In the exemple below, we just pompt the node in the console.
                    }
                    // We chose an id for the svg element. Default behavior automatically creates a unique id.
                    treeIndented.draw(svgId = 'test-pack');
                    document.getElementById('olm-options').classList.remove('hidden');
                }
            });
            $.ajax({
                url:     "/app_dev.php/api/profile/trace/"+directory.id+'/request',
                type:    "POST",
                async:   true,
                success: function(data, textStatus){}
            });
        }
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

