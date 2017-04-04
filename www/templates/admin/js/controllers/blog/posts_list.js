var BlogPostsListController = function($scope, $rootScope, $idialog, BackendService, FileManagerService){
    var self = this;
    self.posts = [];
    self.category = {};
    self.filters = {};
    self.addPost = addPost;
    self.selectPost = selectPost;

    activate();
    function activate() {
        FileManagerService.getDirectory().then(function (response) {

        });
    }
    $scope.$on('Blog:categorySelected', onCategorySelected);
    function onCategorySelected($event, category) {
        if (category.Id != self.category.Id){
            self.category = category;
            updateList();
        }
    }

    function updateList() {
        BackendService.get({module:'Blog', controller:'Admin\\Post', action:'getList', params:{CategoryId:self.category.Id}}).then(function(response){
            var responseData = response.data;
            console.log(responseData);
        });
    }

    function addPost() {

    }

    function selectPost(post) {
        $rootScope.$broadcast('Blog:postSelected', {Post:post});
    }
};

adminApp.controller('blog.postsList', ['$scope', '$rootScope', '$idialog','BackendService', 'FileManagerService', BlogPostsListController]);