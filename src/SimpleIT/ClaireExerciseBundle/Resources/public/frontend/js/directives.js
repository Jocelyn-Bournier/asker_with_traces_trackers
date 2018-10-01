mainApp.directive('backButton', function(){

    return {
        restrict: 'A',

        link: function(scope, element, attrs) {
            element.bind('click', goBack);

            function goBack() {
                history.back();
                scope.$apply();
            }
        }
    }

});

mainApp.directive('nextButton', function(){

    return {
        restrict: 'A',

        link: function(scope, element, attrs) {
            element.bind('click', goForward);

            function goForward() {
                history.forward();
                scope.$apply();
            }
        }
    }

});