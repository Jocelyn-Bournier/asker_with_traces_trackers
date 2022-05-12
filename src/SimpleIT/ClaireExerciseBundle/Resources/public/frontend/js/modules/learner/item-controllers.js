var attemptControllers = angular.module('attemptControllers', ['ui.router']);

attemptControllers.controller('attemptController', ['$scope', '$state', 'AttemptByExercise', 'ExerciseByModel', 'Exercise', 'Attempt', 'Item', '$routeParams', '$stateParams',
    function ($scope, $state, AttemptByExercise, ExerciseByModel, Exercise, Attempt, Item, $routeParams, $stateParams) {

        $scope.imageUrl = BASE_CONFIG.urls.images.uploads;
        $scope.documentUrl = BASE_CONFIG.urls.documents.uploads;
        $scope.imageExoUrl = BASE_CONFIG.urls.images.exercise;
        $scope.navBarUrl = BASE_CONFIG.urls.partials.learner + '/fragment-nav-bar.html';

        $scope.validable = false;

        console.log('loading attempt...');
        // retrieve attempt
        attempt = Attempt.get({attemptId: $stateParams.attemptId},
            function (attempt) {
                // when data loaded
                console.log('loading exercise...');
                $scope.exercise = Exercise.get({exerciseId: attempt.exercise},
                    function () {
                        // when data loaded
                        console.log('loading list of items...');
                        $scope.items = Item.query({attemptId: $stateParams.attemptId},
                            function () {
                                // when data loaded
                                console.log('items loaded.');
                                $scope.gotoItem(0);
                            });
                    });
            }
        );

        $scope.gotoItem = function (index) {
            // switch item
            $scope.item = $scope.items[index];
            // when data loaded
            // its cleaner but it makes a loop between controllers
            //$state.go('attempt.order-items', {itemId: $scope.item.item_id}, {location: false});
            //back to index => it was not possible to validate multiques questions model
            if ($scope.item.type == 'pair-items') {
                $state.go('attempt.pair-items', {itemId: index}, {location: false});
            } else if ($scope.item.type == 'order-items') {
                $state.go('attempt.order-items', {itemId: index}, {location: false});
            } else if ($scope.item.type == 'group-items') {
                $state.go('attempt.group-items', {itemId: index}, {location: false});
            } else if ($scope.item.type == 'multiple-choice') {
                $state.go('attempt.multiple-choice', {itemId: index}, {location: false});
            } else if ($scope.item.type == 'open-ended-question') {
                $state.go('attempt.open-ended-question', {itemId: index}, {location: false});
            }
        };

        $scope.viewAttempt = function (attempt) {
            $state.go('attempt', {attemptId: attempt.id}, {location: false});
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

        $scope.tryModel = function (modelId) {
            // create exercise from model
            console.log('create exercise...');
            exercise = ExerciseByModel.try({modelId: modelId},
                function (exercise) {
                    $scope.tryExercise(exercise);
                });
        };

    }]);

var itemControllers = angular.module('itemControllers', ['ui.router']);

itemControllers.controller('pairItemsController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams',
    function ($scope, Answer, $routeParams, $location, $stateParams) {

        // post answer
        $scope.saveAnswer = function () {
            $scope.validable = false;
            answer = new Answer;
            answer.content = [];

            for (i = 0; i < $scope.drop.length; ++i) {
                answer.content.push($scope.drop[i].id);
            }

            item = answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                function (item) {
                    $scope.items[$stateParams.itemId] = item;
                    $scope.displayCorrection(item)
                });
        };

        // correction
        $scope.displayCorrection = function (item) {
            for (i = 0; i < $scope.drop.length; ++i) {
                $scope.solution[i] = item['content'].mobile_parts[
                    item['content'].solutions[i]
                    ];
                $scope.solution[i].right =
                    item['content'].answers[i] == item['content'].solutions[i];
            }
            $scope.item.corrected = true;
            $scope.item['content']['mark'] = item['content']['mark'];
        };

        // display learner answers
        $scope.fillLearnerAnswers = function () {
            for (i = 0; i < $scope.drop.length; ++i) {
                $scope.drop[i] = $scope.item['content'].mobile_parts[
                    $scope.item['content'].answers[i]
                    ];
            }
        };

        // drag and drop
        $scope.onDropList = function ($event, $data, array) {
            array.push($data);
            $scope.validable = true;
        };

        $scope.onDropField = function ($event, $data, fieldNumber) {
            $scope.drop[fieldNumber] = $data;
        };

        $scope.dropSuccessHandler = function ($event, index, array) {
            array.splice(index, 1);
            if ($scope.item['content'].mobile_parts.length == 0) {
                $scope.validable = true;
            }
        };

        $scope.dropSuccessHandlerField = function ($event, fieldNumber) {
            $scope.drop[fieldNumber] = null;
        };

        // init answer array
        $scope.drop = [];
        $scope.solution = [];
        for (i = 0; i < $scope.item['content'].mobile_parts.length; ++i) {
            $scope.drop[i] = null;
            $scope.solution[i] = null;
            $scope.item['content'].mobile_parts[i].id = i;
        }
        if ($scope.item['corrected'] == true) {
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
        }

    }]);

