var saleAssistant = angular.module('saleAssistant', ['firebase','ngRoute']);

saleAssistant.config(['$routeProvider', function($routeProvider) {

}]);

// Resources
saleAssistant.run(['$rootScope', '$firebaseAuth', '$firebase', function($rootScope, $firebaseAuth, $firebase) {
	var ref = new Firebase('https://mdd-project.firebaseio.com');
	$rootScope.auth = $firebaseAuth(ref);

	console.log('done');
}]);

/*

angular.module('chat', ['firebase']).controller('Chat', ['$scope', 'angularFire',
	function($scope, angularFire) {
		var ref = new Firebase('https://angularfire.firebaseio.com/chat');
		angularFire(ref.limit(15), $scope, "messages");
		$scope.username = 'Guest' + Math.floor(Math.random()*101);
		$scope.addMessage = function() {
		$scope.messages[ref.push().name()] = {
			from: $scope.username, content: $scope.message
		};
		$scope.message = "";
	}
}]);

*/