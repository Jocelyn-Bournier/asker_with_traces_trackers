var attemptControllers = angular.module('attemptControllers', ['ui.router']);

attemptControllers.controller('attemptController', ['$scope', '$state', 'AttemptByExercise', 'ExerciseByModel', 'Exercise', 'Attempt', 'Item', '$routeParams', '$stateParams','$sce',
    function ($scope, $state, AttemptByExercise, ExerciseByModel, Exercise, Attempt, Item, $routeParams, $stateParams,$sce) {

        $scope.imageUrl = BASE_CONFIG.urls.images.uploads;
        $scope.documentUrl = BASE_CONFIG.urls.documents.uploads;
        $scope.imageExoUrl = BASE_CONFIG.urls.images.exercise;
        $scope.navBarUrl = BASE_CONFIG.urls.partials.learner + '/fragment-nav-bar.html?v=' + buildVersion;

        $scope.validable = false;

        $scope.inExerciseTraces = [];

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
                                console.log($scope.exercise);
                            });
                    });
            }
        );

        $scope.gotoItem = function (index) {
            // switch item
            $scope.item = $scope.items[index];
            console.log($scope.item);
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
            } else if ($scope.item.type == 'text-with-holes') {
                console.log("twh");
                $state.go('attempt.text-with-holes', {itemId: index}, {location: false});
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

        $scope.saveTracesInExercise = function () {
            if ($scope.inExerciseTraces.length > 0) {
                for (i = 0 ; i < $scope.inExerciseTraces.length; i++){
                    let trace = $scope.inExerciseTraces[i];
                    $scope.saveTrace(trace.actionType, trace.content, trace.context, trace.dd, trace.df);
                }
                $scope.inExerciseTraces = [];
            }
        }

        $scope.saveTraceReturn = function () {
            $scope.saveTracesInExercise();
            let actionType = "return";
            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            let content = JSON.stringify(
                {"nb_steps":nbSteps, "curr_step":currStep});
            let context = JSON.stringify({"exercise_item_id":$scope.item.item_id});
            let date = new Date().toISOString();
            $scope.saveTrace(actionType, content, context, date, date);
        }

        $scope.saveTraceValidate = function () {
            $scope.saveTracesInExercise();
            let actionType = "validate";
            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            let content = JSON.stringify(
                {"nb_steps":nbSteps, "curr_step":currStep});
            let context = JSON.stringify({"exercise_item_id":$scope.item.item_id});
            let date = new Date().toISOString();
            $scope.saveTrace(actionType, content, context, date, date);
        }

        $scope.saveTraceTryAgain = function () {
            console.log($scope.items, $scope.item);
            $scope.saveTracesInExercise();
            let actionType = "try_again";
            let nbSteps = $scope.items.length;
            // the object change when correction is applied 
            // so we have to search manually the item
            let currStep = -1;
            for (i = 0; i < $scope.items.length; i++){
                if ($scope.items[i].item_id == $scope.item.item_id){
                    currStep = i + 1;
                    break;
                }
            }
            let content = JSON.stringify(
                {"nb_steps":nbSteps, "curr_step":currStep});
            let context = JSON.stringify({"exercise_item_id":$scope.item.item_id});
            let date = new Date().toISOString();
            $scope.saveTrace(actionType, content, context, date, date);
        }

        $scope.saveTraceStep = function (step) {
            console.log($scope.items, $scope.item, step);
            $scope.saveTracesInExercise();
            let actionType = "select_step";
            let selectedStep = step;
            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            let content = JSON.stringify(
                {"nb_steps":nbSteps, "curr_step":currStep,"selected_step":selectedStep});
            let context = JSON.stringify({"exercise_item_id":$scope.item.item_id});
            let date = new Date().toISOString();
            $scope.saveTrace(actionType, content, context, date, date);
        }

        $scope.seeDocument = function (resource){
            if (typeof resource.source !== 'undefined'){
                window.open(BASE_CONFIG.urls.documents.uploads + resource.source);
            }
            else{
                console.log("undefined");
            }
        }

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

        $scope.saveDragPairing = function () {
            $scope.pairingDD = new Date().toISOString();
        };

        $scope.setDroppedItemPairing = function (index, item) {
            $scope.dropPairingSrcIndexFrom = index;
            $scope.dropPairingItemID = item.id;
        };

        $scope.saveDropPairingFromResponse = function () {
            let destID = $scope.dropPairingItemID;
            let src = $scope.dropPairingSrcIndexFrom + 1;
            let dest = 0;
            for (i = 0; i < $scope.drop.length; i++){
                //define dest, if not find in drop, dest = 0 because panel = 0
                if ($scope.drop[i] != null){
                    if ($scope.drop[i].id == destID){
                        dest = i + 1;
                        break;
                    }
                }
            }

            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            
            let responseElement = -1;
            if (dest != 0) {
                responseElement = $scope.item['content']['fix_parts'][dest - 1].origin_resource;
            }
            
            if (src != dest){
                $scope.inExerciseTraces.push(
                {actionType: "place_element_pairing", 
                 dd:$scope.pairingDD, 
                 df:new Date().toISOString(), 
                 content: JSON.stringify({"elt_proposition" : destID, "elt_response" : responseElement, "src_pairing" : src, "dest_pairing" : dest, "nb_responses" : $scope.item['content'].fix_parts.length, "is_correct": -1}), 
                 context : JSON.stringify({"exercise_item_id":$scope.item.item_id, "nb_steps":nbSteps, "curr_step":currStep})});
            }
        };

        $scope.saveDropPairingFromPanel = function (item) {
            let destID = item.id;
            let src = 0;
            let dest = 0;
            for (i = 0; i < $scope.drop.length; i++){
                //define dest, if not find in drop, dest = 0 because panel = 0
                if ($scope.drop[i] != null){
                    if ($scope.drop[i].id == destID){
                        dest = i + 1;
                        break;
                    }
                }
            }

            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            
            let responseElement = -1;
            if (dest != 0) {
                responseElement = $scope.item['content']['fix_parts'][dest - 1].origin_resource;
            }

            if (src != dest){
                $scope.inExerciseTraces.push(
                {actionType: "place_element_pairing", 
                 dd:$scope.pairingDD, 
                 df:new Date().toISOString(), 
                 content: JSON.stringify({"elt_proposition" : destID, "elt_response" : responseElement, "src_pairing" : src, "dest_pairing" : dest, "nb_responses" : $scope.item['content'].fix_parts.length, "is_correct": -1}), 
                 context : JSON.stringify({"exercise_item_id":$scope.item.item_id, "nb_steps":nbSteps, "curr_step":currStep})});
            }
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
        $scope.dropPairingSrc = "none";
        for (i = 0; i < $scope.item['content'].mobile_parts.length; ++i) {
            $scope.drop[i] = null;
            $scope.solution[i] = null;
            $scope.item['content'].mobile_parts[i].id = i;
        }
        if ($scope.item['corrected'] == true) {
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
        }

        /*document.getElementById('toSortable').addEventListener('mousedown', function (e) {
            console.log('mousedown');
        });

        document.getElementById('toSortable').addEventListener('mouseup', function (e) {
            console.log('mouseup');
        });*/

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

itemControllers.controller('textWithHolesController', ['$scope', 'Answer', '$routeParams', '$location', '$stateParams',
    function ($scope, Answer, $routeParams, $location, $stateParams) {

        $scope.item.corrected = false;
        $scope.validable = true;

        $scope.saveAnswer = function () {
            var answer = new Answer;
            $scope.validable = false;
            answer.content = [];
            var answersInput = document.getElementsByClassName('input-TWH');
            for(let answerValue of answersInput){
                if (answerValue.value!= null) {
                    answer.content.push(answerValue.value);
                }
            }

            answer.$save({itemId: $scope.item.item_id, attemptId: $stateParams.attemptId},
                function (item) {
                    $scope.items[$stateParams.itemId] = item;
                    $scope.item = item;
                    $scope.displayCorrection(item)
                });

        };

        // correction
        $scope.displayCorrection = function (item) {
            $scope.item.corrected = true;
            //$scope.item['content']['comment'] = item['content']['comment'];
            $scope.item['content']['mark'] = item['content']['mark'];
        };

        // display learner answers
        $scope.fillLearnerAnswers = function () {
        };

        $scope.splitText = function () {

            let orderedIndices = [];

            for (let indice of $scope.item.content.bold){
                orderedIndices.push(["bo",indice[0]]);
                orderedIndices.push(["bf", indice[1]]);
            }
            for (let indice of $scope.item.content.italize){
                orderedIndices.push(["io",indice[0]]);
                orderedIndices.push(["if", indice[1]]);
            }
            for (let indice of $scope.item.content.underline){
                orderedIndices.push(["uo",indice[0]]);
                orderedIndices.push(["uf", indice[1]]);
            }

            orderedIndices.sort(function(a, b) {
                return b[1] - a[1];
            });

            let cpt = 0;
            let nbThree = 0;
            let nbFour = 0;
            let copieValue = $scope.item.content.text;
            console.log(copieValue);
            let newStr = "";
            for(let lettre of copieValue){
                for(let indice of orderedIndices){
                    if (cpt == indice[1]) {
                        switch (indice[0]) {
                            case "io" :
                                lettre = `<i>${lettre}`;
                                break;
                                case "uo" :
                                    lettre = `<u>${lettre}`;
                                    break;
                                case "bo" :
                                    lettre = `<b>${lettre}`;
                                    break;
                                case "if" :
                                    lettre = `</i>${lettre}`;
                                    break;
                                case "bf" :
                                    lettre = `</b>${lettre}`;
                                    break;
                                case "uf" :
                                    lettre = `</u>${lettre}`;
                                    break;
                        }
                    }
                }
                cpt = cpt + 1;
                newStr += lettre;
            }
            let lines = newStr.split(/\r\n|\r|\n/g);
            newStr = "";
            let newLines = [];
            let indexNewLine = 0;
            for(let line of lines) {
                newStr += line + '<br>';
                indexNewLine += line.length;
                newLines.push(indexNewLine);
            }
            $scope.item.content.text = newStr;

            $scope.item.content.holes.sort(function(a, b) {
                return b.indice_debut - a.indice_febut;
            });

            for(let hole of $scope.item.content.holes){
                let mooveHoleBegin = 0;
                let mooveHoleEnd = 0;
                for(let format of orderedIndices) {
                    switch (format[0]) {
                        case "io":
                        case "uo":
                        case "bo":
                            if (format[1] <= hole.indice_debut) {
                                mooveHoleBegin += 3;
                            }
                            if (format[1] <= hole.indice_fin) {
                                mooveHoleEnd += 3;
                            }
                            break;
                        case "if":
                        case "uf":
                        case "bf":
                            if (format[1] <= hole.indice_debut) {
                                mooveHoleBegin += 4;
                            }
                            if (format[1] <= hole.indice_fin) {
                                mooveHoleEnd += 4;
                            }
                            break;
                    }
                }
                for(let newLine of newLines) {
                    if (newLine <= hole.indice_debut + mooveHoleBegin) {
                        mooveHoleBegin += 4;
                    }
                    if (newLine <= hole.indice_fin + mooveHoleEnd) {
                        mooveHoleEnd += 4;
                    }
                }
                hole.indice_debut += mooveHoleBegin;
                hole.indice_fin += mooveHoleEnd;
                console.log(hole.indice_debut);
                console.log(hole.indice_fin);
            }
        }

        $scope.splitText();
        $scope.separatedText = [];
        let textToPush = $scope.item.content.text.substring(0,$scope.item.content.holes[0].indice_debut);
        if($scope.item.content.holes[0].indication != null){
            textToPush += `<b>(${$scope.item.content.holes[0].indication})</b>`;
        }
        $scope.separatedText.push(textToPush);
        for(let i = 1; i < $scope.item.content.holes.length; i++){
            textToPush = $scope.item.content.text.substring($scope.item.content.holes[i-1].indice_fin, $scope.item.content.holes[i].indice_debut);
            if($scope.item.content.holes[i].indication != null){
                textToPush += `<b>(${$scope.item.content.holes[i].indication})</b>`;
            }
            $scope.separatedText.push(textToPush);
        }
        $scope.separatedText.push($scope.item.content.text.substring($scope.item.content.holes[$scope.item.content.holes.length-1].indice_fin));

        if ($scope.item.content['answers'] != null && $scope.item.content['answers'].length > 0) {
            $scope.validable = false;
            $scope.fillLearnerAnswers();
            $scope.displayCorrection($scope.item);
        }

        $scope.item.content.holes.sort((a, b) => {
            return a.indice_debut - b.indice_debut;
        });

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
            $scope.saveTickAction();
        };

        $scope.saveTickAction = function () {
            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            let isChecked = $scope.tick[index];
            let element = $scope.item['content'].propositions[index];
            let date = new Date().toISOString();
            $scope.inExerciseTraces.push(
                {actionType: "select_answer_qcm", 
                 dd:date, df:date, 
                 content: JSON.stringify({"elt": element, "position": index+1, "nb_propositions": $scope.tick.length, "is_checked": isChecked, "is_correct": -1}), 
                 context: JSON.stringify({"exercise_item_id":$scope.item.item_id, "nb_steps":nbSteps, "curr_step":currStep})});
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
                for (var i = 0; i < $scope.groups.length; i++) {
                    $scope.groups[i].name="";
                }
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

        $scope.saveDragGrouping = function () {
            $scope.groupingDD = new Date().toISOString();
        };

        $scope.setDropGroupingSrc = function (item) {
            for (i = 0; i < $scope.groups.length; i++){
                for (j = 0; j < $scope.groups[i].objects.length; j++){
                    if ($scope.groups[i].objects[j].origin_resource == item.origin_resource){
                        $scope.dropGroupingSrc = "gr"+(i+1)+"_"+$scope.groups[i].name;
                        break;
                    }
                }
            }
        }

        $scope.saveDropGrouping = function (item,zoneSrc) {            
            let src = "gr0_panel";
            if (zoneSrc == "fromResponse"){
                src = $scope.dropGroupingSrc
            }
            
            let dest = "gr0_panel";
            for (i = 0; i < $scope.groups.length; i++){
                for (j = 0; j < $scope.groups[i].objects.length; j++){
                    if ($scope.groups[i].objects[j].origin_resource == item.origin_resource){
                        dest = "gr"+(i+1)+"_"+$scope.groups[i].name;
                        break;
                    }
                }
            }

            if (src == dest){
                return;
            }

            let nbSteps = $scope.items.length;
            let currStep = $scope.items.indexOf($scope.item) + 1;
            
            $scope.inExerciseTraces.push(
                {actionType: "place_element_grouping", 
                 dd:$scope.groupingDD, df:new Date().toISOString(), 
                 content: JSON.stringify({"elt" : item.origin_resource, "src" : src, "dest" : dest, "nb_groups" : $scope.groups.length, "is_correct": -1}), 
                 context: JSON.stringify({"exercise_item_id":$scope.item.item_id, "nb_steps":nbSteps, "curr_step":currStep})});
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
        $scope.dropGroupingSrc = "none";
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