itemControllers.controller('orderItemsController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams', '$timeout', '$state',
    function ($scope, Answer, $routeParams, $location, $stateParams, $timeout,$state) {

        // post answer
        $scope.saveAnswer = function () {
            $scope.validable = false;
            var answer = new Answer;
            answer.content = [];

            //for (i = 0; i < $scope.drops.length; ++i) {
            for (i = 0; i < $scope.drops.length; ++i) {
                answer.content.push($scope.drops[i]);
            }


            answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                function (item) {
                    $scope.items[$stateParams.itemId] = item;
                    $scope.item  = item;
                    $scope.fillLearnerAnswers();
                    $scope.displayCorrection(item)
                    //console.log("un item"+JSON.stringify(item))// => item contient le JSON de l'API
                });
        };

        // correction
        $scope.displayCorrection = function (item) {
            $scope.right = true;
            //for (i = 0; i < $scope.drops.length; ++i) {
            for (i = 0; i < $scope.drops.length; ++i) {
                $scope.solution[i] = {
                    object: item['content'].objects[
                        item['content'].solutions[i]
                        ],
                    value: item['content'].values[
                        item['content'].solutions[i]
                        ]
                };
                //C'est ici qu'on choisit si le cadre est rouge ou vert -- une loop un resultat binaire
                if (item['content'].answers[i] != item['content'].solutions[i]) {
                    $scope.right = false;
                }
            }
            $scope.item.corrected = true;
            $scope.item['content']['mark'] = item['content']['mark'];
        };

        $timeout(function(){ 
                //$scope.item['content'].objects.splice($scope.item.content.give_first, 1);
            $("#toSortable").sortable({
                receive: function( event, ui ) {
                    $(ui.item).remove();
                    //console.log("toArray"+ JSON.stringify($("#toSortable").sortable( "toArray" ,{attribute:"order"})));
                    if ( $("#toSortable").sortable("toArray").length == $scope.item['content'].objects.length){
                        $scope.validable = true;
                        //$scope.drops = $("#toSortable").sortable( "toArray",{attribute:"order"});

                        //console.log("toArray"+ JSON.stringify($("#toSortable").sortable( "toArray" ,{attribute:"order"})));
                    }
                    $scope.drops = $("#toSortable").sortable( "toArray",{attribute:"order"});
                    //$scope.item['content'].objects.splice($(ui.item).attr('order'),1);
                    $state.reload();
                }
            });
            $(".draggable").draggable({
                helper: "clone",
                connectToSortable: '#toSortable'
            });

        })

        // display learner answers
        $scope.fillLearnerAnswers = function () {
            for (i = 0; i < $scope.item['content'].answers.length; ++i) {
                $scope.drops[i] = $scope.item['content'].objects[
                    $scope.item['content'].answers[i]
                    ];
            }
        };


        // init answer array
        $scope.drops = [];
        //$scope.ss = [];
        $scope.solution = [];
        $scope.help = null;
        for (var i = 0; i < $scope.item['content'].objects.length; ++i) {
            $scope.solution[i] = null;
            $scope.item['content'].objects[i].id = i;
        }

        if ($scope.item['corrected'] == true) {
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
            //console.log($scope.drops);
            //console.log($scope.corrected);
            console.log($scope.solution);
        } else {
            // give first, give last
            if ($scope.item.content.give_last != '-1' && $scope.item.content.give_first != '-1') {
                $scope.help = 'Pour vous aider, le premier et le dernier objet ont été placés.'
            }else if($scope.item.content.give_first != '-1') {
                $scope.help = 'Pour vous aider, le premier objet a été placé.'
            }else if($scope.item.content.give_last != '-1') {
                $scope.help = 'Pour vous aider, le dernier objet a été placé.'
            }

        }


        // dnd init
        //$scope.toDrop = {'id': null, 'data': null};
        //$scope.toDrag = {'id': null};
    }]);

itemControllers.controller('multipleChoiceController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams',
    function ($scope, Answer, $routeParams, $location, $stateParams) {

        // post answer
        $scope.saveAnswer = function () {
            $scope.validable = false;

            answer = new Answer;
            answer.content = [];

            for (i = 0; i < $scope.tick.length; ++i) {
                if ($scope.tick[i]) {
                    val = 1;
                } else {
                    val = 0;
                }

                answer.content.push(val);
            }

            item = answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                function (item) {
                    $scope.items[$stateParams.itemId] = item;
                    $scope.displayCorrection(item)
                });
        };

        // correction
        $scope.displayCorrection = function (item) {
            for (var i = 0; i < $scope.tick.length; ++i) {
                $scope.solution[i] = item['content'].propositions[i]['right'];
            }
            $scope.item.corrected = true;
            $scope.item['content']['comment'] = item['content']['comment'];
            $scope.item['content']['mark'] = item['content']['mark'];
        };

        // display learner answers
        $scope.fillLearnerAnswers = function () {
            for (var i = 0; i < $scope.tick.length; ++i) {
                $scope.tick[i] = $scope.item['content'].propositions[i].ticked;
            }
        };

        $scope.tickAction = function (index) {
            if (!$scope.item.corrected) {
                $scope.tick[index] = !$scope.tick[index];
            }
        };

        // init answer array
        $scope.tick = [];
        $scope.solution = [];
        console.log('reinit...');
        for (i = 0; i < $scope.item['content'].propositions.length; ++i) {
            $scope.tick[i] = false;
            $scope.solution[i] = null;
        }

        if ($scope.item['corrected'] == true) {
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
        } else {
            $scope.validable = true;
        }
    }]);

