var BlogPostItemController = function($scope, $rootScope, $idialog, BackendService){
    var self = this;
    self.post = {};
    self.editorOptions = {language: 'ru',skin:'flat'};

    self.save = save;

    activate();
    function activate() {

    }

    $scope.$on('Blog:postSelected', onPostSelected);
    function onPostSelected($event, data) {
        var post = data.Post;
        setPost(post);
    }

    function setPost(post) {
        self.post = post;
    }

    function save(){

    }
};

adminApp.controller('blog.postItem', ['$scope', '$rootScope', '$idialog','BackendService', BlogPostItemController]);