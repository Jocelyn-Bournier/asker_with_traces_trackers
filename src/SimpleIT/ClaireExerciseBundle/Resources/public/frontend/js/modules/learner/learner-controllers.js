var learnerControllers = angular.module('learnerControllers', ['ui.router']);

learnerControllers.controller('directoryModelListController', ['$scope', '$stateParams','DirectoryModelList','ExerciseByModel','$http',
    function ($scope,$stateParams,DirectoryModelList,ExerciseByModel,$http) {
        $scope.recommendations = new Array();
        $scope.objectives = [];
        $scope.directory = DirectoryModelList.get({id: $stateParams.dirId}, function () {
        });
        $scope.selectedIntention = null;
        $scope.currentIntention = null;
        $scope.selectedTab = $.cookie('exerciseGeneratedFrom');
        $scope.profileComputed = false;
        $scope.recommendationsRequested = false;
        $scope.selectionIntention;
        $scope.recommendationsNodes = new Array();
        $scope.selectedOption = 0;
        $scope.showResources = true;
        $scope.profileVisu = null;

        $scope.collapse = function (nodeName) {
            let panel = document.getElementById("panel-"+nodeName);
            let content = document.getElementById("content-"+nodeName);
            panel.classList.toggle("active");
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        }

        $scope.expandAllRecommendations = function () {
            let panels = document.getElementsByClassName("panel-recommendation");
            let contents = document.getElementsByClassName("content-recommendation");
            for(let panel of panels){
                panel.classList.add("active");
            }
            for(let content of contents){
                content.style.display = "block";
            }
        }

        /**
         * Génère un exercise en enregistrant la source de génération
         * @param location l'url de l'exercise
         */
        $scope._createExercise = function (location) {
            $.cookie('exerciseGeneratedFrom', 'management');
            if (location.indexOf("https://asker.univ-lyon1.fr/front/#/learner/model/") != -1) {
                let model_id = parseInt(location.split("https://asker.univ-lyon1.fr/front/#/learner/model/")[1]);
                exercise = ExerciseByModel.try({modelId: model_id},
                    function (exercise) {
                        $scope.tryExercise(exercise);
                    });
            } else {
                document.location = location;
            }
        }

        /**
         * Génère un exercise en enregistrant la source de génération
         * @param location l'url de l'exercise
         */
        $scope._createExerciseFromId = function (modelId) {
            $.cookie('exerciseGeneratedFrom', 'profile');

                exercise = ExerciseByModel.try({modelId: modelId},
                    function (exercise) {
                        $scope.tryExercise(exercise);
                    });
        }

        /**
         * Sélectionne un onglet entre l'onglet d'activités ou de gestion de l'activité (profil et recommandations)
         * @param tab L'onglet à ouvrir'
         */
        $scope._selectTab = function (tab) {
            console.log($scope.directory);
            if (tab === "management") {
                let profileAlreadyComputed = document.getElementById('olm-target-loader').classList.contains('hidden');
                if (!profileAlreadyComputed) {
                    $scope.requestProfile($scope.directory, true);
                }
                if ($scope.recommendations.length === 0) {
                    $scope.obtainRecommendations($scope.directory);
                }
            }

            const tabsToHide = document.getElementsByClassName('tab');
            for (let i = 0; i < tabsToHide.length; i++) {
                tabsToHide[i].classList.add('d-none');
            }
            const liTabsToHide = document.getElementsByClassName('li-tab');
            for (let i = 0; i < liTabsToHide.length; i++) {
                liTabsToHide[i].classList.remove('active');
            }

            if (document.getElementById(tab) !== null && document.getElementById('li-' + tab) !== null) {
                document.getElementById(tab).classList.remove('d-none');
                document.getElementById('li-' + tab).classList.add('active');
            }
        }

        /**
         * Récupère les recommendations d'exercices pour l'apprenant en fonction de son profil
         * @param directory Le repertoire sur lequel les recommendations sont proposées
         */
        $scope.obtainRecommendations = function (directory) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}`,
                type: "GET",
                async: true,
                success: function (data, textStatus) {
                    $scope.recommendations = JSON.parse(data);
                    if (data === "Error: recommendation not found") {
                        $scope.recommendations = [];
                    } else {
                        $scope.recommendations = JSON.parse(data);
                        console.log($scope.recommendations);
                        document.getElementById('recommendations-loader').classList.add('hidden');
                        console.log("debug");

                        let cpt = 1;
                        for(let recommendation in $scope.recommendations){
                            console.log($scope.recommendations[recommendation]);
                            $scope.recommendations[recommendation].learning_type = $scope.typeToAsker($scope.recommendations[recommendation].title);
                            $scope.recommendations[recommendation].index = cpt;
                            cpt = cpt + 1;
                        }

                        $scope.recommendations = Object.values($scope.recommendations);
                        $scope.recommendations.sort(function(a, b){
                            if(a.index < b.index){
                                return -1;
                            }else if(a.index > b.index){
                                return 1;
                            }
                            return 0;
                        });


                        $scope.retrieveGenerationObjectives(directory);
                        $scope.recommendationsNodes = new Array();
                        Object.values($scope.recommendations).filter(function(item){
                            var i = $scope.recommendationsNodes.findIndex(x => (x == item.objectiveNode));
                            if(i <= -1){
                                $scope.recommendationsNodes.push(item.objectiveNode);
                            }
                            return null;
                        });
                        $scope.$apply();
                    }
                },
                error: function () {
                }
            });
        };

        $scope._nbRecommendations = function (nodename){
            let contentContainer = document.getElementById('content-'+nodename);
            return contentContainer.childElementCount < 3;
        };

        /**
         * Récupère les recommendations d'exercices pour l'apprenant en fonction de son profil
         * @param directory Le repertoire sur lequel les recommendations sont proposées
         */
        $scope.requestRecommendations = function (directory, objectives) {
            document.getElementById('recommendations-loader').classList.remove('hidden');
            document.getElementById('recommendations-container').classList.add('hidden');
            let frameworkId = directory.framework_id;
            $.ajax({
                url: `${BASE_CONFIG.urls.api.profile}update/${frameworkId}/${directory.id}`,
                type: "GET",
                crossDomain: true,
                async: true,
                success: function (data, textStatus) {
                    $scope.profileComputed = true;
                    $scope.framework = data;
                    $scope.framework['computedAt']['date'] = $scope.framework['computedAt']['date'].split('.')[0];
                    $scope.drawProfile();


                    $.ajax({
                        url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/${JSON.stringify(objectives)}`,
                        type: "POST",
                        async: true,
                        success: function (data, textStatus) {
                            document.getElementById('recommendations-loader').classList.add('hidden');
                            document.getElementById('recommendations-container').classList.remove('hidden');
                            $scope.recommendationsRequested = true;
                            $scope.recommendations = JSON.parse(data);
                            for(let recommendation in $scope.recommendations) {
                                $scope.recommendations[recommendation].learning_type = $scope.typeToAsker($scope.recommendations[recommendation].title);
                            }
                            $scope.sortRecomendations();
                            $scope.expandAllRecommendations();
                            $scope.$apply();

                            for(let recommendation in $scope.recommendations){
                                console.log(BASE_CONFIG.urls.api.recommendationsTrace);
                                let action = "generate";
                                $.ajax({
                                    url: `${BASE_CONFIG.urls.api.recommendationsTrace}/${directory.id}/${action}/${$scope.recommendations[recommendation].title}`,
                                    type: "POST",
                                    async: true,
                                    success: function (data, textStatus) {
                                    }
                                });
                            }
                        },
                    });
                },
                error: function(request, status, err) {
                    console.log(request);
                    console.log(status);
                    console.log(err);
                        $.ajax({
                            url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/${JSON.stringify(objectives)}`,
                            type: "POST",
                            async: true,
                            success: function (data, textStatus) {
                                document.getElementById('recommendations-loader').classList.add('hidden');
                                document.getElementById('recommendations-container').classList.remove('hidden');
                                $scope.recommendationsRequested = true;
                                $scope.recommendations = JSON.parse(data);
                                for(let recommendation in $scope.recommendations) {
                                    $scope.recommendations[recommendation].learning_type = $scope.typeToAsker($scope.recommendations[recommendation].title);
                                }
                                $scope.sortRecomendations();
                                $scope.expandAllRecommendations();
                                $scope.$apply();

                                for(let recommendation in $scope.recommendations){
                                    console.log(BASE_CONFIG.urls.api.recommendationsTrace);
                                    let action = "generate";
                                    $.ajax({
                                        url: `${BASE_CONFIG.urls.api.recommendationsTrace}/${directory.id}/${action}/${$scope.recommendations[recommendation].title}`,
                                        type: "POST",
                                        async: true,
                                        success: function (data, textStatus) {
                                        }
                                    });
                                }
                            },
                        });
                }
            });
        };

        $scope.sortRecomendations = function () {
            $scope.recommendationsNodes = new Array();
            Object.values($scope.recommendations).filter(function (item) {
                var i = $scope.recommendationsNodes.findIndex(x => (x == item.objectiveNode));
                if (i <= -1) {
                    $scope.recommendationsNodes.push(item.objectiveNode);
                }
                return null;
            });
        };

        /**
         * Récupère les objectifs de la classe
         * @param directory Le repertoire sur lequel les objectifs sont liés
         */
        $scope.retrieveClassObjectives = function (directory) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/classObjectives`,
                type: "GET",
                async: true,
                success: function (data, textStatus) {
                    $scope.objectives = JSON.parse(data);
                    $scope.$apply();
                }
            });
        };

        /**
         * Récupère les objectifs de l'élève
         * @param directory Le repertoire sur lequel les objectifs sont liés
         */
        $scope.retrieveLearnerObjectives = function (directory) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/objectives`,
                type: "GET",
                async: true,
                success: function (data, textStatus) {
                    $scope.objectives = JSON.parse(data);
                    $scope.$apply();
                }
            });
        };

        /**
         * Récupère les objectifs ayant permis la génération des recommandations
         * @param directory Le repertoire sur lequel les objectifs sont liés
         */
        $scope.retrieveGenerationObjectives = function (directory) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/generationObjectives`,
                type: "GET",
                async: true,
                success: function (data, textStatus) {
                    $scope.objectives = JSON.parse(data);
                    $scope.$apply();
                }
            });
        };

        /**
         * Enregistre les objectifs de l'élève
         * @param directory Le repertoire sur lequel les objectifs sont liés
         * @param objectives Les objectifs a enregistrer
         */
        $scope.saveObjectives = function (directory, objectives) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/objectives/${JSON.stringify(objectives)}`,
                type: "POST",
                async: true,
                success: function (data, textStatus) {
                }
            });

        };

        /**
         * Enregistre le nouvel état d'une recommendation
         */
        $scope.performRecommendation = function (directory, recommendation, exerciseLocation, exerciseTitle) {
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendations}/${directory.id}/${directory.framework_id}/perform/${recommendation}`,
                type: "POST",
                async: true,
                success: function (data, textStatus) {
                    $scope.obtainRecommendations(directory);
                    $scope._createExercise(exerciseLocation);
                }
            });

            let action = "perform";
            let exerciseId = $scope.titleToAskerId(exerciseTitle);
            $.ajax({
                url: `${BASE_CONFIG.urls.api.recommendationsTrace}/${directory.id}/${action}/${exerciseTitle}/${exerciseId}`,
                type: "POST",
                async: true,
                success: function (data, textStatus) {
                }
            });
        }

        /**
         * Envoie un recommendation
         * @param directory
         * @param recommendationLocation
         * @param recommendationTitle
         */
        $scope.sendRecommendationStatement = function (directory, recommendationLocation, recommendationTitle) {
            let encodedTitle = encodeURIComponent(recommendationTitle);
            let encodedLocation = encodeURIComponent(recommendationLocation);
            $.ajax({
                url: "/api/recommendations/" + directory + '/' + encodedTitle + '?location=' + encodedLocation,
                type: "GET",
                async: true,
                success: function (data, textStatus) {
                }
            });
        };

        $scope.addIntention = function (nodeName, intention) {
            $scope.objectives.push([nodeName, intention]);
            switch (intention) {
                case 'Pre_requis':
                    intention = "Pré-requis";
                    break;
                case 'Decouverte':
                    intention = "Découverte";
                    break;
                case 'Soutien':
                    intention = "Soutien";
                    break;
                case 'Perfectionnement':
                    intention = "Perfectionnement";
                    break;
                case 'Revision':
                    intention = "Révision";
                    break;
                default:
                    intention = "Pas d'intention pédagogique définie";
                    break;
            }
            $scope.freeAddIntention();
            $scope.saveObjectives($scope.directory, $scope.objectives);
        }

        $scope.removeObjective = function (node) {
            $scope.objectives.splice($scope.objectives.indexOf(node), 1);
            $scope.saveObjectives($scope.directory, $scope.objectives);
        }

        $scope.addMastery = function (nodeName) {
            if (document.getElementById('exist:' + nodeName) === null) {
                let mySpans = document.getElementsByTagName('span');
                for (let i = 0; i < mySpans.length; i++) {
                    if (mySpans[i].innerHTML == nodeName && mySpans[i].id.search("node:") != -1) {
                        let parent = mySpans[i].parentNode;
                        let nodeToAdd = document.createElement('span');
                        nodeToAdd.innerHTML = parent.children[1].outerHTML;
                        nodeToAdd.id = "exist:" + nodeName;
                        nodeToAdd.children[0].style.marginTop = "-0.5em";
                        let objectiveNode = document.getElementById("objective:" + nodeName);
                        objectiveNode.appendChild(nodeToAdd);
                        break;
                    }
                }
            }
        }

        $scope.clearObjectives = function () {
            $scope.objectives = [];
            $scope.$apply();
        }

        $scope.selectIntention = function (intention) {
            var btnIntention = document.getElementById('button-' + intention);
            var allbtnIntentions = document.getElementById('list-button-intentions').children;
            for (var i = 0; i < allbtnIntentions.length; i++) {
                allbtnIntentions[i].children[0].classList.remove('btn-primary');
                allbtnIntentions[i].children[0].classList.add('btn-outline-primary');
            }
            btnIntention.classList.add('btn-primary');
            $scope.currentIntention = intention;
        }

        $scope.resetObjectives = function () {
            objectives = [];
            document.getElementById('reset-objectives').classList.add('d-none');
            document.getElementById('no-objectives').classList.remove('d-none');
            document.getElementById('objectives-container').innerHTML = '';
            $scope.$apply();
        }

        $scope.freeAddIntention = function () {
            $scope.selectionIntention = document.getElementById("selection-intention");
            $scope.selectionIntention.style.display = "none";
            $scope.currentIntention = null;
            $scope.selectedNode = null;
            var allbtnIntentions = document.getElementById('list-button-intentions').children;
            for (var i = 0; i < allbtnIntentions.length; i++) {
                allbtnIntentions[i].children[0].classList.remove('btn-primary');
                allbtnIntentions[i].children[0].classList.add('btn-outline-primary');
            }
        }

        $scope.removeChilds = function (parent) {
                while (parent.lastChild) {
                    parent.removeChild(parent.lastChild);
                }
        }

        $scope.drawProfile = function () {
            console.log($scope.showResources);
            $scope.selectionIntention = document.getElementById("selection-intention");
            $scope.removeChilds(document.getElementById('olm-target'));//.innerHTML = '';
            document.getElementById('olm-target-loader').classList.remove('hidden');
            document.getElementById('olm-target-loader').classList.add('hidden');
            let OLM = document._OLM;
            // Creates a tree based on the framework.
            let fw_tree = new OLM.CORE.FrameworkTree();
            fw_tree.buildFromFramework($scope.framework);
            document._OLM.currentTree = fw_tree;
            let config = {
                "fontHoverColor": "rgba(0, 0, 0, 1)",
                "fontColor": "rgba(0, 0, 0, .85)",
                "backgroundColor": "rgba(255, 255, 255, .95)",
                "showCover": $.cookie('userRoleStudentOnly') === 'false',
                "showTrust": $.cookie('userRoleStudentOnly') === 'false',
                "showExercises": $scope.showResources,
                "useLegend": false,
                "colors" : [{to:parseFloat($scope.framework.colors[0]),color:"#cf000f"},{to:parseFloat($scope.framework.colors[1]),color:"#f57f17"},{to:parseFloat($scope.framework.colors[2]),color:"#ffee58"},{color:"#4caf50"}]
            }

            switch($scope.selectedOption){
                case 0:
                    $scope.profileVisu = new OLM.TreeIndented(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treeIndented';
                    break;
                case 1:
                    $scope.profileVisu = new OLM.TreePartition(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treePartition';
                    break;
                case 2:
                    $scope.profileVisu = new OLM.TreePack(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treePack';
                    break;
                case 3:
                    $scope.profileVisu = new OLM.TreeSunburst(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treeSunburst';
                    break;
            }
            $scope.profileVisu.onClick = (node) => {
                $scope.selectedNode = node.data.name;
                $scope.selectionIntention.style.display = "block";
            };
            $scope.profileVisu.onMouseEnter = (node) => {};
            $scope.profileVisu.draw();

            for(let exercise of document.getElementsByClassName('exercise-olm')){
                let exerciseName = exercise.id.split("exercise-")[1];
                let location = $scope.titleToLocation(exerciseName);
                exercise.addEventListener('click', function() {
                    $scope._createExerciseFromId(location);

                }, false);
                exercise.addEventListener('mouseenter', function() {
                    exercise.style.cursor = "pointer";
                    exercise.style.fontWeight = "bold";

                }, false);
                exercise.addEventListener('mouseleave', function() {
                    exercise.style.fontWeight = "normal";
                }, false);
            }
            document.getElementById('olm-options').classList.remove('hidden');

            document.getElementById('olm-colors').innerHTML = `
                <div style="width: calc(${parseFloat($scope.framework.colors[0])*100}% - 15px); float:left; background-color:#cf000f; height:20px; margin-top:5px;border-radius: 5px 0px 0px 5px"></div>
                <div style="width: 30px; background-color:lightgray; height:30px; display: flex; justify-content: center; align-items: center; float:left;border-radius: 5px;border: 1px solid gray;">${parseFloat($scope.framework.colors[0])*100}</div>
                <div style="width: calc(${(parseFloat($scope.framework.colors[1])-parseFloat($scope.framework.colors[0]))*100}% - 30px); float:left; background-color:#f57f17; height:20px; margin-top:5px;"></div>
                <div style="width: 30px; background-color:lightgray; height:30px;  display: flex; justify-content: center; align-items: center; float:left;border-radius: 5px;border: 1px solid gray;">${parseFloat($scope.framework.colors[1]) * 100}</div>
                <div style="width: calc(${(parseFloat($scope.framework.colors[2])-parseFloat($scope.framework.colors[1]))*100}% - 30px); float:left; background-color:#ffee58; height:20px; margin-top:5px;"></div>
                <div style="width: 30px; background-color:lightgray; height:30px;  display: flex; justify-content: center; align-items: center; float:left;;border-radius: 5px;border: 1px solid gray;">${parseFloat($scope.framework.colors[2]) * 100}</div>
                <div style="width: calc(${(100-(parseFloat($scope.framework.colors[2])*100))}% - 15px); background-color:#4caf50; height:20px; float:left; margin-top:5px;border-radius: 0px 5px 5px 0px"></div>`;
            }
        /**
         * Récupère le profil de l'apprenant sur un répertoire donné
         * @param directory le répertoire sur lequel le profil récupéré correspond
         */
        $scope.requestProfile = function (directory, userInitiated = false) {
                let frameworkId = directory.framework_id;
                let action = userInitiated ? "viewProfileFromUser" : "viewProfile";
                $.ajax({
                    url: `${BASE_CONFIG.urls.api.profile}request/${frameworkId}/${directory.id}`,
                    type: "GET",
                    crossDomain: true,
                    async: true,
                    success: function (data, textStatus) {
                        $scope.profileComputed = true;
                        $scope.framework = data;
                        $scope.framework['computedAt']['date'] = $scope.framework['computedAt']['date'].split('.')[0]
                        $scope.drawProfile();
                    }
                });
                if (userInitiated) {
                    $.ajax({
                        url: `${BASE_CONFIG.urls.api.profile}trace/${frameworkId}/${action}`,
                        type: "POST",
                        async: true,
                        success: function (data, textStatus) {
                        }
                    });
                }
        }

        /**
         * Récupère le profil de l'apprenant sur un répertoire donné
         * @param directory le répertoire sur lequel le profil récupéré correspond
         */
        $scope.updateProfile = function (directory, userInitiated = false) {

            document.getElementById('olm-target').classList.add('hidden');
            document.getElementById('olm-target-loader').classList.remove('hidden');

            let action = userInitiated ? "updateProfileFromUser" : "updateProfile";

            let frameworkId = directory.framework_id;
            $.ajax({
                url: `${BASE_CONFIG.urls.api.profile}update/${frameworkId}/${directory.id}`,
                type: "GET",
                crossDomain: true,
                async: true,
                success: function (data, textStatus) {
                    console.log(data);
                    $scope.profileComputed = true;
                    $scope.framework = data;
                    $scope.framework['computedAt']['date'] = $scope.framework['computedAt']['date'].split('.')[0]
                    document.getElementById('olm-target-loader').classList.add('hidden');
                    document.getElementById('olm-target').classList.remove('hidden');
                    $scope.drawProfile();
                },
                error: function() {
                    document.getElementById('olm-target-loader').classList.add('hidden');
                    document.getElementById('olm-target').classList.remove('hidden');
                }
            });
            if(userInitiated) {
                $.ajax({
                    url: `${BASE_CONFIG.urls.api.profile}trace/${frameworkId}/${action}`,
                    type: "POST",
                    async: true,
                    success: function (data, textStatus) {
                    }
                });
            }
        }


        $scope.hideResource = function () {
            $scope.showResources = !$scope.showResources;
            $scope.profileComputed = true;
            $scope.framework = $scope.framework;
            $scope.updateProfile($scope.directory, false);
            document.getElementById('buttonShowExercises').innerHTML = "Afficher les resources";
            document.getElementById('buttonShowExercises').onclick = $scope.showResource;
        }

        $scope.showResource = function() {
            $scope.showResources = !$scope.showResources;
            $scope.profileComputed = true;
            $scope.framework = $scope.framework;
            $scope.updateProfile($scope.directory, false);
            document.getElementById('buttonShowExercises').innerHTML = "Cacher les resources";
            document.getElementById('buttonShowExercises').onclick = $scope.hideResource;
        }

        $scope.typeToAsker = function (type) {
            console.log("here");
            console.log(type);
            if ($scope.directory.models != null){
                for(let model of $scope.directory.models){
                    if (type == model.title){
                        console.log(model.type);
                        return model.type;
                    }
                }
            }
            if($scope.directory.subs != null){
                for(let sub of $scope.directory.subs){
                    if (sub.models != null){
                        for(let model of sub.models){
                            if (type == model.title){
                                console.log(model.type);
                                return model.type;
                            }
                        }
                    }
                }
            }
        };

        $scope.titleToAskerId = function (title) {
            if ($scope.directory.models != null){
                for(let model of $scope.directory.models){
                    if (title == model.title){
                        return "asker"+model.id;
                    }
                }
            }
            if($scope.directory.subs != null){
                for(let sub of $scope.directory.subs){
                    if (sub.models != null){
                        for(let model of sub.models){
                            if (title == model.title){
                                return "asker"+model.id;
                            }
                        }
                    }
                }
            }
        };

        $scope.titleToLocation = function (title) {
            console.log(title);
            if ($scope.directory.models != null){
                for(let model of $scope.directory.models){
                    if (title == model.title){
                        return model.id;
                    }
                }
            }
            if($scope.directory.subs != null){
                for(let sub of $scope.directory.subs){
                    if (sub.models != null){
                        for(let model of sub.models){
                            if (title == model.title){
                                return model.id;
                            }
                        }
                    }
                }
            }
        };

        $scope._changeOLMVisu = function (){
            $scope.selectedOption = parseInt(document.getElementById("olm-options").value);
            document.getElementById('olm-target').innerHTML = '';
            document.getElementById('olm-target-loader').classList.remove('hidden');
            document.getElementById('olm-target-loader').classList.add('hidden');
            let OLM = document._OLM;
            // Creates a tree based on the framework.
            let fw_tree = document._OLM.currentTree;

            let config = {
                "fontHoverColor": "rgba(0, 0, 0, 1)",
                "fontColor": "rgba(0, 0, 0, .85)",
                "backgroundColor": "rgba(255, 255, 255, .95)",
                "showCover": $.cookie('userRoleStudentOnly') === 'false',
                "showTrust": $.cookie('userRoleStudentOnly') === 'false',
                "useLegend": false,
                "showExercises": $scope.showResources
            }
            switch($scope.selectedOption){
                case 0:
                    console.log("here");
                    $scope.profileVisu = new OLM.TreeIndented(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treeIndented';
                    break;
                case 1:
                    $scope.profileVisu = new OLM.TreePartition(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treePartition';
                    break;
                case 2:
                    $scope.profileVisu = new OLM.TreePack(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treePack';
                    break;
                case 3:
                    $scope.profileVisu = new OLM.TreeSunburst(document.getElementById('olm-target'), fw_tree, config);
                    action = 'change_to_treeSunburst';
                    break;
            }

            $scope.profileVisu.onMouseEnter = (node) => {};
            $scope.profileVisu.draw();
            for(let exercise of document.getElementsByClassName('exercise-olm')){
                let exerciseName = exercise.id.split("exercise-")[1];
                let location = $scope.titleToLocation(exerciseName);
                console.log(exerciseName);
                console.log(location);
                exercise.addEventListener('click', function() {
                    $scope._createExerciseFromId(location);

                }, false);
                exercise.addEventListener('mouseenter', function() {
                    exercise.style.cursor = "pointer";
                    exercise.style.fontWeight = "bold";

                }, false);
                exercise.addEventListener('mouseleave', function() {
                    exercise.style.fontWeight = "normal";
                }, false);
            }
            document.getElementById('olm-options').classList.remove('hidden');
        }

    }
]);

learnerControllers.controller('learnerController', ['$scope', 'User', 'AttemptByExercise', 'ExerciseByModel', 'AttemptList', '$routeParams', '$location', '$stateParams','$sce',
    function ($scope, User, AttemptByExercise, ExerciseByModel, AttemptList, $routeParams, $location, $stateParams) {
        $scope.section = 'attempts';
        $scope.imageUrl = BASE_CONFIG.urls.images.uploads;
        $scope.documentUrl = BASE_CONFIG.urls.documents.uploads;
        $scope.imageExoUrl = BASE_CONFIG.urls.images.exercise;

        // retrieve attempts
        if ($stateParams.modelId == '' || $stateParams.modelId == null) {
            $scope.models = AttemptList.query(
                function () {
                    // when data loaded
                    $scope.loadUsers($scope.models);
                });
        } else {
            $scope.models = [];
            $scope.models[0] = AttemptList.get({modelId: $stateParams.modelId},
                function () {
                    // when data loaded
                    $scope.loadUsers($scope.models);
                });

        }

        $scope.viewAttempt = function (attempt) {
            $location.path("/learner/attempt/" + attempt.id);
        };

        $scope.tryExercise = function (exercise) {
            attempt = AttemptByExercise.create({exerciseId: exercise.id},
                function (attempt) {
                    $scope.viewAttempt(attempt);
                });
        };

        $scope.tryModel = function (model) {
            // create exercise from model
            $.cookie('exerciseGeneratedFrom', 'activities');
            exercise = ExerciseByModel.try({modelId: model.id},
                function (exercise) {
                    $scope.tryExercise(exercise);
                });

            //save trace
            actionType = "generateExercise";
            content = {"test":"try model"};
            context = {"test":"learner"};
            $scope.saveTrace(actionType, content, context);
        };

    }]);