itemControllers.controller('openEndedQuestionController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams',
    function ($scope, Answer, $routeParams, $location, $stateParams) {

        // post answer
        $scope.saveAnswer = function () {
            if ($scope.item['content'].answer != null && $scope.item['content'].answer != '') {
                $scope.validable = false;

                var answer = new Answer;
                answer.content = {answer: $scope.item['content'].answer};

                answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                    function (item) {
                        $scope.items[$stateParams.itemId] = item;
                        $scope.displayCorrection(item)
                    });
            }
        };

        // correction
        $scope.displayCorrection = function (item) {
            $scope.solutions = item['content'].solutions;
            $scope.right = $scope.solutions.indexOf($scope.item['content'].answer) != -1;

            $scope.item.corrected = true;
            $scope.item['content']['comment'] = item['content']['comment'];
            $scope.item['content']['mark'] = item['content']['mark'];
        };

        // init answer array
        console.log('reinit...');
        if ($scope.item['corrected'] == true) {
            $scope.displayCorrection($scope.item);
        } else {
            $scope.validable = true;
        }
    }]);

itemControllers.controller('groupItemsController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams',
    function ($scope, Answer, $routeParams, $location, $stateParams) {

        // post answer
        $scope.saveAnswer = function () {
            $scope.validable = false;
            var answer = new Answer;
            answer.content = {"obj": []};
            if ($scope.dgn === 'ask') {
                answer.content.gr = [];
            }

            for (var i = 0; i < $scope.groups.length; ++i) {
                // objects
                for (var j = 0; j < $scope.groups[i].objects.length; ++j) {
                    answer.content.obj[$scope.groups[i].objects[j].id] = i;
                }

                // group names
                if ($scope.dgn === 'ask') {
                    answer.content.gr[i] = $scope.groups[i].name;
                }
            }

            answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                function (item) {
                    $scope.items[$stateParams.itemId] = item;
                    $scope.displayCorrection(item)
                });
        };

        // correction
        $scope.displayCorrection = function (item) {
            $scope.item.corrected = true;
            $scope.item['content']['mark'] = item['content']['mark'];

            for (var i = 0; i < $scope.groups.length; ++i) {
                for (var j = 0; j < $scope.groups[i].objects.length; ++j) {
                    if (item['content'].solutions[$scope.groups[i].objects[j].id] === i) {
                        $scope.groups[i].objects[j].right = true;
                    } else {
                        $scope.solutions[
                            item['content'].solutions[$scope.groups[i].objects[j].id]
                            ].obj.push($scope.groups[i].objects[j]);
                        $scope.groups[i].objects[j].right = false;
                    }
                }

                // group names
                if ($scope.dgn == 'ask') {
                    $scope.groups[i].goodName = item['content'].groups[i];
                }
            }
        };

        // display learner answers
        $scope.fillLearnerAnswers = function () {
            for (i = 0; i < $scope.item['content'].answers.obj.length; ++i) {
                $scope.groups[
                    $scope.item['content'].answers.obj[i]
                    ].objects.push($scope.item['content'].objects[i]);
            }

            // group names
            if ($scope.dgn == 'ask') {
                for (i = 0; i < $scope.item['content'].answers.gr.length; ++i) {
                    $scope.groups[i].name = $scope.item['content'].answers.gr[i];
                }
            }
        };

        // drag and drop
        $scope.onDropList = function ($event, $data, array) {
            array.push($data);
        };

        //c'est ici que le boutton passe visible en theorique
        $scope.dropSuccessHandler = function ($event, index, array) {
            array.splice(index, 1);
            $scope.validable = ($scope.item['content'].objects.length == 0);
        };

        // init groups and solution
        $scope.groups = [];
        $scope.solutions = [];
        $scope.dgn = $scope.item['content'].display_group_names;
        for (i = 0; i < $scope.item['content'].groups.length; ++i) {
            $scope.groups[i] = {objects: []};
            if ($scope.dgn === 'show') {
                $scope.groups[i].name = $scope.item['content'].groups[i];
            }
            else {
                $scope.groups[i].name = null;
            }
            $scope.solutions[i] = {"obj": [], "gr": []};
        }

        // init objects
        for (i = 0; i < $scope.item['content'].objects.length; ++i) {
            $scope.item['content'].objects[i].id = i;
        }

        // corrected?
        if ($scope.item['corrected'] == true) {
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
        }
    }]);

// TODO Complete with a call to the API
sendStatement = function (item, mark){

}